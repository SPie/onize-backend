<?php

namespace Test;

use App\Models\User\RefreshTokenDoctrineModel;
use Illuminate\Support\Collection;

/**
 * Trait AuthIntegrationHelper
 *
 * @package Test
 *
 * @method Collection createModels(string $class, int $times, array $data)
 */
trait AuthIntegrationHelper
{

    /**
     * @param int   $times
     * @param array $data
     *
     * @return RefreshTokenDoctrineModel[]|Collection
     */
    protected function createRefreshTokens(int $times = 1, array $data = []): Collection
    {
        return $this->createModels(RefreshTokenDoctrineModel::class, $times, $data);
    }
}
