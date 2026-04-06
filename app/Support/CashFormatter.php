<?php

namespace App\Support;

/**
 * Display Unit Value / On Hand Value as cash (₱1,234.00). Parsing tolerates ints, floats, and numeric strings.
 * Preserves the original decimal precision from CSV data — no rounding.
 */
final class CashFormatter
{
    public static function toFloat(mixed $raw): ?float
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        if (is_int($raw) || is_float($raw)) {
            return (float) $raw;
        }
        $s = trim((string) $raw);
        $s = str_replace(',', '', preg_replace('/\s+/', '', $s) ?? '');
        if ($s === '' || ! is_numeric($s)) {
            return null;
        }

        return (float) $s;
    }

    /**
     * Count the number of decimal places in the original raw value.
     * Returns at least 2 so that whole numbers still show as e.g. 1,234.00.
     */
    private static function decimalPlaces(mixed $raw): int
    {
        $s = trim((string) $raw);
        // Strip commas / whitespace but keep the decimal point
        $s = str_replace(',', '', preg_replace('/\s+/', '', $s) ?? '');
        $dotPos = strpos($s, '.');
        if ($dotPos === false) {
            return 2; // no decimals → default to 2
        }
        return max(2, strlen($s) - $dotPos - 1);
    }

    /**
     * @param  bool  $includeSymbol  Prefix with ₱ (Philippine peso).
     */
    public static function format(mixed $raw, bool $includeSymbol = true): string
    {
        $n = self::toFloat($raw);
        if ($n === null) {
            return '';
        }
        $decimals = self::decimalPlaces($raw);
        $formatted = number_format($n, $decimals, '.', ',');

        return $includeSymbol ? ('₱' . $formatted) : $formatted;
    }

    public static function formatOrPlaceholder(mixed $raw, string $placeholder = '—'): string
    {
        $f = self::format($raw);

        return $f !== '' ? $f : $placeholder;
    }

    /**
     * Value for text inputs: no symbol, original decimals preserved, thousands commas.
     */
    public static function formatForInput(mixed $raw): string
    {
        if ($raw === null || $raw === '') {
            return '';
        }
        $n = self::toFloat($raw);
        if ($n !== null) {
            $decimals = self::decimalPlaces($raw);
            return number_format($n, $decimals, '.', ',');
        }

        return trim((string) $raw);
    }
}
