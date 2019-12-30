<?php

use App\Repositories\DoctrineQuery;
use App\Repositories\DoctrineQueryBuilder;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder as RealQueryBuilder;
use Doctrine\ORM\Query as RealQuery;
use Mockery as m;
use Mockery\MockInterface;
use Test\ReflectionMethodHelper;
use Test\RepositoryHelper;

/**
 * Class DoctrineQueryBuilderTest
 */
final class DoctrineQueryBuilderTest extends TestCase
{
    use ReflectionMethodHelper;
    use RepositoryHelper;

    //region Tests

    /**
     * @return void
     */
    public function testUpdate(): void
    {
        $table = $this->getFaker()->word;
        $newRealQueryBuilder = $this->createRealQueryBuilder();
        $realQueryBuilder = $this->createRealQueryBuilder();
        $this->mockRealQueryBuilderUpdate($realQueryBuilder, $newRealQueryBuilder, $table);
        $queryBuilder = $this->getDoctrineQueryBuilder($realQueryBuilder);

        $queryBuilder->update($table);

        $this->assertEquals($newRealQueryBuilder, $this->getPrivateProperty($queryBuilder, 'realQueryBuilder'));
    }

    /**
     * @return void
     */
    public function testSet(): void
    {
        $column = $this->getFaker()->word;
        $value = $this->getFaker()->word;
        $newRealQueryBuilder = $this->createRealQueryBuilder();
        $realQueryBuilder = $this->createRealQueryBuilder();
        $this->mockRealQueryBuilderSet($realQueryBuilder, $newRealQueryBuilder, $column, $value);
        $queryBuilder = $this->getDoctrineQueryBuilder($realQueryBuilder);

        $queryBuilder->set($column, $value);

        $this->assertEquals($newRealQueryBuilder, $this->getPrivateProperty($queryBuilder, 'realQueryBuilder'));
    }

    /**
     * @return void
     */
    public function testWhere(): void
    {
        $expression = $this->getFaker()->word;
        $newRealQueryBuilder = $this->createRealQueryBuilder();
        $realQueryBuilder = $this->createRealQueryBuilder();
        $this->mockRealQueryBuilderWhere($realQueryBuilder, $newRealQueryBuilder, $expression);
        $queryBuilder = $this->getDoctrineQueryBuilder($realQueryBuilder);

        $queryBuilder->where($expression);

        $this->assertEquals($newRealQueryBuilder, $this->getPrivateProperty($queryBuilder, 'realQueryBuilder'));
    }

    /**
     * @return void
     */
    public function testAndWhere(): void
    {
        $expression = $this->getFaker()->word;
        $newRealQueryBuilder = $this->createRealQueryBuilder();
        $realQueryBuilder = $this->createRealQueryBuilder();
        $this->mockRealQueryBuilderAndWhere($realQueryBuilder, $newRealQueryBuilder, $expression);
        $queryBuilder = $this->getDoctrineQueryBuilder($realQueryBuilder);

        $queryBuilder->andWhere($expression);

        $this->assertEquals($newRealQueryBuilder, $this->getPrivateProperty($queryBuilder, 'realQueryBuilder'));
    }

    /**
     * @return void
     */
    public function testSetParameter(): void
    {
        $parameter = $this->getFaker()->word;
        $value = $this->getFaker()->word;
        $newRealQueryBuilder = $this->createRealQueryBuilder();
        $realQueryBuilder = $this->createRealQueryBuilder();
        $this->mockRealQueryBuilderSetParameter($realQueryBuilder, $newRealQueryBuilder, $parameter, $value);
        $queryBuilder = $this->getDoctrineQueryBuilder($realQueryBuilder);

        $queryBuilder->setParameter($parameter, $value);

        $this->assertEquals($newRealQueryBuilder, $this->getPrivateProperty($queryBuilder, 'realQueryBuilder'));
    }

    /**
     * @return void
     */
    public function testGetQuery(): void
    {
        $realQuery = $this->createRealQuery();
        $realQueryBuilder = $this->createRealQueryBuilder();
        $this->mockRealQueryBuilderGetQuery($realQueryBuilder, $realQuery);
        $queryBuilder = $this->getDoctrineQueryBuilder($realQueryBuilder);

        $this->assertEquals(new DoctrineQuery($realQuery), $queryBuilder->getQuery());
    }

