<?php

namespace App\Filament\Resources\AlbumResource\Tables;

use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;

class AlbumTableSchema
{
    public static function columns(): array
    {
        return [
            TextColumn::make('spotify_id')
                ->searchable(),
            TextColumn::make('name')
                ->searchable()
                ->sortable(),
            TextColumn::make('album_type')
                ->searchable(),
            TextColumn::make('release_date')
                ->sortable(),
            TextColumn::make('total_tracks')
                ->numeric()
                ->sortable(),
            TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function filters(): array
    {
        return [
            //
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
                    \App\Jobs\PopulateAlbumsJob::dispatch();

                    \Filament\Notifications\Notification::make()
                        ->title('Albums population queued')
                        ->success()
                        ->send();
                }),
        ];
    }
}
