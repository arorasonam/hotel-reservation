<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = File::json(database_path('seeders/data/states.json'));
        $totalStates = count($states);

        $this->command->info("Seeding {$totalStates} states...");

        $progressBar = $this->command->getOutput()->createProgressBar($totalStates);
        $progressBar->setFormat('verbose');

        foreach ($states as $state) {
            $country = Country::where('iso2', $state['country_code'])->firstOrFail();
            $country->states()->updateOrCreate([
                'name' => $state['name'],
            ], [
                'type' => $state['type'],
                'ref_id' => $state['id'],
                'latitude' => $state['latitude'],
                'longitude' => $state['longitude'],
            ]);
            $progressBar->advance();
        }


        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("Successfully seeded {$totalStates} states!");
    }
}
