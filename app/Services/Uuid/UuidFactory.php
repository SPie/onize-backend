<?php

namespace App\Services\Uuid;

/**
 * Interface UuidFactory
 *
 * @package App\Services\Uuid
 */
interface UuidFactory
{
    /**
     * @return string
     */
    public function create(): string;
}
