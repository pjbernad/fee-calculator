<?php

declare(strict_types=1);

namespace PragmaGoTech\Interview\Interpolation;

use PragmaGoTech\Interview\Model\Fee;

/**
 * Estimates a fee value for the loan amount between the lower and upper bound that it fall between
 */
class LinearInterpolation implements InterpolationInterface
{
    /**
     * @param Fee[] $fees
     */
    public function interpolate(float $loanAmount, array $fees): float
    {
        if (empty($fees)) {
            throw new \InvalidArgumentException('Fees can not be empty');
        }

        [$lowerBoundFee, $upperBoundFee] = $this->findLowerAndUpperBounds($loanAmount, $fees);

        if (!$lowerBoundFee || !$upperBoundFee) {
            throw new \RuntimeException('Can not find boundaries for the loan amount in the given fees');
        }

        bcscale(2);

        return (float) bcadd(
            (string) $lowerBoundFee->value,
            bcdiv(
                bcmul(
                    bcsub((string)$loanAmount, (string)$lowerBoundFee->loanAmount),
                    bcsub((string)$upperBoundFee->value, (string)$lowerBoundFee->value)
                ),
                bcsub((string)$upperBoundFee->loanAmount, (string)$lowerBoundFee->loanAmount)
            )
        );
    }

    /**
     * @param Fee[] $fees
     * @return array{0: Fee|null, 1: Fee|null}
     */
    private function findLowerAndUpperBounds(float $loanAmount, array $fees): array
    {
        $lowerBoundAmount = 0;
        $upperBoundAmount = PHP_FLOAT_MAX;
        $lowerBoundFee = null;
        $upperBoundFee = null;

        foreach ($fees as $fee) {
            if ($fee->loanAmount <= $loanAmount && $fee->loanAmount > $lowerBoundAmount) {
                $lowerBoundAmount = $fee->loanAmount;
                $lowerBoundFee = $fee;
            }

            if ($fee->loanAmount >= $loanAmount && $fee->loanAmount < $upperBoundAmount) {
                $upperBoundAmount = $fee->loanAmount;
                $upperBoundFee = $fee;
            }
        }

        return [$lowerBoundFee, $upperBoundFee];
    }
}
