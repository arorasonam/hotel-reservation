<?php

namespace App\Helpers;

use App\Filament\Resources\Cities\CityResource;
use App\Filament\Resources\Countries\CountryResource;
use App\Filament\Resources\States\StateResource;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class FilamentHelperLocationable
{

    public static function getLocationableInfolistEntries()
    {
        return   Section::make('Location')
            ->icon('heroicon-o-map-pin')
            ->columns(2)
            ->schema([
                TextEntry::make('locationable.name')
                    ->label('Location')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(function ($record) {
                        if ($record->locationable instanceof Country) {
                            return $record->locationable->emoji . ' ' . $record->locationable->name;
                        } elseif ($record->locationable instanceof State) {
                            return $record->locationable->name . ' (' . $record->locationable->country->iso2 . ')';
                        } elseif ($record->locationable instanceof City) {
                            return $record->locationable->name . ' (' . $record->locationable->country->iso2 . ')';
                        }

                        return $record->locationable?->name ?? '-';
                    })
                    // ->url(function ($record) {
                    //     if ($record->locationable instanceof Country) {
                    //         return CountryResource::getUrl('view', ['record' => $record->locationable]);
                    //     } elseif ($record->locationable instanceof State) {
                    //         return StateResource::getUrl('view', ['record' => $record->locationable]);
                    //     } elseif ($record->locationable instanceof City) {
                    //         return CityResource::getUrl('view', ['record' => $record->locationable]);
                    //     }

                    //     return null;
                    // })
                    ->openUrlInNewTab(false),

                TextEntry::make('locationable.country.name')
                    ->label('Country')
                    ->badge()
                    ->color('info')
                    ->visible(fn($record) => $record->locationable instanceof State || $record->locationable instanceof City)
                    ->formatStateUsing(function ($record) {
                        $country = null;

                        if ($record->locationable instanceof State || $record->locationable instanceof City) {
                            $country = $record->locationable->country ?? null;
                        }

                        return $country
                            ? $country->emoji . ' ' . $country->name
                            : '-';
                    })
                    ->url(function ($record) {
                        if ($record->locationable instanceof State || $record->locationable instanceof City) {
                            return CountryResource::getUrl('view', ['record' => $record->locationable->country]);
                        }

                        return null;
                    })
                    ->openUrlInNewTab(false),

                TextEntry::make('locationable_type')
                    ->label('Location Type')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn($state) => class_basename($state)),
            ]);
    }



    public static function getLocationableTableColumns(): array
    {
        return [
            TextColumn::make('locationable_type')
                ->label('Type')
                ->badge()
                ->color('info')
                ->formatStateUsing(fn($state) => class_basename($state))
                ->searchable(),
            TextColumn::make('locationable.name')
                ->label('Location')
                ->searchable()
                ->sortable()
                ->formatStateUsing(function ($record) {
                    if ($record->locationable instanceof Country) {
                        return $record->locationable->emoji . ' ' . $record->locationable->name;
                    } elseif ($record->locationable instanceof State) {
                        return $record->locationable->name . ' (' . $record->locationable->country->iso2 . ')';
                    } elseif ($record->locationable instanceof City) {
                        return $record->locationable->name . ' (' . $record->locationable->state?->name . '/' . $record->locationable->country->iso2 . ')';
                    }
                    return $record->locationable?->name ?? '-';
                })
                // ->url(function ($record) {
                //     if ($record->locationable instanceof Country) {
                //         return CountryResource::getUrl('view', ['record' => $record->locationable]);
                //     } elseif ($record->locationable instanceof State) {
                //         return StateResource::getUrl('view', ['record' => $record->locationable]);
                //     } elseif ($record->locationable instanceof City) {
                //         return CityResource::getUrl('view', ['record' => $record->locationable]);
                //     }
                //     return null;
                // })
                ->openUrlInNewTab(false)
        ];
    }

    public static function getCountrySelectFilterTable()
    {
        return  SelectFilter::make('country')
            ->label('Country')
            ->multiple()
            ->searchable()
            ->preload()
            ->getSearchResultsUsing(
                fn(string $search): array => Country::query()
                    ->where('name', 'ilike', "%{$search}%")
                    ->orderBy('name')
                    ->limit(50)
                    ->pluck('name', 'id')
                    ->all()
            )
            ->getOptionLabelsUsing(fn(array $values): array => Country::query()->whereIn('id', $values)->pluck('name', 'id')->all())
            ->query(function (Builder $query, array $data) {v
                $countryId = $data['values'] ?? [];
                if (count($countryId) == 0) return $query;
                $query->whereHasMorph('locationable', [Country::class], fn($q) => $q->whereIn('id', $countryId));
                $query->orWhereHasMorph('locationable', [State::class, City::class], fn($q) => $q->whereIn('country_id', $countryId));
                return $query;q
            });
    }



    public static function getStateSelectFilterTable()
    {
        return SelectFilter::make('state')
            ->label('State')
            ->multiple()
            ->searchable()
            ->preload()
            ->getSearchResultsUsing(
                fn(string $search): array => State::query()
                    ->where('name', 'ilike', "%{$search}%")
                    ->with('country')
                    ->orderBy('name')
                    ->limit(50)
                    ->get()
                    ->mapWithKeys(fn(State $state) => [$state->id => $state->name . ' (' . $state->country?->iso2 . ')',])
                    ->all()
            )
            ->getOptionLabelsUsing(
                fn(array $values): array => State::query()
                    ->whereIn('id', $values)
                    ->with('country')
                    ->get()
                    ->mapWithKeys(fn(State $state) => [$state->id => $state->name . ' (' . $state->country?->iso2 . ')',])
                    ->all()
            )
            ->query(function (Builder $query, array $data): Builder {
                $stateIds = $data['values'] ?? [];
                if (count($stateIds) == 0) return $query;
                return $query
                    ->whereHasMorph('locationable', [State::class], fn(Builder $q) => $q->whereIn('id', $stateIds))
                    ->orWhereHasMorph('locationable', [City::class], fn(Builder $q) => $q->whereIn('state_id', $stateIds));
            });
    }

    public static function getCitySelectFilterTable()
    {
        return  SelectFilter::make('city')
            ->label('City')
            ->multiple()
            ->searchable()
            ->preload()
            ->getSearchResultsUsing(
                fn(string $search): array => City::query()
                    ->where('name', 'ilike', "%{$search}%")
                    ->with('country', 'state')
                    ->limit(50)
                    ->orderBy('name')
                    ->get()
                    ->mapWithKeys(fn(City $city) => [$city->id => $city->name . ' (' . $city->state?->name . '() (' . $city->country->iso2 . ')',])
                    ->all()
            )
            ->getOptionLabelsUsing(
                fn(array $values): array => City::query()
                    ->whereIn('id', $values)
                    ->with('country', 'state')
                    ->get()
                    ->mapWithKeys(fn(City $city) => [$city->id => $city->name . ' (' . $city->state->name . ') (' . $city->country->iso2 . ')',])
                    ->all()
            )
            ->query(function (Builder $query, array $data) {
                $cityIds = $data['values'] ?? [];
                if (count($cityIds) == 0) return $query;
                $query->whereHasMorph('locationable', [City::class], fn($q) => $q->whereIn('id', $cityIds));
                return $query;
            });
    }
}
