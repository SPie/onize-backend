<?php

namespace App\Repositories;

/**
 * Interface Query
 *
 * @package App\Repositories
 */
interface Query
{
    /**
     * @return mixed
     */
    public function execute();
}
