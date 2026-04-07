<?php

namespace App\Filament\Resources\Guests\RelationManagers;

use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Actions\CreateAction;
use Illuminate\Support\Facades\Auth;

class NotesRelationManager extends RelationManager
{
    protected static string $relationship = 'notes';

    protected static ?string $title = 'Staff Notes';

    public function form(Schema $schema): Schema
    {
         return $schema
            ->components([

                Textarea::make('note')
                    ->required()
                    ->rows(4),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('note')
                    ->limit(50),

                TextColumn::make('author.name')
                    ->label('Created By'),

                TextColumn::make('created_at')
                    ->dateTime(),

            ])
            ->headerActions([

                CreateAction::make()
                    ->label('Add Staff Note')
                    ->mutateFormDataUsing(function ($data) {

                        $data['created_by'] = Auth::id();

                        return $data;
                    }),

            ]);
    }
}