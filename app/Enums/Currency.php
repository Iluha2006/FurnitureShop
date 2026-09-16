<?php

namespace App\Enums;

/**
 * Backed by ISO 4217 currency codes.
 */
enum Currency: string
{
    case Ruble = 'RUB';

    /**
     * Short display label, e.g. "Руб.".
     */
    public function label(): string
    {
        return match ($this) {
            self::Ruble => 'Руб.',
        };
    }

    public static function fromCode(string $code): self
    {
        return match (strtoupper($code)) {
            'RUB' => self::Ruble,
            default => throw new \InvalidArgumentException("Unsupported currency code [{$code}]."),
        };
    }

    /**
     * Format an amount in this currency, e.g. "25 000 Руб.".
     */
    public function format(int|float|string $amount): string
    {
        return $this->trimmedFormat((float) $amount).' '.$this->label();
    }

    /**
     * Format using a comma decimal separator and space thousands separator,
     * trimming trailing zero kopecks: 25000.00 -> "25 000", 1250.50 -> "1 250,5".
     */
    private function trimmedFormat(float $amount): string
    {
        $formatted = number_format(round($amount, 2), 2, ',', ' ');

        return rtrim(rtrim($formatted, '0'), ',') ?: '0';
    }
}
