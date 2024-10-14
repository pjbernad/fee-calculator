<?php

declare(strict_types=1);

namespace PragmaGoTech\Interview\Model;

/**
 * A cut down version of a loan application containing
 * only the required properties for this test.
 */
readonly class LoanProposal
{
    public function __construct(
        public int $term,
        public float $amount
    ) {
    }
}
