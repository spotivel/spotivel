<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AlbumsTableWidget;
use App\Filament\Widgets\ArtistsTableWidget;
use App\Filament\Widgets\PlaylistsTableWidget;
use App\Filament\Widgets\TracksTableWidget;
use Filament\Pages\Page;

class Dashboard extends Page
{
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
            PlaylistsTableWidget::class,
        ];
    }
    
    protected function playlistsTable(): string
    {
        return view('filament::widgets.widget', [
            'widget' => $this->getCachedWidget(PlaylistsTableWidget::class),
        ])->render();
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