    //endregion

    /**
     * @param RealQueryBuilder|null $realQueryBuilder
     *
     * @return DoctrineQueryBuilder
     */
    private function getDoctrineQueryBuilder(RealQueryBuilder $realQueryBuilder = null): DoctrineQueryBuilder
    {
        return new DoctrineQueryBuilder($realQueryBuilder ?: $this->createRealQueryBuilder());
    }

    /**
     * @return RealQueryBuilder
     */
    private function createRealQueryBuilder(): RealQueryBuilder
    {
        return m::spy(RealQueryBuilder::class);
    }

    /**
     * @param RealQueryBuilder|MockInterface $realQueryBuilder
     * @param RealQueryBuilder               $newRealQueryBuilder
     * @param string                         $table
     *
     * @return $this
     */
    private function mockRealQueryBuilderUpdate(
        MockInterface $realQueryBuilder,
        RealQueryBuilder $newRealQueryBuilder,
        string $table
    ): self {
        $realQueryBuilder
            ->shouldReceive('update')
            ->with($table)
            ->andReturn($newRealQueryBuilder);

        return $this;
    }

    /**
     * @param RealQueryBuilder|MockInterface $realQueryBuilder
     * @param RealQueryBuilder               $newRealQueryBuilder
     * @param string                         $column
     * @param string                         $value
     *
     * @return $this
     */
    private function mockRealQueryBuilderSet(
        MockInterface $realQueryBuilder,
        RealQueryBuilder $newRealQueryBuilder,
        string $column,
        string $value
    ): self {
        $realQueryBuilder
            ->shouldReceive('set')
            ->with($column, $value)
            ->andReturn($newRealQueryBuilder);

        return $this;
    }

    /**
     * @param RealQueryBuilder|MockInterface $realQueryBuilder
     * @param RealQueryBuilder               $newRealQueryBuilder
     * @param string                         $expression
     *
     * @return $this
     */
    private function mockRealQueryBuilderWhere(
        MockInterface $realQueryBuilder,
        RealQueryBuilder $newRealQueryBuilder,
        string $expression
    ): self {
        $realQueryBuilder
            ->shouldReceive('where')
            ->with($expression)
            ->andReturn($newRealQueryBuilder);

        return $this;
    }

    /**
     * @param RealQueryBuilder|MockInterface $realQueryBuilder
     * @param RealQueryBuilder               $newRealQueryBuilder
     * @param string                         $expression
     *
     * @return $this
     */
    private function mockRealQueryBuilderAndWhere(
        MockInterface $realQueryBuilder,
        RealQueryBuilder $newRealQueryBuilder,
        string $expression
    ): self {
        $realQueryBuilder
            ->shouldReceive('andWhere')
            ->with($expression)
            ->andReturn($newRealQueryBuilder);

        return $this;
    }

    /**
     * @param RealQueryBuilder|MockInterface    $realQueryBuilder
     * @param RealQueryBuilder $newRealQueryBuilder
     * @param string           $parameter
     * @param mixed            $value
     *
     * @return $this
     */
    private function mockRealQueryBuilderSetParameter(
        MockInterface $realQueryBuilder,
        RealQueryBuilder $newRealQueryBuilder,
        string $parameter,
        $value
    ): self {
        $realQueryBuilder
            ->shouldReceive('setParameter')
            ->with($parameter, $value)
            ->andReturn($newRealQueryBuilder);

        return $this;
    }

    /**
     * @param RealQueryBuilder|MockInterface $realQueryBuilder
     * @param RealQuery                      $realQuery
     *
     * @return $this
     */
    private function mockRealQueryBuilderGetQuery(MockInterface $realQueryBuilder, RealQuery $realQuery): self
    {
        $realQueryBuilder
            ->shouldReceive('getQuery')
            ->andReturn($realQuery);

        return $this;
    }

    /**
     * @return RealQuery
     */
    private function createRealQuery(): RealQuery
    {
        return new RealQuery($this->createEntityManagerWithConfiguration());
    }

    /**
     * @return EntityManagerInterface|MockInterface
     */
    private function createEntityManagerWithConfiguration(): EntityManagerInterface
    {
        $configuration = m::spy(Configuration::class);
        return $this->createEntityManager()
            ->shouldReceive('getConfiguration')
            ->andReturn($configuration)
            ->getMock();
    }
}
