<?php

declare(strict_types=1);

namespace PragmaGoTech\Interview\Service;

use PragmaGoTech\Interview\Repository\FeeRepositoryInterface;
use PragmaGoTech\Interview\Model\Fee;
use PragmaGoTech\Interview\Model\LoanProposal;
use PragmaGoTech\Interview\Interpolation\InterpolationInterface;

class FeeCalculator implements FeeCalculatorInterface
{
    public function __construct(
        private FeeRepositoryInterface $feeRepository,
        private InterpolationInterface $interpolationStrategy
    ) {
    }

    /**
     * @inheritDoc
     */
    public function calculate(LoanProposal $loanProposal): float
    {
        $this->validateLoanProposal($loanProposal);

        $fees = $this->feeRepository->findFeesByTerm($loanProposal->term);
        if (! $fees) {
            throw new \RuntimeException('No fees found for term ' . $loanProposal->term);
        }

        $fee = \current(\array_filter($fees, static fn(Fee $fee): bool => $fee->loanAmount === $loanProposal->amount));
        $feeValue = $fee ? $fee->value : $this->interpolationStrategy->interpolate($loanProposal->amount, $fees);

        return $this->roundUpFeeToMakeSumMultipleOf5($feeValue, $loanProposal->amount);
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function validateLoanProposal(LoanProposal $loanProposal): void
    {
        if ($loanProposal->amount < 1000) {
            throw new \InvalidArgumentException('The minimum amount for a loan is 1000');
        }

        if ($loanProposal->amount > 20000) {
            throw new \InvalidArgumentException('The maximum amount for a loan is 20000');
        }

        if ($loanProposal->term !== 12 && $loanProposal->term !== 24) {
            throw new \InvalidArgumentException('Loan term must be either 12 or 24 months');
        }
    }

    private function roundUpFeeToMakeSumMultipleOf5(float $fee, float $loanAmount): float
    {
        bcscale(2);
        $sum = \bcadd((string)$loanAmount, (string)$fee);
        $remainder = \fmod((float)$sum, 5);
        if ($remainder === 0.0) {
            return $fee;
        }

        return (float)\bcadd(
            (string)$fee,
            \bcsub('5.0', (string)$remainder)
        );
    }
}
