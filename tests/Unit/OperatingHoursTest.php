<?php

namespace Tests\Unit;

use App\Support\OperatingHours;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class OperatingHoursTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_same_day_window_is_open_inside_and_closed_outside(): void
    {
        $this->assertTrue(OperatingHours::isOpen('09:00', '23:00', null, Carbon::parse('2026-10-18 12:00:00')));
        $this->assertTrue(OperatingHours::isOpen('09:00', '23:00', null, Carbon::parse('2026-10-18 09:00:00')));
        $this->assertTrue(OperatingHours::isOpen('09:00', '23:00', null, Carbon::parse('2026-10-18 23:00:00')));
        $this->assertFalse(OperatingHours::isOpen('09:00', '23:00', null, Carbon::parse('2026-10-18 08:59:00')));
        $this->assertFalse(OperatingHours::isOpen('09:00', '23:00', null, Carbon::parse('2026-10-18 23:01:00')));
    }

    public function test_overnight_window_stays_open_after_midnight(): void
    {
        $thursdayNight = Carbon::parse('2026-10-15 23:30:00'); // Thursday
        $fridayEarly = Carbon::parse('2026-10-16 00:30:00'); // Friday 12:30 AM
        $fridayLate = Carbon::parse('2026-10-16 02:00:00');

        $this->assertTrue(OperatingHours::isOpen('09:00', '01:00', [4], $thursdayNight));
        $this->assertTrue(OperatingHours::isOpen('09:00', '01:00', [4], $fridayEarly));
        $this->assertFalse(OperatingHours::isOpen('09:00', '01:00', [4], $fridayLate));
    }

    public function test_closed_on_non_working_day(): void
    {
        $fridayNoon = Carbon::parse('2026-10-16 12:00:00');
        $this->assertFalse(OperatingHours::isOpen('09:00', '23:00', [6, 0, 1, 2, 3, 4], $fridayNoon));
        $this->assertTrue(OperatingHours::isOpen('09:00', '23:00', [6, 0, 1, 2, 3, 4], Carbon::parse('2026-10-15 12:00:00')));
    }

    public function test_missing_hours_mean_always_open(): void
    {
        $this->assertTrue(OperatingHours::isOpen(null, null, [5], Carbon::parse('2026-10-16 03:00:00')));
    }

    public function test_arabic_labels(): void
    {
        $this->assertSame('9:00 ص — 11:00 م · يومياً ما عدا الجمعة', OperatingHours::fullLabel('09:00', '23:00', [6, 0, 1, 2, 3, 4]));
        $this->assertSame('12:00 ص — 1:00 ص', OperatingHours::rangeLabel('00:00', '01:00'));
    }
}
