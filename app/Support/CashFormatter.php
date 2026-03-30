<?php

namespace App\Support;

/**
 * Display Unit Value / On Hand Value as cash (₱1,234.00). Parsing tolerates ints, floats, and numeric strings.
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
     * @param  bool  $includeSymbol  Prefix with ₱ (Philippine peso).
     */
    public static function format(mixed $raw, bool $includeSymbol = true): string
    {
        $n = self::toFloat($raw);
        if ($n === null) {
            return '';
        }
        $formatted = number_format($n, 2, '.', ',');

        return $includeSymbol ? ('₱' . $formatted) : $formatted;
    }

    public static function formatOrPlaceholder(mixed $raw, string $placeholder = '—'): string
    {
        $f = self::format($raw);

        return $f !== '' ? $f : $placeholder;
    }

    /**
     * Value for text inputs: no symbol, two decimals, thousands commas (e.g. 1,234.00).
     */
    public static function formatForInput(mixed $raw): string
    {
        if ($raw === null || $raw === '') {
            return '';
        }
        $n = self::toFloat($raw);
        if ($n !== null) {
            return number_format($n, 2, '.', ',');
        }

        return trim((string) $raw);
    }
}
