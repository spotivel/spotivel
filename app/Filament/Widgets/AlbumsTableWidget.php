<?php

namespace App\Filament\Widgets;

use App\Models\Album;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AlbumsTableWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Album::query()->latest()->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('album_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'album' => 'success',
                        'single' => 'info',
                        'compilation' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('release_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_tracks')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->heading('Recent Albums');
    }
}
