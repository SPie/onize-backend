<?php

namespace App\Models;

/**
 * Interface DepthArrayable
 *
 * @package App\Models
 */
interface Arrayable
{
    /**
     * @param int $depth
     *
     * @return array
     */
    public function toArray(int $depth = 1): array;
}
