<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

/**
 * Cache Schema::hasTable lookups to avoid repeated information_schema queries.
 *
 * In production this can noticeably reduce latency, especially on higher RTT
 * connections (e.g. remote Postgres).
 */
final class SchemaCache
{
    /**
     * @var array<string, bool>
     */
    private static array $hasTable = [];

    public static function hasTable(string $table): bool
    {
        if (array_key_exists($table, self::$hasTable)) {
            return self::$hasTable[$table];
        }

        try {
            $exists = Schema::hasTable($table);
        } catch (\Throwable) {
            // Don't cache failures (DB may become available later).
            return false;
        }

        return self::$hasTable[$table] = $exists;
    }
}

