<?php

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Class Version00000000000001
 *
 * @package Database\Migrations
 */
final class Version00000000000001 extends AbstractMigration
{
    /**
     * @param Schema $schema
     *
     * @return void
     */
    public function up(Schema $schema)
    {
        $this->addFieldTypeToProjectMetaDataElements();
    }

    /**
     * @return $this
     */
    private function addFieldTypeToProjectMetaDataElements(): self
    {
//        $this->addSql("ALTER TABLE `project_meta_data_elements` ADD COLUMN `field_type` ENUM('text', 'number', 'date', 'email') DEFAULT 'text'");

        return $this;
    }

    /**
     * @param Schema $schema
     *
     * @return
     */
    public function down(Schema $schema)
    {
        // TODO: Implement down() method.
    }
}
