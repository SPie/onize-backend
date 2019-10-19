<?php

namespace App\Models;

use Doctrine\ORM\Mapping;

/**
 * Class AbstractDoctrineModel
 *
 * @package App\Models
 */
class AbstractDoctrineModel implements ModelInterface
{

    /**
     * @Mapping\Id
     * @Mapping\GeneratedValue
     * @Mapping\Column(type="integer")
     *
     * @var int
     */
    public $id;

    /**
     * @param int|null $id
     *
     * @return $this
     */
    public function setId(?int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getId() : ?int
    {
        return $this->id;
    }

    /**
     * @param int $depth
     *
     * @return array
     */
    public function toArray(int $depth = 1): array
    {
        return [];
    }
}
