<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Receipt;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReceiptTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category;
    private Expense $expense;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('receipts');
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create(['user_id' => $this->user->id]);
        $this->expense = Expense::factory()->create([
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * RC-01a: Upload a receipt (PDF).
     */
    public function test_receipt_can_be_uploaded(): void
    {
        $file = UploadedFile::fake()->create('factura.pdf', 2048, 'application/pdf');

        $response = $this
            ->actingAs($this->user)
            ->post(route('expenses.receipts.store', $this->expense), [
                'receipt' => $file,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('receipts', [
            'expense_id' => $this->expense->id,
            'user_id' => $this->user->id,
            'file_type' => 'application/pdf',
            'file_size' => 2048 * 1024,
        ]);

        $receipt = Receipt::first();
        $this->assertNotNull($receipt);
        Storage::disk('receipts')->assertExists($receipt->file_path);
    }

    /**
     * RC-01a variant: Upload a JPEG image.
     */
    public function test_receipt_image_can_be_uploaded(): void
    {
        $file = UploadedFile::fake()->image('recibo.jpg', 800, 600);

        $response = $this
            ->actingAs($this->user)
            ->post(route('expenses.receipts.store', $this->expense), [
                'receipt' => $file,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('receipts', [
            'expense_id' => $this->expense->id,
            'user_id' => $this->user->id,
        ]);

        $receipt = Receipt::first();
        $this->assertStringContainsString('image/', $receipt->file_type);
    }

    /**
     * RC-01b: Max 5 receipts per expense.
     */
    public function test_receipt_limit_of_five_is_enforced(): void
    {
        // Create 5 receipts
        for ($i = 0; $i < 5; $i++) {
            Receipt::factory()->create([
                'expense_id' => $this->expense->id,
                'user_id' => $this->user->id,
            ]);
        }

        $file = UploadedFile::fake()->create('factura-6.pdf', 1024, 'application/pdf');

        $response = $this
            ->actingAs($this->user)
            ->post(route('expenses.receipts.store', $this->expense), [
                'receipt' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Máximo 5 adjuntos por gasto');

        $this->assertDatabaseCount('receipts', 5);
    }

    /**
     * RC-02a: Reject non-allowed MIME type.
     */
    public function test_receipt_rejects_invalid_mime_type(): void
    {
        $file = UploadedFile::fake()->create('virus.exe', 512, 'application/x-msdownload');

        $response = $this
            ->actingAs($this->user)
            ->post(route('expenses.receipts.store', $this->expense), [
                'receipt' => $file,
            ]);

        $response->assertSessionHasErrors('receipt');
        $this->assertDatabaseCount('receipts', 0);
    }

    /**
     * RC-02b: Reject file exceeding 10 MB.
     */
    public function test_receipt_rejects_file_exceeding_max_size(): void
    {
        $file = UploadedFile::fake()->create('large.pdf', 11264, 'application/pdf'); // 11 MB

        $response = $this
            ->actingAs($this->user)
            ->post(route('expenses.receipts.store', $this->expense), [
                'receipt' => $file,
            ]);

        $response->assertSessionHasErrors('receipt');
        $this->assertDatabaseCount('receipts', 0);
    }

    /**
     * RC-03: Delete receipt (removes DB record + file).
     */
    public function test_receipt_can_be_deleted(): void
    {
        $file = UploadedFile::fake()->create('factura.pdf', 1024, 'application/pdf');
        $path = $file->store('/', 'receipts');

        $receipt = Receipt::factory()->create([
            'expense_id' => $this->expense->id,
            'user_id' => $this->user->id,
            'file_path' => $path,
        ]);

        Storage::disk('receipts')->assertExists($path);

        $response = $this
            ->actingAs($this->user)
            ->delete(route('receipts.destroy', $receipt));

        $response->assertRedirect();

        $this->assertDatabaseMissing('receipts', ['id' => $receipt->id]);
        Storage::disk('receipts')->assertMissing($path);
    }

    /**
     * Authorization: Cannot upload receipt for another user's expense.
     */
    public function test_cannot_upload_receipt_for_other_users_expense(): void
    {
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);
        $otherExpense = Expense::factory()->create([
            'category_id' => $otherCategory->id,
            'user_id' => $otherUser->id,
        ]);

        $file = UploadedFile::fake()->create('factura.pdf', 1024, 'application/pdf');

        $response = $this
            ->actingAs($this->user)
            ->post(route('expenses.receipts.store', $otherExpense), [
                'receipt' => $file,
            ]);

        $response->assertStatus(403);
    }

    /**
     * Authorization: Cannot delete another user's receipt.
     */
    public function test_cannot_delete_other_users_receipt(): void
    {
        $otherUser = User::factory()->create();
        $receipt = Receipt::factory()->create([
            'expense_id' => $this->expense->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->delete(route('receipts.destroy', $receipt));

        $response->assertStatus(403);
    }

    /**
     * Unauthenticated cannot upload.
     */
    public function test_unauthenticated_user_cannot_upload_receipt(): void
    {
        $file = UploadedFile::fake()->create('factura.pdf', 1024, 'application/pdf');

        $this->post(route('expenses.receipts.store', $this->expense), [
            'receipt' => $file,
        ])->assertRedirect('/login');
    }

    /**
     * Receipt file_url accessor returns a URL.
     */
    public function test_receipt_file_url_accessor(): void
    {
        $receipt = Receipt::factory()->create([
            'expense_id' => $this->expense->id,
            'user_id' => $this->user->id,
            'file_path' => 'test/file.pdf',
        ]);

        $url = $receipt->file_url;
        $this->assertStringContainsString('test/file.pdf', $url);
    }
}
