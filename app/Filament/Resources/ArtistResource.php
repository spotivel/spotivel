<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArtistResource\Pages;
use App\Filament\Resources\ArtistResource\Schemas\ArtistFormSchema;
use App\Filament\Resources\ArtistResource\Tables\ArtistTableSchema;
use App\Models\Artist;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ArtistResource extends Resource
{
    protected static ?string $model = Artist::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema(ArtistFormSchema::make());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(ArtistTableSchema::columns())
            ->filters(ArtistTableSchema::filters())
            ->actions(ArtistTableSchema::actions())
            ->bulkActions(ArtistTableSchema::bulkActions())
            ->headerActions(ArtistTableSchema::headerActions());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArtists::route('/'),
            'create' => Pages\CreateArtist::route('/create'),
            'edit' => Pages\EditArtist::route('/{record}/edit'),
        ];
    }
}
