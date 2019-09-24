<?php

namespace App\Models;

/**
 * Interface Uuidable
 *
 * @package App\Models
 */
interface Uuidable
{
    const PROPERTY_UUID = 'uuid';

    /**
     * @param string $uuid
     *
     * @return Uuidable
     */
    public function setUuid(string $uuid): Uuidable;

    /**
     * @return string
     */
    public function getUuid(): string;
}
