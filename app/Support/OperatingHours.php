<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class OperatingHours
{
    /** @var array<int, string> Display order starts Saturday as in Gaza. */
    public const DAYS = [
        6 => 'السبت',
        0 => 'الأحد',
        1 => 'الاثنين',
        2 => 'الثلاثاء',
        3 => 'الأربعاء',
        4 => 'الخميس',
        5 => 'الجمعة',
    ];

    /**
     * @return list<int>
     */
    public static function allDayNumbers(): array
    {
        return [6, 0, 1, 2, 3, 4, 5];
    }

    public static function normalizeTime(?string $time): ?string
    {
        if ($time === null) {
            return null;
        }

        $time = trim($time);
        if ($time === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $time, $m)) {
            return sprintf('%02d:%02d:%02d', (int) $m[1], (int) $m[2], (int) ($m[3] ?? 0));
        }

        return null;
    }

    /**
     * @param  array<int|string>|null  $days
     * @return list<int>
     */
    public static function normalizeDays(?array $days): array
    {
        if ($days === null || $days === []) {
            return self::allDayNumbers();
        }

        $valid = [];
        foreach ($days as $day) {
            if (! is_numeric($day)) {
                continue;
            }
            $n = (int) $day;
            if ($n >= 0 && $n <= 6) {
                $valid[] = $n;
            }
        }

        $valid = array_values(array_unique($valid));

        return array_values(array_filter(
            self::allDayNumbers(),
            fn (int $d) => in_array($d, $valid, true)
        ));
    }

    /**
     * Map submitted checkbox values without filling missing days.
     *
     * @param  array<int|string>|null  $days
     * @return list<int>
     */
    public static function submittedDays(?array $days): array
    {
        if ($days === null || $days === []) {
            return [];
        }

        $valid = [];
        foreach ($days as $day) {
            if (! is_numeric($day)) {
                continue;
            }
            $n = (int) $day;
            if ($n >= 0 && $n <= 6) {
                $valid[] = $n;
            }
        }

        return array_values(array_unique($valid));
    }

    /**
     * @param  array<int|string>|null  $workingDays
     */
    public static function isOpen(
        ?string $opensAt,
        ?string $closesAt,
        ?array $workingDays = null,
        ?CarbonInterface $at = null,
    ): bool {
        $at = $at ? Carbon::instance($at) : now();
        $opens = self::normalizeTime($opensAt);
        $closes = self::normalizeTime($closesAt);

        if ($opens === null || $closes === null) {
            return true;
        }

        $days = self::normalizeDays($workingDays);
        $time = $at->format('H:i:s');
        $overnight = $opens > $closes;

        $dayToCheck = (int) $at->dayOfWeek;
        if ($overnight && $time <= $closes) {
            $dayToCheck = (int) $at->copy()->subDay()->dayOfWeek;
        }

        if (! in_array($dayToCheck, $days, true)) {
            return false;
        }

        if ($opens === $closes) {
            return true;
        }

        if ($overnight) {
            return $time >= $opens || $time <= $closes;
        }

        return $time >= $opens && $time <= $closes;
    }

    public static function formatTime(?string $time): string
    {
        $norm = self::normalizeTime($time);
        if ($norm === null) {
            return '';
        }

        [$h, $m] = array_map('intval', explode(':', $norm));
        $period = $h >= 12 ? 'م' : 'ص';
        $h12 = $h % 12;
        if ($h12 === 0) {
            $h12 = 12;
        }

        return $h12.':'.sprintf('%02d', $m).' '.$period;
    }

    /**
     * @param  array<int|string>|null  $days
     */
    public static function daysLabel(?array $days): string
    {
        $days = self::normalizeDays($days);
        if (count($days) === 7) {
            return 'يومياً';
        }

        $off = array_values(array_diff(self::allDayNumbers(), $days));
        if (count($off) === 1) {
            return 'يومياً ما عدا '.self::DAYS[$off[0]];
        }

        return implode('، ', array_map(fn (int $d) => self::DAYS[$d], $days));
    }

    public static function rangeLabel(?string $opensAt, ?string $closesAt): string
    {
        if (! self::normalizeTime($opensAt) || ! self::normalizeTime($closesAt)) {
            return 'ساعات العمل غير محددة';
        }

        return self::formatTime($opensAt).' — '.self::formatTime($closesAt);
    }

    /**
     * @param  array<int|string>|null  $days
     */
    public static function fullLabel(?string $opensAt, ?string $closesAt, ?array $days = null): string
    {
        $range = self::rangeLabel($opensAt, $closesAt);
        if ($range === 'ساعات العمل غير محددة') {
            return $range;
        }

        return $range.' · '.self::daysLabel($days);
    }

    /**
     * @return array<string, list<string>>
     */
    public static function daysValidationRules(string $field = 'working_days'): array
    {
        return [
            $field => ['required', 'array', 'min:1'],
            $field.'.*' => ['integer', 'in:0,1,2,3,4,5,6'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function daysValidationMessages(string $field = 'working_days'): array
    {
        return [
            $field.'.required' => 'حدد أيام العمل.',
            $field.'.min' => 'اختر يوماً واحداً على الأقل.',
        ];
    }
}
