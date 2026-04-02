<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = File::json(database_path('seeders/data/countries.json'));
        $totalCountries = count($countries);

        $this->command->info("Seeding {$totalCountries} countries...");

        $progressBar = $this->command->getOutput()->createProgressBar($totalCountries);
        $progressBar->setFormat('verbose');

        foreach ($countries as $country) {
            Country::updateOrCreate(
                [
                    'iso2' => $country['iso2'],
                    'name' => $country['name'],
                    'iso3' => $country['iso3'],
                ],
                [
                    'ref_id' => $country['id'],
                    'capital' => $country['capital'],
                    'region' => $country['region'],
                    'subregion' => $country['subregion'],
                    'timezones' => $country['timezones'],
                    'emoji' => $country['emoji'],
                    'currency' => $country['currency'],
                ]
            );


            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("Successfully seeded {$totalCountries} countries!");
    }
}
