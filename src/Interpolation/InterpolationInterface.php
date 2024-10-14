<?php

declare(strict_types=1);

namespace PragmaGoTech\Interview\Interpolation;

use PragmaGoTech\Interview\Model\Fee;

interface InterpolationInterface
{
    /**
     * @param Fee[] $fees
     */
    public function interpolate(float $loanAmount, array $fees): float;
}
