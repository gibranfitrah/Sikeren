<?php
 
use Illuminate\Database\Seeder;
 
class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call(TasksTableSeeder::class);
        $this->call(LinksTableSeeder::class);
    }
}