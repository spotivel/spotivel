<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AlbumsTableWidget;
use App\Filament\Widgets\ArtistsTableWidget;
use App\Filament\Widgets\TracksTableWidget;
use App\Jobs\SyncPlaylistJob;
use App\Models\Playlist;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class Dashboard extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    // Icon constants for type safety
    private const ICON_SYNC = 'heroicon-o-arrow-path';
    private const ICON_EDIT = 'heroicon-o-pencil-square';
    private const ICON_MUSIC = 'heroicon-o-musical-note';

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    protected static ?int $navigationSort = -2;

    public static function getNavigationLabel(): string
    {
        return 'Dashboard';
    }

    public function getTitle(): string
    {
        return 'Dashboard';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TracksTableWidget::class,
            ArtistsTableWidget::class,
            AlbumsTableWidget::class,
        ];
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->query(Playlist::query()->latest()->limit(10))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Playlist $record): string => $record->description ?? ''),
                Tables\Columns\IconColumn::make('public')
                    ->boolean()
                    ->label('Public'),
                Tables\Columns\IconColumn::make('collaborative')
                    ->boolean()
                    ->label('Collab'),
                Tables\Columns\TextColumn::make('total_tracks')
                    ->numeric()
                    ->sortable()
                    ->label('Tracks'),
                Tables\Columns\TextColumn::make('owner_name')
                    ->searchable()
                    ->label('Owner'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->label('Added'),
            ])
            ->actions([
                Tables\Actions\Action::make('populate')
                    ->label('Populate')
                    ->icon(self::ICON_SYNC)
                    ->color('primary')
                    ->extraAttributes([
                        'class' => 'bg-nord-600 text-nord-300 hover:bg-nord-500',
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Sync Playlist')
                    ->modalDescription(fn (Playlist $record): string => "This will sync the playlist '{$record->name}' with Spotify, including finding live versions and updating tracks. This may take a few minutes.")
                    ->modalSubmitActionLabel('Start Sync')
                    ->action(function (Playlist $record) {
                        SyncPlaylistJob::dispatch($record->id);
                        
                        Notification::make()
                            ->title('Playlist sync started')
                            ->body("'{$record->name}' is being synced in the background.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->icon(self::ICON_EDIT)
                        ->url(fn (Playlist $record): string => route('filament.admin.resources.playlists.edit', ['record' => $record])),
                    Tables\Actions\Action::make('open_spotify')
                        ->label('Open in Spotify')
                        ->icon(self::ICON_MUSIC)
                        ->url(fn (Playlist $record): string => $record->external_url)
                        ->openUrlInNewTab(),
                ]),
            ])
            ->heading('Recent Playlists')
            ->description('Your most recently synced playlists from Spotify')
            ->striped();
    }
    
    protected function tracksTable(): string
    {
        return view('filament::widgets.widget', [
            'widget' => $this->getCachedWidget(TracksTableWidget::class),
        ])->render();
    }
    
    protected function artistsTable(): string
    {
        return view('filament::widgets.widget', [
            'widget' => $this->getCachedWidget(ArtistsTableWidget::class),
        ])->render();
    }
    
    protected function albumsTable(): string
    {
        return view('filament::widgets.widget', [
            'widget' => $this->getCachedWidget(AlbumsTableWidget::class),
        ])->render();
    }
}
