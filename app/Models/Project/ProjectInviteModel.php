<?php

namespace App\Models\Project;

use App\Models\ModelInterface;

/**
 * Interface ProjectInviteModel
 *
 * @package App\Models\Project
 */
interface ProjectInviteModel extends ModelInterface
{
    /**
     * @param string $token
     *
     * @return $this
     */
    public function setToken(string $token): self;

    /**
     * @return string
     */
    public function getToken(): string;
}
