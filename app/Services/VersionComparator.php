<?php

namespace App\Services;

class VersionComparator
{
    /*
     * Перевіряємо, чи є зміни між старими та новими даними для вказаних атрибутів.
     */
    public static function hasChanges(array $oldData, array $newData, array $attributes): bool
    {
        foreach ($attributes as $attribute) {
            $oldValue = $oldData[$attribute] ?? '';
            $newValue = $newData[$attribute] ?? '';

            if (self::hash($oldValue) !== self::hash($newValue)) {
                return true;
            }
        }

        return false;
    }

    /*
     * Нормалізуємо значення: обрізаємо пробіли на початку та в кінці, а також замінюємо послідовності пробілів одним пробілом.
     */
    public static function normalize($value): string
    {
        if (!is_string($value)) {
            $value = (string) $value;
        }

        $value = trim($value);

        return preg_replace('/\s+/', ' ', $value);
    }

    /*
     * Створюємо MD5-хеш нормалізованого значення для порівняння.
     */
    public static function hash($value): string
    {
        $normalized = self::normalize($value);
        return md5($normalized);
    }
}
