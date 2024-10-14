<?php

declare(strict_types=1);

namespace PragmaGoTech\Interview\Service;

use PragmaGoTech\Interview\Model\Fee;
use PragmaGoTech\Interview\Model\LoanProposal;
use PragmaGoTech\Interview\FeeStorage\FeeStorageInterface;
use PragmaGoTech\Interview\Interpolation\InterpolationInterface;

class FeeCalculator implements FeeCalculatorInterface
{
    private function __construct(
        private FeeStorageInterface $feeStorage,
        private InterpolationInterface $interpolationStrategy
    ) {
    }

    /**
     * @inheritDoc
     */
    public function calculate(LoanProposal $loanProposal): float
    {
        $fees = $this->feeStorage->findFeesByTerm($loanProposal->term);
        if (! $fees) {
            throw new \RuntimeException('No fees found for term ' . $loanProposal->term);
        }

        $fee = \array_filter($fees, static fn(Fee $fee) => $fee->loanAmount === $loanProposal->amount);
        if (! $fee) {
            $fee = $this->interpolationStrategy->interpolate($loanProposal->amount, $fees);
        }

        return $fee;
    }
}
