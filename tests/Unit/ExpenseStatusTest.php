<?php

namespace Tests\Unit;

use App\Enums\ExpenseStatus;
use Tests\TestCase;

class ExpenseStatusTest extends TestCase
{
    public function test_enum_has_planned_case(): void
    {
        $this->assertSame('planned', ExpenseStatus::Planned->value);
    }

    public function test_enum_has_contracted_case(): void
    {
        $this->assertSame('contracted', ExpenseStatus::Contracted->value);
    }

    public function test_enum_has_paid_case(): void
    {
        $this->assertSame('paid', ExpenseStatus::Paid->value);
    }

    public function test_enum_has_exactly_three_cases(): void
    {
        $this->assertCount(3, ExpenseStatus::cases());
    }

    public function test_enum_can_be_created_from_string(): void
    {
        $status = ExpenseStatus::from('planned');
        $this->assertSame(ExpenseStatus::Planned, $status);
    }

    public function test_enum_throws_on_invalid_value(): void
    {
        $this->expectException(\ValueError::class);
        ExpenseStatus::from('partially-paid');
    }

    public function test_enum_try_from_returns_null_on_invalid_value(): void
    {
        $status = ExpenseStatus::tryFrom('invalid');
        $this->assertNull($status);
    }
}
