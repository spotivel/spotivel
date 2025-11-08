<?php

namespace App\Filament\Widgets;

use App\Models\Track;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TracksTableWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Track::query()->latest()->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\IconColumn::make('explicit')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_interesting')
                    ->boolean()
                    ->label('Interesting'),
                Tables\Columns\TextColumn::make('popularity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_ms')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => gmdate('i:s', $state / 1000)),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->heading('Recent Tracks');
    }
}
