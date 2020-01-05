<?php

namespace App\Repositories;

use Doctrine\ORM\Query as RealQuery;

/**
 * Class DoctrineQuery
 *
 * @package App\Repositories
 */
final class DoctrineQuery implements Query
{
    /**
     * @var RealQuery
     */
    private $realQuery;

    /**
     * DoctrineQuery constructor.
     *
     * @param RealQuery $realQuery
     */
    public function __construct(RealQuery $realQuery)
    {
        $this->realQuery = $realQuery;
    }

    /**
     * @return RealQuery
     */
    private function getRealQuery(): RealQuery
    {
        return $this->realQuery;
    }

    /**
     * @return mixed
     */
    public function execute()
    {
        return $this->getRealQuery()->getResult();
    }
}
