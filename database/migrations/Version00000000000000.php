<?php

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use LaravelDoctrine\Migrations\Schema\Builder;
use LaravelDoctrine\Migrations\Schema\Table;

/**
 * Class Version00000000000000
 *
 * @package Database\Migrations
 */
class Version00000000000000 extends AbstractMigration
{

    //region Up calls

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this
            ->createUsersTable($schema)
            ->createRefreshTokensTable($schema)
            ->createLoginAttemptsTable($schema)
            ->createProjectsTable($schema)
            ->createProjectInvitesTable($schema)
            ->createProjectMembersTable($schema);
    }

    /**
     * @param Schema $schema
     *
     * @return $this
     */
    private function createUsersTable(Schema $schema): self
    {
        (new Builder($schema))->create('users', function (Table $table) {
            $table->increments('id');
            $table->string('uuid');
            $table->unique('uuid');
            $table->string('email');
            $table->unique('email');
            $table->string('password');
            $table->timestamps();
            $table->softDeletes();
        });

        return $this;
    }

    /**
     * @param Schema $schema
     *
     * @return $this
     */
    private function createRefreshTokensTable(Schema $schema): self
    {
        (new Builder($schema))->create('refresh_tokens', function (Table $table) {
            $table->increments('id');
            $table->string('identifier');
            $table->unique('identifier');
            $table->dateTime('valid_until')->setNotnull(false);
            $table->integer('user_id', false, true);
            $table->foreign('users', 'user_id', 'id');
            $table->timestamps();
            $table->index('valid_until');
        });

        return $this;
    }

    /**
     * @param Schema $schema
     *
     * @return $this
     */
    private function createLoginAttemptsTable(Schema $schema): self
    {
        (new Builder($schema))->create('login_attempts', function (Table $table) {
            $table->increments('id');
            $table->string('ip_address');
            $table->string('identifier');
            $table->dateTime('attempted_at');
            $table->boolean('success');
        });

        return $this;
    }

    /**
     * @param Schema $schema
     *
     * @return $this
     */
    private function createProjectsTable(Schema $schema): self
    {
        (new Builder($schema))->create('projects', function (Table $table) {
            $table->increments('id');
            $table->string('uuid');
            $table->unique('uuid');
            $table->string('label');
            $table->string('description')->setNotnull(false);
            $table->integer('user_id', false, true);
            $table->foreign('users', 'user_id', 'id');
            $table->timestamps();
            $table->softDeletes();
        });

        return $this;
    }

    /**
     * @param Schema $schema
     *
     * @return $this
     */
    private function createProjectInvitesTable(Schema $schema): self
    {
        (new Builder($schema))->create('project_invites', function (Table $table) {
            $table->increments('id');
            $table->string('uuid');
            $table->unique('uuid');
            $table->string('token');
            $table->unique('token');
            $table->string('email');
            $table->integer('project_id', false, true);
            $table->foreign('projects', 'project_id', 'id');
            $table->unique(['email', 'project_id']);
            $table->timestamps();
        });

        return $this;
    }

    /**
     * @param Schema $schema
     *
     * @return $this
     */
    private function createProjectMembersTable(Schema $schema): self
    {
        (new Builder($schema))->create('project_members', function (Table $table) {
            $table->increments('id');
            $table->integer('user_id', false, true);
            $table->foreign('users', 'user_id', 'id');
            $table->integer('project_id', false, true);
            $table->foreign('projects', 'project_id', 'id');
            $table->unique(['user_id', 'project_id']);
        });

        return $this;
    }

    //endregion

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        //no rollback
    }
}
