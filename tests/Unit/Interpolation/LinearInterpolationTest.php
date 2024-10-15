<?php

declare(strict_types=1);

namespace Tests\Unit\Interpolation;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PragmaGoTech\Interview\Interpolation\LinearInterpolation;
use PragmaGoTech\Interview\Model\Fee;

#[CoversClass(LinearInterpolation::class)]
final class LinearInterpolationTest extends TestCase
{
    private LinearInterpolation $linearInterpolation;

    /** @var Fee[] */
    private array $fees;

    protected function setUp(): void
    {
        $this->linearInterpolation = new LinearInterpolation();
        $this->fees = [
            new Fee(700, 20),
            new Fee(900, 30),
            new Fee(1100, 40),
            new Fee(1300, 50),
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideLoanAmountsAndExpectedFees')]
    public function testInterpolateFeeValue(float $loanAmount, float $expected): void
    {
        $actual = $this->linearInterpolation->interpolate($loanAmount, $this->fees);
        $this->assertEquals($expected, $actual);
    }

    public function testExceptionIsThrownWhenFeesNotGiven(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Fees can not be empty');

        $this->linearInterpolation->interpolate(1000, []);
    }

    public function testExceptionIsThrownWhenFeesBoundariesNotFound(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Can not find boundaries for the loan amount in the given fees');

        $this->linearInterpolation->interpolate(1400, $this->fees);
    }

    public static function provideLoanAmountsAndExpectedFees(): array
    {
        return [
            [950, 32.5],
            [1000, 35.0],
            [1030, 36.5],
        ];
    }
}
