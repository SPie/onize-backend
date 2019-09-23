<?php

use App\Services\Uuid\RamseyUuidFactory;
use Mockery as m;
use Mockery\MockInterface;
use Ramsey\Uuid\UuidFactoryInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * Class RamseyUuidFactoryTest
 */
final class RamseyUuidFactoryTest extends TestCase
{
    //region Tests

    /**
     * @return void
     */
    public function testCreate(): void
    {
        $uuid = $this->createUuid($this->getFaker()->uuid);
        $uuidFactory = $this->createUuidFactory();
        $this->mockUuidFactoryUuid4($uuidFactory, $uuid);

        $this->assertEquals($uuid->toString(), $this->getRamseyUuidFactory($uuidFactory)->create());
    }

    //endregion

    /**
     * @param UuidFactoryInterface|null $uuidFactory
     *
     * @return RamseyUuidFactory
     */
    private function getRamseyUuidFactory(UuidFactoryInterface $uuidFactory = null)
    {
        return new RamseyUuidFactory($uuidFactory ?: $this->createUuidFactory());
    }

    /**
     * @return UuidFactoryInterface|MockInterface
     */
    private function createUuidFactory(): UuidFactoryInterface
    {
        return m::spy(UuidFactoryInterface::class);
    }

    /**
     * @param UuidFactoryInterface|MockInterface $uuidFactory
     * @param UuidInterface                      $uuid
     *
     * @return $this
     */
    private function mockUuidFactoryUuid4(MockInterface $uuidFactory, UuidInterface $uuid)
    {
        $uuidFactory
            ->shouldReceive('uuid4')
            ->andReturn($uuid);

        return $this;
    }

    /**
     * @param string $uuidString
     *
     * @return UuidInterface|MockInterface
     */
    private function createUuid(string $uuidString): UuidInterface
    {
        return m::mock(UuidInterface::class)
            ->shouldReceive('toString')
            ->andReturn($uuidString)
            ->getMock();
    }
}
