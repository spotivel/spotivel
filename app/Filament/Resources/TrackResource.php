<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrackResource\Pages;
use App\Filament\Resources\TrackResource\Schemas\TrackFormSchema;
use App\Filament\Resources\TrackResource\Tables\TrackTableSchema;
use App\Models\Track;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class TrackResource extends Resource
{
    protected static ?string $model = Track::class;

    protected static ?string $navigationIcon = 'heroicon-o-musical-note';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema(TrackFormSchema::make());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(TrackTableSchema::columns())
            ->filters(TrackTableSchema::filters())
            ->actions(TrackTableSchema::actions())
            ->bulkActions(TrackTableSchema::bulkActions())
            ->headerActions(TrackTableSchema::headerActions());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTracks::route('/'),
            'create' => Pages\CreateTrack::route('/create'),
            'edit' => Pages\EditTrack::route('/{record}/edit'),
        ];
    }
}
