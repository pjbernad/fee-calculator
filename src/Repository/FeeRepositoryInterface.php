<?php

declare(strict_types=1);

namespace PragmaGoTech\Interview\Repository;

use PragmaGoTech\Interview\Model\Fee;

interface FeeRepositoryInterface
{
    /**
     * @return Fee[]
     */
    public function findFeesByTerm(int $term): array;
}
