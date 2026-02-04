<?php

namespace App\Services;

class BjjAgeCategoryService
{
    /**
     * Get BJJ age category label from birth year. Ranges shift by one year each calendar year.
     */
    public static function getCategory(int $birthYear, ?int $currentYear = null): ?string
    {
        $currentYear = $currentYear ?? (int) date('Y');
        $config = config('bjj_age_categories');
        $baseYear = $config['base_year'] ?? 2025;
        $offset = $currentYear - $baseYear;

        foreach ($config['kids'] ?? [] as $cat) {
            $min = ($cat['min'] ?? 0) + $offset;
            $max = ($cat['max'] ?? 0) + $offset;
            if ($birthYear >= $min && $birthYear <= $max) {
                return $cat['name'];
            }
        }

        foreach ($config['adults'] ?? [] as $cat) {
            $min = $cat['min'] !== null ? ($cat['min'] + $offset) : null;
            $max = $cat['max'] !== null ? ($cat['max'] + $offset) : null;
            if ($min !== null && $max !== null && $birthYear >= $min && $birthYear <= $max) {
                return $cat['name'];
            }
            if ($min === null && $max !== null && $birthYear <= $max) {
                return $cat['name'];
            }
        }

        return null;
    }
}
