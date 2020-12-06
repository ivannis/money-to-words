<?php

declare(strict_types=1);

namespace Tests\MoneyToWords;

use MoneyToWords\Converter;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ConversionTest extends TestCase
{
    protected array $digits = [
        'zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven',
        'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen', 'twenty',
    ];

    protected array $tens = [
        30 => 'thirty', 40 => 'forty', 50 => 'fifty', 60 => 'sixty', 70 => 'seventy', 80 => 'eighty', 90 => 'ninety',
    ];

    protected array $maxInt = [
        2147483647 => 'two billion one hundred and forty seven million four hundred and eighty three thousand six hundred and forty seven',
        9223372036854775807 => 'nine quintillion two hundred and twenty three quadrillion three hundred and seventy two trillion thirty six billion eight hundred and fifty four million seven hundred and seventy five thousand eight hundred and seven',
    ];

    public function tensAndUnitsDataProvider()
    {
        return [
            ...array_map(fn ($key, $value) => [$key, $value], array_keys($this->digits), array_values($this->digits)),
            ...array_map(fn ($key, $value) => [$key, $value], array_keys($this->tens), array_values($this->tens)),
        ];
    }

    public function tensDataProvider()
    {
        return [
            ...array_map(fn ($key, $value) => [$key, $value], array_keys($this->tens), array_values($this->tens)),
        ];
    }

    public function usesCasesDataProvider()
    {
        return [
            [5001001.32, 'five million one thousand and one dollars and thirty two cents'],
            [5000001, 'five million and one dollars'],
            [1099, 'one thousand and ninety nine dollars'],
            [5692701, 'five million six hundred and ninety two thousand seven hundred and one dollars'],
            [4705807, 'four million seven hundred and five thousand eight hundred and seven dollars'],
            [4003100, 'four million three thousand one hundred dollars'],
            [4030100, 'four million thirty thousand one hundred dollars'],
            [0.12, 'twelve cents'],
            ['10.55', 'ten dollars and fifty five cents'],
            [-0.12, 'minus twelve cents'],
            ['08.8638', 'eight dollars and eighty six cents'],
            [234.0005, 'two hundred and thirty four dollars'],
            [0.005, 'zero dollars'],
            [-0.005, 'zero dollars'],
            [0.00, 'zero dollars'],
            [0.10, 'ten cents'],
        ];
    }

    public function usesCasesDifferentMoneyDataProvider()
    {
        return [
            [5001001.32, 'five million one thousand and one pounds and thirty two pence'],
            [5000001, 'five million and one pounds'],
            [1099, 'one thousand and ninety nine pounds'],
            [5692701, 'five million six hundred and ninety two thousand seven hundred and one pounds'],
            [4705807, 'four million seven hundred and five thousand eight hundred and seven pounds'],
            [4003100, 'four million three thousand one hundred pounds'],
            [4030100, 'four million thirty thousand one hundred pounds'],
            [0.12, 'twelve pence'],
            ['10.55', 'ten pounds and fifty five pence'],
            [-0.12, 'minus twelve pence'],
            ['08.8638', 'eight pounds and eighty six pence'],
            [234.0005, 'two hundred and thirty four pounds'],
            [0.005, 'zero pounds'],
            [-0.005, 'zero pounds'],
            [0.00, 'zero pounds'],
            [0.10, 'ten pence'],
        ];
    }

    /**
     * @dataProvider tensAndUnitsDataProvider
     */
    public function testTensAndUnits(int $number, string $expected)
    {
        $this->assertEquals($expected . ' dollars', Converter::convert($number));
        $this->assertEquals($expected . ' dollars', Converter::convert((string) $number));
    }

    /**
     * @dataProvider tensAndUnitsDataProvider
     */
    public function testNegativeTensAndUnits(int $number, string $expected)
    {
        if ($number !== 0) {
            $this->assertEquals('minus ' . $expected . ' dollars', Converter::convert(-$number));
            $this->assertEquals('minus ' . $expected . ' dollars', Converter::convert((string) -$number));
        } else {
            $this->assertEquals($expected . ' dollars', Converter::convert(-$number));
            $this->assertEquals($expected . ' dollars', Converter::convert((string) -$number));
        }
    }

    /**
     * @dataProvider tensDataProvider
     */
    public function testTens(int $number, string $expected)
    {
        $this->assertEquals($expected . ' dollars', Converter::convert($number));
        $this->assertEquals($expected . ' dollars', Converter::convert((string) $number));

        $this->assertEquals('minus ' . $expected . ' dollars', Converter::convert(-$number));
        $this->assertEquals('minus ' . $expected . ' dollars', Converter::convert((string) -$number));

        for ($i = $number + 1; $i < $number + 10; ++$i) {
            $digit = $i - $number;

            // thirty + (one, two ...) + dollars
            $this->assertEquals(
                $expected . ' ' . $this->digits[$digit] . ' dollars',
                Converter::convert($i)
            );

            $this->assertEquals(
                $expected . ' ' . $this->digits[$digit] . ' dollars',
                Converter::convert((string) $i)
            );

            $this->assertEquals(
                'minus ' . $expected . ' ' . $this->digits[$digit] . ' dollars',
                Converter::convert(-$i)
            );

            $this->assertEquals(
                'minus ' . $expected . ' ' . $this->digits[$digit] . ' dollars',
                Converter::convert((string) -$i)
            );
        }
    }

    /**
     * @dataProvider tensAndUnitsDataProvider
     */
    public function testDecimals(int $number, string $expected)
    {
        for ($i = 1; $i < 100; ++$i) {
            $cents = $i / 100;
            $decimal = $number + $cents;

            if ($number === 0) {
                $this->assertEquals(
                    Converter::convert($cents),
                    Converter::convert($decimal)
                );

                $this->assertEquals(
                    Converter::convert($cents),
                    Converter::convert((string) $decimal)
                );

                $this->assertEquals(
                    'minus ' . Converter::convert($cents),
                    Converter::convert(-$decimal)
                );

                $this->assertEquals(
                    'minus ' . Converter::convert($cents),
                    Converter::convert((string) -$decimal)
                );
            } else {
                $this->assertEquals(
                    $expected . ' dollars and ' . Converter::convert($cents),
                    Converter::convert($decimal)
                );

                $this->assertEquals(
                    $expected . ' dollars and ' . Converter::convert($cents),
                    Converter::convert((string) $decimal)
                );

                $this->assertEquals(
                    'minus ' . $expected . ' dollars and ' . Converter::convert($cents),
                    Converter::convert(-$decimal)
                );

                $this->assertEquals(
                    'minus ' . $expected . ' dollars and ' . Converter::convert($cents),
                    Converter::convert((string) -$decimal)
                );
            }
        }
    }

    public function testMaxPHPInt()
    {
        $this->assertEquals($this->maxInt[PHP_INT_MAX] . ' dollars', Converter::convert(PHP_INT_MAX));
        $this->assertEquals('minus ' . $this->maxInt[PHP_INT_MAX] . ' dollars', Converter::convert(-PHP_INT_MAX));
    }

    /**
     * @dataProvider usesCasesDataProvider
     * @param mixed $number
     */
    public function testUsesCases($number, string $expected)
    {
        $this->assertEquals($expected, Converter::convert($number));
    }

    /**
     * @dataProvider usesCasesDifferentMoneyDataProvider
     */
    public function testDifferentMoneyAndUnit($number, string $expected)
    {
        $this->assertEquals($expected, Converter::convert($number, 'pounds', 'pence'));
    }
}
