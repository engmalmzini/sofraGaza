<?php

namespace App\Support;

use App\Models\MenuItem;
use RuntimeException;

class MenuOptions
{
    /**
     * @param  mixed  $raw
     * @return list<array{name: string, required: bool, type: string, choices: list<array{name: string, price: float}>}>
     */
    public static function normalize(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $groups = [];
        foreach ($raw as $group) {
            if (! is_array($group)) {
                continue;
            }

            $name = trim((string) ($group['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $type = ($group['type'] ?? 'single') === 'multi' ? 'multi' : 'single';
            $required = self::truthy($group['required'] ?? ($type === 'single'));
            $choices = [];

            foreach ((array) ($group['choices'] ?? []) as $choice) {
                if (! is_array($choice)) {
                    continue;
                }
                $choiceName = trim((string) ($choice['name'] ?? ''));
                if ($choiceName === '') {
                    continue;
                }
                $choices[] = [
                    'name' => mb_substr($choiceName, 0, 180),
                    'price' => round(max(0, (float) ($choice['price'] ?? 0)), 2),
                ];
            }

            if ($choices === []) {
                continue;
            }

            $groups[] = [
                'name' => mb_substr($name, 0, 80),
                'required' => $required,
                'type' => $type,
                'choices' => $choices,
            ];
        }

        return $groups;
    }

    /**
     * @return list<array{name: string, required: bool, type: string, choices: list<array{name: string, price: float}>}>|null
     */
    public static function fromRequest(mixed $raw): ?array
    {
        $groups = self::normalize($raw);

        return $groups === [] ? null : $groups;
    }

    /**
     * @param  mixed  $posted  option_groups[groupIndex] = optionIndex or array of indexes
     * @return array{choices: list<array{group: string, name: string, price: float}>, unit_price: float}
     */
    public static function resolve(MenuItem $item, mixed $posted): array
    {
        $groups = $item->optionGroups();
        $posted = is_array($posted) ? $posted : [];
        $choices = [];

        foreach ($groups as $gi => $group) {
            $raw = $posted[$gi] ?? $posted[(string) $gi] ?? null;
            $indexes = self::postedIndexes($raw, $group['type'] === 'multi');

            if ($indexes === []) {
                if ($group['required']) {
                    throw new RuntimeException('اختر '.$group['name'].' للصنف '.$item->name.'.');
                }
                continue;
            }

            foreach ($indexes as $oi) {
                $option = $group['choices'][$oi] ?? null;
                if (! $option) {
                    throw new RuntimeException('اختيار غير صالح في قسم '.$group['name'].'.');
                }
                $choices[] = [
                    'group' => $group['name'],
                    'name' => $option['name'],
                    'price' => (float) $option['price'],
                ];
            }
        }

        return [
            'choices' => $choices,
            'unit_price' => round((float) $item->price + self::extras($choices), 2),
        ];
    }

    /**
     * @param  list<array{price?: float|int|string}>  $choices
     */
    public static function extras(array $choices): float
    {
        $sum = 0.0;
        foreach ($choices as $choice) {
            $sum += (float) ($choice['price'] ?? 0);
        }

        return round($sum, 2);
    }

    /**
     * @param  list<array{group?: string, name?: string}>  $choices
     */
    public static function fingerprint(array $choices): string
    {
        $parts = [];
        foreach ($choices as $choice) {
            $parts[] = ($choice['group'] ?? '').'='.($choice['name'] ?? '');
        }
        sort($parts);

        return $parts === [] ? '' : substr(sha1(implode('|', $parts)), 0, 12);
    }

    /**
     * @param  list<array{group?: string, name?: string}>  $choices
     */
    public static function lineKey(int $itemId, array $choices = []): string
    {
        $fp = self::fingerprint($choices);

        return $fp === '' ? (string) $itemId : $itemId.':'.$fp;
    }

    /**
     * @param  list<array{group?: string, name?: string, price?: float|int|string}>  $choices
     */
    public static function label(array $choices): string
    {
        $names = [];
        foreach ($choices as $choice) {
            $name = trim((string) ($choice['name'] ?? ''));
            if ($name !== '') {
                $names[] = $name;
            }
        }

        return implode(' · ', $names);
    }

    /**
     * @param  list<array{required?: bool, choices?: list<array{price?: float|int|string}>}>  $groups
     */
    public static function startingExtra(array $groups): float
    {
        $extra = 0.0;
        foreach ($groups as $group) {
            if (! ($group['required'] ?? false)) {
                continue;
            }
            $prices = array_map(fn ($choice) => (float) ($choice['price'] ?? 0), $group['choices'] ?? []);
            if ($prices === []) {
                continue;
            }
            $extra += min($prices);
        }

        return round($extra, 2);
    }

    /**
     * @return list<int>
     */
    private static function postedIndexes(mixed $raw, bool $multi): array
    {
        if ($raw === null || $raw === '' || $raw === false) {
            return [];
        }

        $values = is_array($raw) ? $raw : [$raw];
        $indexes = [];
        foreach ($values as $value) {
            if (! is_numeric($value)) {
                continue;
            }
            $indexes[] = (int) $value;
        }
        $indexes = array_values(array_unique($indexes));

        if (! $multi) {
            return array_slice($indexes, 0, 1);
        }

        return $indexes;
    }

    private static function truthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array($value, [1, '1', 'on', 'true', 'yes'], true);
    }
}
