<?php

namespace App\Repositories\User;

use App\Models\ModelInterface;
use App\Models\User\UserModelInterface;
use App\Repositories\RepositoryInterface;
use Illuminate\Contracts\Auth\UserProvider;

/**
 * Interface UserRepository
 *
 * @package App\Repositories\User
 */
interface UserRepository extends RepositoryInterface, UserProvider
{

    /**
     * @param int $id
     *
     * @return UserModelInterface|ModelInterface|null
     */
    public function find($id): ?ModelInterface;

    /**
     * @param string $email
     *
     * @return UserModelInterface|ModelInterface|null
     */
    public function findOneByEmail(string $email): ?UserModelInterface;

    /**
     * @param ModelInterface $model
     * @param bool           $flush
     *
     * @return UserModelInterface|ModelInterface
     */
    public function save(ModelInterface $model, bool $flush = true): ModelInterface;
}
