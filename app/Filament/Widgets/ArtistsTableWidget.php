<?php

namespace App\Filament\Widgets;

use App\Models\Artist;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ArtistsTableWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Artist::query()->latest()->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\IconColumn::make('is_interesting')
                    ->boolean()
                    ->label('Interesting'),
                Tables\Columns\TextColumn::make('popularity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('followers')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state)),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->heading('Recent Artists');
    }
}
