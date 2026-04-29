<?php

namespace App\Filament\Resources\HousekeepingStaff\Pages;

use App\Filament\Resources\HousekeepingStaff\HousekeepingStaffResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ListHousekeepingStaff extends ListRecords
{
    protected static string $resource = HousekeepingStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('addExistingUser')
                ->label('Add Existing User')
                ->icon('heroicon-o-user-plus')
                ->form([
                    Select::make('user_id')
                        ->label('User')
                        ->options(fn (): array => User::query()
                            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'housekeeping'))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray())
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $role = Role::firstOrCreate([
                        'name' => 'housekeeping',
                        'guard_name' => 'web',
                    ]);

                    User::findOrFail($data['user_id'])->syncRoles([$role]);

                    app(PermissionRegistrar::class)->forgetCachedPermissions();
                }),
        ];
    }
}
