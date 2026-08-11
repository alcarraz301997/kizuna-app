<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryTemplate;
use App\Models\Expense;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingMember;
use App\Services\CategoryTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTemplateTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Wedding $wedding;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->create();
        $this->wedding = Wedding::factory()->create(['owner_id' => $this->owner->id]);
        WeddingMember::factory()->create([
            'wedding_id' => $this->wedding->id,
            'user_id' => $this->owner->id,
            'role' => 'owner',
        ]);
    }

    public function test_owner_can_save_a_named_template_with_parent_child_order(): void
    {
        $response = $this->actingAs($this->owner)->post($this->templatesPath(), [
            'name' => 'Reception',
            'items' => [
                ['name' => 'Catering', 'budget_limit' => 2000, 'color' => '#222222', 'parent_index' => 1],
                ['name' => 'Venue', 'budget_limit' => 1000, 'color' => '#111111'],
            ],
        ]);

        $response->assertRedirect();
        $template = CategoryTemplate::firstOrFail();
        $this->assertSame('Reception', $template->name);
        $this->assertDatabaseHas('category_template_items', [
            'category_template_id' => $template->id,
            'name' => 'Venue',
            'parent_id' => null,
            'sort_order' => 0,
        ]);
        $this->assertDatabaseHas('category_template_items', [
            'category_template_id' => $template->id,
            'name' => 'Catering',
            'parent_id' => 1,
            'sort_order' => 1,
        ]);
    }

    public function test_authorized_member_can_apply_template_without_erasing_existing_categories(): void
    {
        $editor = User::factory()->create();
        WeddingMember::factory()->create([
            'wedding_id' => $this->wedding->id,
            'user_id' => $editor->id,
            'role' => 'editor',
        ]);
        $existing = Category::factory()->create([
            'wedding_id' => $this->wedding->id,
            'user_id' => $this->owner->id,
            'name' => 'Existing',
        ]);
        $template = CategoryTemplate::factory()->create(['wedding_id' => $this->wedding->id]);
        $template->items()->createMany([
            ['name' => 'Parent', 'budget_limit' => 1000, 'color' => '#111111', 'sort_order' => 0],
            ['name' => 'Child', 'budget_limit' => 500, 'color' => '#222222', 'sort_order' => 1, 'parent_id' => 1],
        ]);

        $this->actingAs($editor)
            ->post($this->templatesPath($template->id, 'apply'))
            ->assertRedirect();

        $this->assertDatabaseHas('categories', ['id' => $existing->id, 'name' => 'Existing']);
        $this->assertDatabaseHas('categories', ['wedding_id' => $this->wedding->id, 'name' => 'Parent', 'parent_id' => null, 'sort_order' => 0]);
        $this->assertDatabaseHas('categories', ['wedding_id' => $this->wedding->id, 'name' => 'Child', 'parent_id' => 2, 'sort_order' => 1]);
    }

    public function test_outsider_cannot_apply_a_template_or_read_rollups(): void
    {
        $outsider = User::factory()->create();
        $template = CategoryTemplate::factory()->create(['wedding_id' => $this->wedding->id]);

        $this->actingAs($outsider)
            ->post($this->templatesPath($template->id, 'apply'))
            ->assertForbidden();

        $this->actingAs($outsider)
            ->getJson($this->rollupsPath())
            ->assertForbidden();
    }

    public function test_rollups_keep_planned_contracted_paid_and_empty_categories_distinct(): void
    {
        $parent = Category::factory()->create([
            'wedding_id' => $this->wedding->id,
            'user_id' => $this->owner->id,
            'name' => 'Reception',
            'parent_id' => null,
            'sort_order' => 0,
        ]);
        $child = Category::factory()->create([
            'wedding_id' => $this->wedding->id,
            'user_id' => $this->owner->id,
            'name' => 'Catering',
            'parent_id' => $parent->id,
            'sort_order' => 1,
        ]);
        $empty = Category::factory()->create([
            'wedding_id' => $this->wedding->id,
            'user_id' => $this->owner->id,
            'name' => 'Empty',
            'sort_order' => 2,
        ]);
        Expense::factory()->create(['category_id' => $child->id, 'wedding_id' => $this->wedding->id, 'user_id' => $this->owner->id, 'amount' => 100, 'status' => 'planned']);
        Expense::factory()->create(['category_id' => $child->id, 'wedding_id' => $this->wedding->id, 'user_id' => $this->owner->id, 'amount' => 250, 'status' => 'contracted']);
        Expense::factory()->create(['category_id' => $child->id, 'wedding_id' => $this->wedding->id, 'user_id' => $this->owner->id, 'amount' => 75, 'status' => 'paid']);

        $response = $this->actingAs($this->owner)->getJson($this->rollupsPath());

        $response->assertOk()
            ->assertJsonPath('categories.0.name', 'Reception')
            ->assertJsonPath('categories.0.planned', 100)
            ->assertJsonPath('categories.0.contracted', 250)
            ->assertJsonPath('categories.0.paid', 75)
            ->assertJsonPath('categories.1.name', 'Catering')
            ->assertJsonPath('categories.1.planned', 100)
            ->assertJsonPath('categories.1.contracted', 250)
            ->assertJsonPath('categories.1.paid', 75)
            ->assertJsonPath('categories.2.name', 'Empty')
            ->assertJsonPath('categories.2.planned', 0)
            ->assertJsonPath('categories.2.contracted', 0)
            ->assertJsonPath('categories.2.paid', 0);
    }

    public function test_template_application_is_transactional_and_does_not_duplicate_existing_structure(): void
    {
        $template = CategoryTemplate::factory()->create(['wedding_id' => $this->wedding->id]);
        $template->items()->create(['name' => 'Venue', 'budget_limit' => 1000, 'color' => '#111111', 'sort_order' => 0]);

        $service = app(CategoryTemplateService::class);
        $service->apply($this->wedding, $template);
        $service->apply($this->wedding, $template);

        $this->assertDatabaseCount('categories', 1);
        $this->assertDatabaseHas('categories', ['name' => 'Venue', 'wedding_id' => $this->wedding->id]);
    }

    private function templatesPath(?int $templateId = null, string $suffix = ''): string
    {
        $path = "/weddings/{$this->wedding->id}/category-templates";
        if ($templateId !== null) {
            $path .= "/{$templateId}";
        }

        return $suffix === '' ? $path : "$path/$suffix";
    }

    private function rollupsPath(): string
    {
        return "/weddings/{$this->wedding->id}/category-rollups";
    }
}
