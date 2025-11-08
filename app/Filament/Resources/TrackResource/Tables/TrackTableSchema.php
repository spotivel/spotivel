<?php

namespace App\Filament\Resources\TrackResource\Tables;

use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;

class TrackTableSchema
{
    public static function columns(): array
    {
        return [
            TextColumn::make('spotify_id')
                ->searchable(),
            TextColumn::make('name')
                ->searchable()
                ->sortable(),
            TextColumn::make('duration_ms')
                ->numeric()
                ->sortable(),
            IconColumn::make('explicit')
                ->boolean(),
            IconColumn::make('is_interesting')
                ->boolean()
                ->label('Interesting'),
            TextColumn::make('popularity')
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
            TernaryFilter::make('explicit'),
            TernaryFilter::make('is_interesting'),
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
                    \App\Jobs\PopulateTracksJob::dispatch();

                    \Filament\Notifications\Notification::make()
                        ->title('Tracks population queued')
                        ->success()
                        ->send();
                }),
        ];
    }
}
