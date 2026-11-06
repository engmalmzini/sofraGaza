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
     * Restaurant hours are wall-clock times in Gaza, even if the server/app timezone is UTC.
     *
     * @var list<string>
     */
    private const LOCAL_TIMEZONES = ['Asia/Gaza', 'Asia/Hebron', '+03:00'];

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

    public static function toHi(?string $time): ?string
    {
        $norm = self::normalizeTime($time);

        return $norm ? substr($norm, 0, 5) : null;
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

        return self::submittedDays($days);
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

        $valid = array_values(array_unique($valid));

        return array_values(array_filter(
            self::allDayNumbers(),
            fn (int $d) => in_array($d, $valid, true)
        ));
    }

    /**
     * @param  array<int|string, mixed>|null  $dayHours
     * @return array<string, array{opens_at: string, closes_at: string}>|null
     */
    public static function submittedDayHours(
        mixed $dayHours,
        array $workingDays,
        ?string $defaultOpens,
        ?string $defaultCloses,
        mixed $customEnabled = false,
    ): ?array {
        $enabled = self::truthy($customEnabled);
        $raw = is_array($dayHours) ? $dayHours : [];

        if (! $enabled) {
            foreach ($raw as $row) {
                if (! is_array($row)) {
                    continue;
                }
                if (self::toHi($row['opens_at'] ?? null) || self::toHi($row['closes_at'] ?? null)) {
                    $enabled = true;
                    break;
                }
            }
        }

        if (! $enabled) {
            return null;
        }

        $defaultOpensHi = self::toHi($defaultOpens);
        $defaultClosesHi = self::toHi($defaultCloses);
        $out = [];

        foreach ($workingDays as $day) {
            $day = (int) $day;
            $row = $raw[$day] ?? $raw[(string) $day] ?? [];
            $row = is_array($row) ? $row : [];
            $opens = self::toHi($row['opens_at'] ?? null) ?? $defaultOpensHi;
            $closes = self::toHi($row['closes_at'] ?? null) ?? $defaultClosesHi;

            if ($opens === null || $closes === null || $opens === $closes) {
                continue;
            }

            if ($opens === $defaultOpensHi && $closes === $defaultClosesHi) {
                continue;
            }

            $out[(string) $day] = [
                'opens_at' => $opens,
                'closes_at' => $closes,
            ];
        }

        return $out === [] ? null : $out;
    }

    /**
     * @param  array<int|string, mixed>|null  $dayHours
     * @return array{opens_at: ?string, closes_at: ?string, always: bool}|null
     */
    public static function scheduleForDay(
        ?string $opensAt,
        ?string $closesAt,
        ?array $workingDays,
        ?array $dayHours,
        int $day,
    ): ?array {
        $days = self::normalizeDays($workingDays);
        if (! in_array($day, $days, true)) {
            return null;
        }

        $override = self::dayOverride($dayHours, $day);
        $opens = self::normalizeTime($override['opens_at'] ?? $opensAt);
        $closes = self::normalizeTime($override['closes_at'] ?? $closesAt);

        if ($opens === null || $closes === null) {
            return [
                'opens_at' => null,
                'closes_at' => null,
                'always' => true,
            ];
        }

        return [
            'opens_at' => $opens,
            'closes_at' => $closes,
            'always' => false,
        ];
    }

    /**
     * @param  array<int|string>|null  $workingDays
     * @param  array<int|string, mixed>|null  $dayHours
     */
    public static function isOpen(
        ?string $opensAt,
        ?string $closesAt,
        ?array $workingDays = null,
        ?CarbonInterface $at = null,
        ?array $dayHours = null,
    ): bool {
        $at = self::clockAt($at);
        $hasDefault = self::normalizeTime($opensAt) && self::normalizeTime($closesAt);
        if (! $hasDefault && ! self::hasAnyDayHours($dayHours)) {
            return true;
        }

        $time = $at->format('H:i:s');
        $today = (int) $at->dayOfWeek;
        $yesterday = (int) $at->copy()->subDay()->dayOfWeek;

        $todaySchedule = self::scheduleForDay($opensAt, $closesAt, $workingDays, $dayHours, $today);
        if ($todaySchedule) {
            if ($todaySchedule['always']) {
                return true;
            }
            if (self::isOvernight($todaySchedule['opens_at'], $todaySchedule['closes_at'])) {
                if ($time >= $todaySchedule['opens_at']) {
                    return true;
                }
            } elseif ($time >= $todaySchedule['opens_at'] && $time <= $todaySchedule['closes_at']) {
                return true;
            }
        }

        $yesterdaySchedule = self::scheduleForDay($opensAt, $closesAt, $workingDays, $dayHours, $yesterday);
        if (
            $yesterdaySchedule
            && ! $yesterdaySchedule['always']
            && self::isOvernight($yesterdaySchedule['opens_at'], $yesterdaySchedule['closes_at'])
            && $time <= $yesterdaySchedule['closes_at']
        ) {
            return true;
        }

        return false;
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

        return self::compactDayNames($days);
    }

    /**
     * @param  list<int>  $days
     */
    public static function compactDayNames(array $days): string
    {
        $days = self::submittedDays($days);
        if ($days === []) {
            return '';
        }
        if (count($days) === 7) {
            return 'يومياً';
        }

        $ranges = [];
        $current = [];
        foreach (self::allDayNumbers() as $day) {
            if (in_array($day, $days, true)) {
                $current[] = $day;
                continue;
            }
            if ($current !== []) {
                $ranges[] = $current;
                $current = [];
            }
        }
        if ($current !== []) {
            $ranges[] = $current;
        }

        $parts = array_map(function (array $range) {
            if (count($range) === 1) {
                return self::DAYS[$range[0]];
            }
            if (count($range) === 2) {
                return self::DAYS[$range[0]].' و'.self::DAYS[$range[1]];
            }

            return self::DAYS[$range[0]].'–'.self::DAYS[$range[array_key_last($range)]];
        }, $ranges);

        return implode('، ', $parts);
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
     * @param  array<int|string, mixed>|null  $dayHours
     */
    public static function fullLabel(?string $opensAt, ?string $closesAt, ?array $days = null, ?array $dayHours = null): string
    {
        $days = self::normalizeDays($days);
        $groups = [];

        foreach ($days as $day) {
            $schedule = self::scheduleForDay($opensAt, $closesAt, $days, $dayHours, $day);
            $opens = $schedule['opens_at'] ?? null;
            $closes = $schedule['closes_at'] ?? null;
            $key = ($opens ?? '').'|'.($closes ?? '');
            $groups[$key]['opens'] = $opens;
            $groups[$key]['closes'] = $closes;
            $groups[$key]['days'][] = $day;
        }

        if ($groups === []) {
            return 'ساعات العمل غير محددة';
        }

        if (count($groups) === 1) {
            $group = array_values($groups)[0];
            $range = self::rangeLabel($group['opens'], $group['closes']);
            if ($range === 'ساعات العمل غير محددة') {
                return $range;
            }

            return $range.' · '.self::daysLabel($days);
        }

        $parts = [];
        foreach ($groups as $group) {
            $range = self::rangeLabel($group['opens'], $group['closes']);
            $parts[] = self::compactDayNames($group['days']).' '.$range;
        }

        return implode(' · ', $parts);
    }

    /**
     * @param  array<int|string>|null  $days
     * @param  array<int|string, mixed>|null  $dayHours
     */
    public static function momentRangeLabel(
        ?string $opensAt,
        ?string $closesAt,
        ?array $days = null,
        ?array $dayHours = null,
        ?CarbonInterface $at = null,
    ): string {
        $at = self::clockAt($at);
        $schedule = self::scheduleForDay($opensAt, $closesAt, $days, $dayHours, (int) $at->dayOfWeek);
        if ($schedule && ! $schedule['always']) {
            return self::rangeLabel($schedule['opens_at'], $schedule['closes_at']);
        }

        return self::rangeLabel($opensAt, $closesAt);
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

    /**
     * @param  list<int>  $workingDays
     * @return array<string, list<string>>
     */
    public static function hoursValidationRules(bool $customDayHours, array $workingDays): array
    {
        $rules = [
            'opens_at' => ['required', 'date_format:H:i'],
            'closes_at' => ['required', 'date_format:H:i', 'different:opens_at'],
            'custom_day_hours' => ['sometimes', 'boolean'],
            'day_hours' => ['nullable', 'array'],
            ...self::daysValidationRules(),
        ];

        if ($customDayHours) {
            foreach ($workingDays as $day) {
                $rules["day_hours.{$day}.opens_at"] = ['required', 'date_format:H:i'];
                $rules["day_hours.{$day}.closes_at"] = ['required', 'date_format:H:i', "different:day_hours.{$day}.opens_at"];
            }
        }

        return $rules;
    }

    /**
     * @param  list<int>  $workingDays
     * @return array<string, string>
     */
    public static function hoursValidationMessages(array $workingDays = []): array
    {
        $messages = [
            'opens_at.required' => 'حدد ساعة الافتتاح.',
            'closes_at.required' => 'حدد ساعة الإغلاق.',
            'closes_at.different' => 'ساعة الإغلاق يجب أن تختلف عن ساعة الافتتاح.',
            ...self::daysValidationMessages(),
        ];

        foreach ($workingDays as $day) {
            $name = self::DAYS[$day] ?? (string) $day;
            $messages["day_hours.{$day}.opens_at.required"] = "حدد ساعة الافتتاح ليوم {$name}.";
            $messages["day_hours.{$day}.closes_at.required"] = "حدد ساعة الإغلاق ليوم {$name}.";
            $messages["day_hours.{$day}.closes_at.different"] = "ساعة الإغلاق ليوم {$name} يجب أن تختلف عن الافتتاح.";
        }

        return $messages;
    }

    /**
     * @param  array<int|string, mixed>|null  $dayHours
     * @return array{opens_at: string, closes_at: string}|null
     */
    private static function dayOverride(?array $dayHours, int $day): ?array
    {
        if ($dayHours === null || $dayHours === []) {
            return null;
        }

        $row = $dayHours[$day] ?? $dayHours[(string) $day] ?? null;
        if (! is_array($row)) {
            return null;
        }

        $opens = self::toHi($row['opens_at'] ?? null);
        $closes = self::toHi($row['closes_at'] ?? null);
        if ($opens === null || $closes === null) {
            return null;
        }

        return [
            'opens_at' => $opens,
            'closes_at' => $closes,
        ];
    }

    /**
     * @param  array<int|string, mixed>|null  $dayHours
     */
    private static function hasAnyDayHours(?array $dayHours): bool
    {
        if ($dayHours === null) {
            return false;
        }

        foreach (self::allDayNumbers() as $day) {
            if (self::dayOverride($dayHours, $day)) {
                return true;
            }
        }

        return false;
    }

    private static function isOvernight(string $opens, string $closes): bool
    {
        return $opens > $closes;
    }

    public static function localTimezone(): string
    {
        foreach (self::LOCAL_TIMEZONES as $tz) {
            try {
                new \DateTimeZone($tz);

                return $tz;
            } catch (\Exception) {
                continue;
            }
        }

        return '+03:00';
    }

    /**
     * Convert any instant to the Gaza wall clock used by restaurant hours.
     */
    public static function clockAt(?CarbonInterface $at = null): Carbon
    {
        $moment = $at ? Carbon::instance($at)->copy() : now()->copy();

        return $moment->timezone(self::localTimezone());
    }

    private static function truthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value === 1;
        }

        if (! is_string($value)) {
            return false;
        }

        return in_array(strtolower(trim($value)), ['1', 'true', 'on', 'yes'], true);
    }
}
