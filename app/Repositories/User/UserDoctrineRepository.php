<?php

namespace App\Repositories\User;

use App\Models\ModelInterface;
use App\Models\User\UserModelInterface;
use App\Repositories\AbstractDoctrineRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserDoctrineRepository
 *
 * @package App\Repositories\User
 */
class UserDoctrineRepository extends AbstractDoctrineRepository implements UserRepository
{

    /**
     * @param string $email
     *
     * @return UserModelInterface|ModelInterface|null
     */
    public function findOneByEmail(string $email): ?UserModelInterface
    {
        return $this->findOneBy([UserModelInterface::PROPERTY_EMAIL => $email]);
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     *
     * @return Authenticatable|UserModelInterface|ModelInterface|null
     */
    public function retrieveById($identifier)
    {
        return $this->findOneByEmail($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string $token
     *
     * @return Authenticatable|UserModelInterface|ModelInterface|null
     */
    public function retrieveByToken($identifier, $token)
    {
        return $this->findOneByEmail($identifier);
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  Authenticatable $user
     * @param  string          $token
     *
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        //do nothing
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     *
     * @return Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (!$this->hasCredentials($credentials)) {
            return null;
        }

        $user = $this->findOneByEmail($credentials[UserModelInterface::PROPERTY_EMAIL]);
        if (!$user || !$this->hasValidPassword($user, $credentials[UserModelInterface::PROPERTY_PASSWORD])) {
            return null;
        }

        return $user;
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  Authenticatable $user
     * @param  array           $credentials
     *
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return (
            $this->hasCredentials($credentials)
            && $user->getAuthIdentifier() == $credentials[UserModelInterface::PROPERTY_EMAIL]
            && $this->hasValidPassword($user, $credentials[UserModelInterface::PROPERTY_PASSWORD])
        );
    }

    protected function hasCredentials(array $credentials): bool
    {
        return (
            !empty($credentials[UserModelInterface::PROPERTY_EMAIL])
            && !empty($credentials[UserModelInterface::PROPERTY_PASSWORD])
        );
    }

    protected function hasValidPassword(Authenticatable $user, string $password): bool
    {
        return Hash::check($password, $user->getAuthPassword());
    }
}
