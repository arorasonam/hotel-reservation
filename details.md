php artisan make:filament-widget StatsOverview

php artisan filament:resource resourceName

php artisan shield:generate --all

php artisan make:filament-importer BookingSourceImporter
php artisan make:filament-importer BookingTypeImporter
php artisan make:filament-importer SourceMarketImporter
php artisan make:filament-importer MealPlanImporter

php artisan vendor:publish --tag=filament-actions-migrations

# Run the migration to create the 'imports' table

php artisan migrate
