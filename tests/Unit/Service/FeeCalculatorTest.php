<?php

declare(strict_types=1);

namespace Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PragmaGoTech\Interview\Interpolation\LinearInterpolation;
use PragmaGoTech\Interview\Model\LoanProposal;
use PragmaGoTech\Interview\Repository\FeeConstRepository;
use PragmaGoTech\Interview\Service\FeeCalculator;
use PragmaGoTech\Interview\Model\Fee;

#[CoversClass(FeeCalculator::class)]
final class FeeCalculatorTest extends TestCase
{
    private FeeCalculator $feeCalculator;

    /** @psalm-var MockObject&FeeConstRepository */
    private MockObject $feeConstRepositoryMock;

    /** @psalm-var MockObject&LinearInterpolation */
    private MockObject $linearInterpolationMock;

    /** @var Fee[] */
    private array $fees;

    protected function setUp(): void
    {
        $this->feeConstRepositoryMock = $this->createMock(FeeConstRepository::class);
        $this->linearInterpolationMock = $this->createMock(LinearInterpolation::class);
        $this->feeCalculator = new FeeCalculator($this->feeConstRepositoryMock, $this->linearInterpolationMock);
        $this->fees = [
            new Fee(1100, 20),
            new Fee(1300, 30),
            new Fee(1500, 40),
            new Fee(1700, 50),
        ];
    }

    public function testCalculateFeeValueWithoutInterpolation(): void
    {
        $loanProposal = new LoanProposal(24, 1500);

        $this->feeConstRepositoryMock->expects($this->once())
            ->method('findFeesByTerm')
            ->with($loanProposal->term)
            ->willReturn($this->fees);

        $this->linearInterpolationMock->expects($this->never())
            ->method('interpolate');

        $actual = $this->feeCalculator->calculate($loanProposal);
        $expected = 40;
        $this->assertEquals($expected, $actual);
    }

    public function testCalculateFeeValueWithInterpolation(): void
    {
        $loanProposal = new LoanProposal(24, 1400);

        $this->feeConstRepositoryMock->expects($this->once())
            ->method('findFeesByTerm')
            ->with($loanProposal->term)
            ->willReturn($this->fees);

        $this->linearInterpolationMock->expects($this->once())
            ->method('interpolate')
            ->with($loanProposal->amount, $this->fees)
            ->willReturn(35.0);

        $actual = $this->feeCalculator->calculate($loanProposal);
        $expected = 35.0;
        $this->assertEquals($expected, $actual);
    }

    public function testCalculateFeeValueReturnsRoundedFee(): void
    {
        $loanProposal = new LoanProposal(24, 1350);

        $this->feeConstRepositoryMock->expects($this->once())
            ->method('findFeesByTerm')
            ->with($loanProposal->term)
            ->willReturn($this->fees);

        $this->linearInterpolationMock->expects($this->once())
            ->method('interpolate')
            ->with($loanProposal->amount, $this->fees)
            ->willReturn(32.5);

        $actual = $this->feeCalculator->calculate($loanProposal);
        $expected = 35;
        $this->assertEquals($expected, $actual);
    }

    public function testExceptionIsThrownWhenLoanAmountIsBelowMinimum(): void
    {
        $loanProposal = new LoanProposal(24, 900);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The minimum amount for a loan is 1000');

        $this->feeCalculator->calculate($loanProposal);
    }

    public function testExceptionIsThrownWhenLoanAmountIsAboveMaximum(): void
    {
        $loanProposal = new LoanProposal(24, 21000);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The maximum amount for a loan is 20000');

        $this->feeCalculator->calculate($loanProposal);
    }

    public function testExceptionIsThrownWhenLoanTermIsNotValid(): void
    {
        $loanProposal = new LoanProposal(18, 1400);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Loan term must be either 12 or 24 months');

        $this->feeCalculator->calculate($loanProposal);
    }

    public function testExceptionIsThrownWhenFeesNotFound(): void
    {
        $loanProposal = new LoanProposal(24, 1400);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No fees found for term 24');

        $this->feeConstRepositoryMock->expects($this->once())
            ->method('findFeesByTerm')
            ->with($loanProposal->term)
            ->willReturn([]);

        $this->feeCalculator->calculate($loanProposal);
    }
}
