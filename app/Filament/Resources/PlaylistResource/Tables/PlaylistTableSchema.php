<?php

namespace App\Filament\Resources\PlaylistResource\Tables;

use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;

class PlaylistTableSchema
{
    public static function columns(): array
    {
        return [
            TextColumn::make('spotify_id')
                ->searchable(),
            TextColumn::make('name')
                ->searchable()
                ->sortable(),
            IconColumn::make('public')
                ->boolean(),
            IconColumn::make('collaborative')
                ->boolean(),
            TextColumn::make('total_tracks')
                ->numeric()
                ->sortable(),
            TextColumn::make('owner_name')
                ->searchable(),
            TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function filters(): array
    {
        return [
            TernaryFilter::make('public'),
            TernaryFilter::make('collaborative'),
        ];
    }

    public static function actions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    public static function bulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ];
    }

    public static function headerActions(): array
    {
        return [
            Action::make('populate')
                ->label('Populate')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    // Queue population job
                    \App\Jobs\PopulatePlaylistsJob::dispatch();

                    \Filament\Notifications\Notification::make()
                        ->title('Playlists population queued')
                        ->success()
                        ->send();
                }),
        ];
    }
}
