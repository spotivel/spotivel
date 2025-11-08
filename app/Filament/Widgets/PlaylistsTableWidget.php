<?php

namespace App\Filament\Widgets;

use App\Jobs\PopulatePlaylistsJob;
use App\Models\Playlist;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\Action;
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
            ->heading('Recent Playlists')
            ->headerActions([
                Action::make('sync_playlists')
                    ->label('Sync All Playlists')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Sync All Playlists')
                    ->modalDescription('This will fetch all your playlists from Spotify, sync their tracks (including finding live versions), and update both the database and Spotify. This process may take several minutes.')
                    ->modalSubmitActionLabel('Start Sync')
                    ->action(function () {
                        // Dispatch the populate job which will chain to sync jobs
                        PopulatePlaylistsJob::dispatch();

                        Notification::make()
                            ->title('Playlist sync started')
                            ->body('Your playlists are being synced in the background. This may take several minutes.')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
