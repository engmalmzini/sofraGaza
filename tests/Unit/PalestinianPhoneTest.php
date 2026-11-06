<?php

namespace Tests\Unit;

use App\Support\PalestinianPhone;
use PHPUnit\Framework\TestCase;

class PalestinianPhoneTest extends TestCase
{
    public function test_digits_strips_non_numeric_characters(): void
    {
        $this->assertSame('0591234567', PalestinianPhone::digits('059-123-4567'));
        $this->assertSame('0591234567', PalestinianPhone::digits(' 059 123 4567 '));
        $this->assertSame('', PalestinianPhone::digits(null));
        $this->assertSame('', PalestinianPhone::digits(''));
    }

    public function test_digits_accepts_array_and_nested_input(): void
    {
        $this->assertSame('0591234567', PalestinianPhone::digits(['0591234567']));
        $this->assertSame('0591234567', PalestinianPhone::digits(['', '059-123-4567']));
        $this->assertSame('0591234567', PalestinianPhone::digits(['country' => 'PS', 'number' => '0591234567']));
        $this->assertSame('0591234567', PalestinianPhone::digits([['0591234567']]));
        $this->assertSame('', PalestinianPhone::digits([]));
        $this->assertSame('', PalestinianPhone::digits(['', null, []]));
        $this->assertSame('', PalestinianPhone::digits(true));
    }

    public function test_local_normalizes_international_numbers_from_arrays(): void
    {
        $this->assertSame('0591234567', PalestinianPhone::local(['972591234567']));
        $this->assertSame('0591234567', PalestinianPhone::local(['00972591234567']));
    }
}
