<?php

declare(strict_types=1);

namespace Jengo\Schema\Support;

final class StringUtils
{
    /**
     * Convert camelCase to snake_case
     */
    public static function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $input));
    }

    /**
     * Convert snake_case to camelCase
     */
    public static function snakeToCamel(string $input): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
    }

    /**
     * Generate a short hash from a string (6 chars)
     */
    public static function shortHash(string $input): string
    {
        return substr(sha1($input), 0, 6);
    }

    /**
     * Generate a unique alias based on path (array of strings)
     */
    public static function aliasFromPath(array $path, int $depth): string
    {
        $hash = self::shortHash(implode('.', $path));

        return "t_{$depth}_{$hash}";
    }
}
