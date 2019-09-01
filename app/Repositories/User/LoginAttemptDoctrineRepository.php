<?php

namespace App\Repositories\User;

use App\Models\User\LoginAttemptModel;
use App\Repositories\AbstractDoctrineRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Illuminate\Support\Collection;

/**
 * Class LoginAttemptDoctrineRepository
 *
 * @package App\Repositories\User
 */
final class LoginAttemptDoctrineRepository extends AbstractDoctrineRepository implements LoginAttemptRepository
{
    /**
     * @param string             $ipAddress
     * @param string             $identifier
     * @param \DateTimeImmutable $since
     *
     * @return LoginAttemptModel[]|Collection
     */
    public function getLoginAttemptsForUserSince(
        string $ipAddress,
        string $identifier,
        \DateTimeImmutable $since
    ): Collection {
        return $this->findByCriteria(
            (new Criteria())
                ->where(new Comparison(LoginAttemptModel::PROPERTY_IP_ADDRESS, Comparison::EQ, $ipAddress))
                ->andWhere(new Comparison(LoginAttemptModel::PROPERTY_IDENTIFIER, Comparison::EQ, $identifier))
                ->andWhere(new Comparison(LoginAttemptModel::PROPERTY_ATTEMPTED_AT, Comparison::GTE, $since))
                ->orderBy([LoginAttemptModel::PROPERTY_ID => Criteria::DESC])
        );
    }
}