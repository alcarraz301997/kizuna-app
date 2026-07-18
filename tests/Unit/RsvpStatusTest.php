<?php

namespace Tests\Unit;

use App\Enums\RsvpStatus;
use Tests\TestCase;

class RsvpStatusTest extends TestCase
{
    public function test_enum_has_pendiente_case(): void
    {
        $this->assertSame('pendiente', RsvpStatus::Pendiente->value);
    }

    public function test_enum_has_confirmado_case(): void
    {
        $this->assertSame('confirmado', RsvpStatus::Confirmado->value);
    }

    public function test_enum_has_no_asiste_case(): void
    {
        $this->assertSame('no_asiste', RsvpStatus::NoAsiste->value);
    }

    public function test_enum_has_exactly_three_cases(): void
    {
        $this->assertCount(3, RsvpStatus::cases());
    }

    public function test_enum_can_be_created_from_string(): void
    {
        $status = RsvpStatus::from('pendiente');
        $this->assertSame(RsvpStatus::Pendiente, $status);
    }

    public function test_enum_throws_on_invalid_value(): void
    {
        $this->expectException(\ValueError::class);
        RsvpStatus::from('tal_vez');
    }

    public function test_enum_try_from_returns_null_on_invalid_value(): void
    {
        $status = RsvpStatus::tryFrom('invalid');
        $this->assertNull($status);
    }
}
