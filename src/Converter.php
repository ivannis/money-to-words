<?php

declare(strict_types=1);

namespace MoneyToWords;

use Webmozart\Assert\Assert;

class Converter
{
    private static array $unitsAndTens = [
        'zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten',
        'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen', 'twenty',
        30 => 'thirty', 40 => 'forty', 50 => 'fifty', 60 => 'sixty', 70 => 'seventy', 80 => 'eighty', 90 => 'ninety',
    ];

    private static array $hundredth = [
        /*pow(10, 2)*/'hundred',
        /*pow(10, 3)*/'thousand',
        /*pow(10, 6)*/'million',
        /*pow(10, 9)*/'billion',
        /*pow(10, 12)*/'trillion',
        /*pow(10, 15)*/'quadrillion',
        /*pow(10, 18)*/'quintillion',
        ///*pow(10, 21)*/ 'sextillion', The max number supported by 64-bit builds in PHP is 9,223,372,036,854,775,807
        ///*pow(10, 24)*/ 'septillion',
        ///*pow(10, 27)*/ 'octillion',
        ///*pow(10, 30)*/ 'nonillion',
        ///*pow(10, 33)*/ 'decillion',
        ///*pow(10, 36)*/ 'undecillion',
        ///*pow(10, 39)*/ 'duodecillion',
        ///*pow(10, 42)*/ 'tredecillion',
        ///*pow(10, 45)*/ 'quattuordecillion',
        ///*pow(10, 48)*/ 'quindecillion',
        ///*pow(10, 51)*/ 'sexdecillion',
        ///*pow(10, 54)*/ 'septemdecillion',
        ///*pow(10, 57)*/ 'octodecillion',
        ///*pow(10, 60)*/ 'novemdecillion',
        ///*pow(10, 63)*/ 'vigintillion',
        ///*pow(10, 66)*/ 'unvigintillion',
        ///*pow(10, 69)*/ 'duovigintillion',
        ///*pow(10, 72)*/ 'trevigintillion',
        ///*pow(10, 75)*/ 'quattuorvigintillion',
        ///*pow(10, 78)*/ 'quinvigintillion',
        /*...*/
    ];

    /**
     * @param float|int $number
     */
    public static function convert($number, string $currency = 'dollars', string $unit = 'cents'): string
    {
        Assert::numeric($number);
        $isNegative = $number < 0;
        $value = abs($number);

        if (static::isDecimal($value)) {
            [
                'integerPart' => $integerPart,
                'decimalPart' => $decimalPart,
            ] = static::integerAndDecimalParts($value);

            $money = sprintf('%s %s', static::numberToString($integerPart), $currency);
            $cents = sprintf('%s %s', static::numberToString($decimalPart), $unit);

            if ($integerPart === 0) {
                if ($decimalPart === 0) { //  0.0
                    return static::format($money, false);
                }

                // 0.x
                return static::format($cents, $isNegative);
            }

            // x.x
            if ($decimalPart > 0) {
                return static::format(
                    sprintf('%s and %s', $money, $cents),
                    $isNegative
                );
            }

            // xxx
            return static::format($money, $isNegative);
        }

        return static::format(
            sprintf('%s %s', static::numberToString(intval($value)), $currency),
            $isNegative
        );
    }

    private static function numberToString(int $number): string
    {
        if ($number < 21) {
            return static::$unitsAndTens[$number];
        }

        if ($number < 100) {
            $remainder = $number % 10;

            return sprintf(
                '%s%s',
                static::$unitsAndTens[$number - $remainder],
                $remainder > 0 ? (' ' . static::numberToString($remainder)) : ''
            );
        }

        $position = intval(log($number, 10) / 3);
        $power = $position == 0 ? 2 : $position * 3;

        $zeros = pow(10, $power);
        $prefix = intval($number / $zeros);
        $remainder = $number % $zeros;

        $and = '';
        if (static::shouldAddAnd($number, $power)) { // should add and?
            $and = ' and';
        }

        return sprintf(
            '%s %s%s',
            static::numberToString($prefix),
            static::$hundredth[$position] . $and,
            $remainder > 0 ? ' ' . static::numberToString($remainder) : ''
        );
    }

    private static function shouldAddAnd(int $number, int $pow): bool
    {
        $remainder = $number % pow(10, $pow);

        return $remainder > 0 && $remainder < 100;
    }

    private static function isDecimal($value): bool
    {
        return strpos((string) $value, '.') !== false;
    }

    private static function integerAndDecimalParts(float $value): array
    {
        $parts = explode('.', (string) $value);

        return [
            'integerPart' => intval($parts[0]), // money part
            'decimalPart' => intval(bcmul(bcsub((string) $value, $parts[0], 2), '100')), // cents part
        ];
    }

    private static function format(string $words, bool $isNegative): string
    {
        return ($isNegative ? 'minus ' : '') . $words;
    }
}
