<?php

namespace App\Filament\Widgets;

use App\Models\Playlist;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PlaylistsTableWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Playlist::query()->latest()->limit(5))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\IconColumn::make('public')
                    ->boolean(),
                Tables\Columns\IconColumn::make('collaborative')
                    ->boolean(),
                Tables\Columns\TextColumn::make('total_tracks')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('owner_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->heading('Recent Playlists');
    }
}
