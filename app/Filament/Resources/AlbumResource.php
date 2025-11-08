<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlbumResource\Pages;
use App\Filament\Resources\AlbumResource\Schemas\AlbumFormSchema;
use App\Filament\Resources\AlbumResource\Tables\AlbumTableSchema;
use App\Models\Album;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class AlbumResource extends Resource
{
    protected static ?string $model = Album::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema(AlbumFormSchema::make());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(AlbumTableSchema::columns())
            ->filters(AlbumTableSchema::filters())
            ->actions(AlbumTableSchema::actions())
            ->bulkActions(AlbumTableSchema::bulkActions())
            ->headerActions(AlbumTableSchema::headerActions());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlbums::route('/'),
            'create' => Pages\CreateAlbum::route('/create'),
            'edit' => Pages\EditAlbum::route('/{record}/edit'),
        ];
    }
}
