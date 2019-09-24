<?php

namespace App\Models;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait Uuid
 *
 * @package App\Models
 */
trait Uuid
{
    /**
     * @ORM\Column(name="uuid", type="string", length=255, nullable=false)
     *
     * @var string
     */
    private $uuid;

    /**
     * @param string $uuid
     *
     * @return $this|Uuidable
     */
    public function setUuid(string $uuid): Uuidable
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }
}
