<?php

namespace App\Models;

use App\Services\Uuid\UuidFactory;

/**
 * Trait Uuid
 *
 * @package App\Models
 */
trait UuidCreate
{
    /**
     * @var
     */
    private $uuidFactory;

    /**
     * @return UuidFactory
     */
    private function getUuidFactory(): UuidFactory
    {
        return $this->uuidFactory;
    }
}
