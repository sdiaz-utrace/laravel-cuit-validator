<?php

namespace Iutrace\Validation\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class Cuit implements Rule
{
    public const TYPES = [
        20,
        23,
        24,
        25,
        26,
        27,
        30,
        33,
        34,
    ];

    protected string $attribute;

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value): bool
    {
        $this->attribute = $attribute;

        return static::validate($value);
    }

    /**
     * @inheritDoc
     */
    public function message(): string
    {
        return static::replacerMessage($this->attribute);
    }

    public static function validate(mixed $value): bool
    {
        if (!is_string($value) || strlen($value) !== 11) {
            return false;
        }

        if (!Str::startsWith($value, static::TYPES)) {
            return false;
        }

        $type = substr($value, 0, 2);
        $identification = substr($value, 2, 8);
        $verifier = substr($value, 10, 1);

        $digits = array_reverse(str_split($type . $identification));

        $multiplier = 2;
        $accumulator = 0;
        foreach ($digits as $digit) {
            $accumulator += (int) $digit * $multiplier;

            $multiplier = ($multiplier + 1) % 8;
            $multiplier = $multiplier === 0 ? 2 : $multiplier;
        }

        $calculatedVerifier = 11 - ($accumulator % 11);

        if ($calculatedVerifier === 11) {
            $calculatedVerifier = 0;
        } elseif ($calculatedVerifier === 10) {
            $calculatedVerifier = 1;
        }

        return (int) $verifier === $calculatedVerifier;
    }

    public static function replacerMessage(string $attribute): string
    {
        return __('iutrace::validation.cuit', [
            'attribute' => $attribute,
        ]);
    }
}
