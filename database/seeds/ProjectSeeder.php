<?php

use App\Models\Project\ProjectDoctrineModel;
use Illuminate\Database\Seeder;

/**
 * Class ProjectSeeder
 */
final class ProjectSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run(): void
    {
        entity(ProjectDoctrineModel::class, 5)->create();
    }
}
