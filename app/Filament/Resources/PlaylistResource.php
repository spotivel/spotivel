<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlaylistResource\Pages;
use App\Filament\Resources\PlaylistResource\Schemas\PlaylistFormSchema;
use App\Filament\Resources\PlaylistResource\Tables\PlaylistTableSchema;
use App\Models\Playlist;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class PlaylistResource extends Resource
{
    protected static ?string $model = Playlist::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema(PlaylistFormSchema::make());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(PlaylistTableSchema::columns())
            ->filters(PlaylistTableSchema::filters())
            ->actions(PlaylistTableSchema::actions())
            ->bulkActions(PlaylistTableSchema::bulkActions())
            ->headerActions(PlaylistTableSchema::headerActions());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlaylists::route('/'),
            'create' => Pages\CreatePlaylist::route('/create'),
            'edit' => Pages\EditPlaylist::route('/{record}/edit'),
        ];
    }
}
