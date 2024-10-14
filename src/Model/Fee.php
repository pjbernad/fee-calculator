<?php

declare(strict_types=1);

namespace PragmaGoTech\Interview\Model;

readonly class Fee
{
    public function __construct(
        public float $loanAmount,
        public float $value
    ) {
    }
}
