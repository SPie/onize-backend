<?php

namespace App\Services\Uuid;

use Ramsey\Uuid\UuidFactoryInterface;

/**
 * Class RamseyUuidService
 *
 * @package App\Services\Uuid
 */
final class RamseyUuidFactory implements UuidFactory
{
    /**
     * @var UuidFactoryInterface
     */
    private $uuidFactory;

    /**
     * RamseyUuidService constructor.
     *
     * @param UuidFactoryInterface $uuidFactory
     */
    public function __construct(UuidFactoryInterface $uuidFactory)
    {
        $this->uuidFactory = $uuidFactory;
    }

    /**
     * @return UuidFactoryInterface
     */
    private function getUuidFactory(): UuidFactoryInterface
    {
        return $this->uuidFactory;
    }

    /**
     * @return string
     */
    public function create(): string
    {
        return $this->getUuidFactory()->uuid4()->toString();
    }
}
