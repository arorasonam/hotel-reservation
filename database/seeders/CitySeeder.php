<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\State;
use Exception;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = File::json(database_path('seeders/data/cities.json'));
        $totalCities = count($cities);

        $this->command->info("Seeding {$totalCities} cities...");

        $progressBar = $this->command->getOutput()->createProgressBar($totalCities);
        $progressBar->setFormat('verbose');

        $nullCount = 0;
        foreach ($cities as $city) {
            $country = Country::where('iso2', $city['country_code'])->firstOrFail();
            $state = State::where('ref_id', $city['state_id'])->first();
            $country->cities()->updateOrCreate([
                'name' => Str::ascii($city['name'])
            ], [
                'state_code' => $city['state_code'],
                'natural_name' => $city['name'],
                'ref_id' => $city['id'],
                'state_id' => $state ? $state->id : null,
                'latitude' => $city['latitude'],
                'longitude' => $city['longitude'],
            ]);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("Successfully seeded {$totalCities} cities!");
    }
}
