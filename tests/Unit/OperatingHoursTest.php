<?php

namespace Tests\Unit;

use App\Support\OperatingHours;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class OperatingHoursTest extends TestCase
{
    private string $previousTimezone;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousTimezone = date_default_timezone_get();
        date_default_timezone_set('Asia/Gaza');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        date_default_timezone_set($this->previousTimezone);
        parent::tearDown();
    }

    public function test_utc_server_morning_is_open_at_gaza_noon(): void
    {
        $gazaNoonAsUtc = Carbon::parse('2026-10-20 09:35:00', 'UTC');
        $gazaBeforeOpenAsUtc = Carbon::parse('2026-10-20 07:50:00', 'UTC');

        $this->assertTrue(OperatingHours::isOpen('11:00', '23:00', null, $gazaNoonAsUtc));
        $this->assertFalse(OperatingHours::isOpen('11:00', '23:00', null, $gazaBeforeOpenAsUtc));
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

    public function test_friday_can_have_its_own_hours(): void
    {
        $days = [6, 0, 1, 2, 3, 4, 5];
        $fridayHours = [
            '5' => ['opens_at' => '08:00', 'closes_at' => '15:00'],
        ];

        $fridayMorning = Carbon::parse('2026-10-16 10:00:00');
        $fridayEvening = Carbon::parse('2026-10-16 16:00:00');
        $thursdayNoon = Carbon::parse('2026-10-15 12:00:00');

        $this->assertTrue(OperatingHours::isOpen('11:00', '23:00', $days, $fridayMorning, $fridayHours));
        $this->assertFalse(OperatingHours::isOpen('11:00', '23:00', $days, $fridayEvening, $fridayHours));
        $this->assertTrue(OperatingHours::isOpen('11:00', '23:00', $days, $thursdayNoon, $fridayHours));
        $this->assertSame(
            'السبت–الخميس 11:00 ص — 11:00 م · الجمعة 8:00 ص — 3:00 م',
            OperatingHours::fullLabel('11:00', '23:00', $days, $fridayHours)
        );
    }

    public function test_overnight_override_does_not_break_next_morning(): void
    {
        $days = [4, 5];
        $dayHours = [
            '4' => ['opens_at' => '11:00', 'closes_at' => '01:00'],
            '5' => ['opens_at' => '08:00', 'closes_at' => '15:00'],
        ];

        $this->assertTrue(OperatingHours::isOpen('11:00', '23:00', $days, Carbon::parse('2026-10-16 00:30:00'), $dayHours));
        $this->assertTrue(OperatingHours::isOpen('11:00', '23:00', $days, Carbon::parse('2026-10-16 10:00:00'), $dayHours));
        $this->assertFalse(OperatingHours::isOpen('11:00', '23:00', $days, Carbon::parse('2026-10-16 16:00:00'), $dayHours));
    }
}
