<?php

namespace App\Filament\Pages;

use App\Models\Artist;
use App\Models\Playlist;
use App\Models\Track;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MusicPlayer extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-musical-note';

    protected static string $view = 'filament.pages.music-player';

    protected static ?int $navigationSort = -1;

    public static function getNavigationLabel(): string
    {
        return 'Music Player';
    }

    public function getTitle(): string
    {
        return 'Music Player';
    }

    public string $currentView = 'playlists';

    public ?int $selectedPlaylistId = null;

    public ?int $selectedArtistId = null;

    /**
     * Main table for playlists/tracks in middle section.
     */
    public function table(Table $table): Table
    {
        if ($this->currentView === 'playlists') {
            return $this->playlistsTable($table);
        }

        if ($this->currentView === 'playlist_tracks' && $this->selectedPlaylistId) {
            return $this->playlistTracksTable($table);
        }

        if ($this->currentView === 'artists') {
            return $this->artistsTable($table);
        }

        if ($this->currentView === 'artist_tracks' && $this->selectedArtistId) {
            return $this->artistTracksTable($table);
        }

        return $this->playlistsTable($table);
    }

    /**
     * Playlists table.
     */
    protected function playlistsTable(Table $table): Table
    {
        return $table
            ->query(Playlist::query()->latest())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('total_tracks')
                    ->label('Tracks')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('public')
                    ->boolean()
                    ->label('Public'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_tracks')
                    ->label('View Tracks')
                    ->icon('heroicon-o-queue-list')
                    ->action(function (Playlist $record) {
                        $this->selectedPlaylistId = $record->id;
                        $this->currentView = 'playlist_tracks';
                    }),
            ])
            ->heading('All Playlists')
            ->striped();
    }

    /**
     * Playlist tracks table.
     */
    protected function playlistTracksTable(Table $table): Table
    {
        $playlist = Playlist::find($this->selectedPlaylistId);

        return $table
            ->query(
                Track::query()
                    ->whereHas('playlists', fn (Builder $query) => $query->where('playlists.id', $this->selectedPlaylistId))
                    ->orderBy('pivot_position')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('artists.name')
                    ->label('Artists')
                    ->searchable()
                    ->limitList(2),
                Tables\Columns\TextColumn::make('duration_ms')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => gmdate('i:s', $state / 1000)),
            ])
            ->heading($playlist ? "Tracks in {$playlist->name}" : 'Playlist Tracks')
            ->headerActions([
                Tables\Actions\Action::make('back')
                    ->label('Back to Playlists')
                    ->icon('heroicon-o-arrow-left')
                    ->action(function () {
                        $this->currentView = 'playlists';
                        $this->selectedPlaylistId = null;
                    }),
            ])
            ->striped();
    }

    /**
     * Artists table.
     */
    protected function artistsTable(Table $table): Table
    {
        return $table
            ->query(Artist::query()->latest())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\IconColumn::make('is_interesting')
                    ->boolean()
                    ->label('★'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_tracks')
                    ->label('View Tracks')
                    ->icon('heroicon-o-queue-list')
                    ->action(function (Artist $record) {
                        $this->selectedArtistId = $record->id;
                        $this->currentView = 'artist_tracks';
                    }),
            ])
            ->heading('All Artists')
            ->striped();
    }

    /**
     * Artist tracks table.
     */
    protected function artistTracksTable(Table $table): Table
    {
        $artist = Artist::find($this->selectedArtistId);

        return $table
            ->query(
                Track::query()
                    ->whereHas('artists', fn (Builder $query) => $query->where('artists.id', $this->selectedArtistId))
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('album.name')
                    ->label('Album')
                    ->searchable(),
                Tables\Columns\TextColumn::make('duration_ms')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => gmdate('i:s', $state / 1000)),
            ])
            ->heading($artist ? "Tracks by {$artist->name}" : 'Artist Tracks')
            ->headerActions([
                Tables\Actions\Action::make('back')
                    ->label('Back to Artists')
                    ->icon('heroicon-o-arrow-left')
                    ->action(function () {
                        $this->currentView = 'artists';
                        $this->selectedArtistId = null;
                    }),
            ])
            ->striped();
    }

    /**
     * Get top 10 interesting playlists for sidebar.
     */
    public function getTopPlaylists()
    {
        return Playlist::query()
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Get top 10 interesting artists for sidebar.
     */
    public function getTopArtists()
    {
        return Artist::query()
            ->where('is_interesting', true)
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Switch to playlists view.
     */
    public function showPlaylists()
    {
        $this->currentView = 'playlists';
        $this->selectedPlaylistId = null;
        $this->selectedArtistId = null;
    }

    /**
     * Switch to artists view.
     */
    public function showArtists()
    {
        $this->currentView = 'artists';
        $this->selectedPlaylistId = null;
        $this->selectedArtistId = null;
    }

    /**
     * View a specific playlist.
     */
    public function viewPlaylist(int $playlistId)
    {
        $this->selectedPlaylistId = $playlistId;
        $this->currentView = 'playlist_tracks';
    }

    /**
     * View a specific artist.
     */
    public function viewArtist(int $artistId)
    {
        $this->selectedArtistId = $artistId;
        $this->currentView = 'artist_tracks';
    }
}
