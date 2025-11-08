<?php

namespace App\Filament\Resources\AlbumResource\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class AlbumFormSchema
{
    public static function make(): array
    {
        return [
            TextInput::make('spotify_id')
                ->required()
                ->maxLength(255),
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            Select::make('album_type')
                ->options([
                    'album' => 'Album',
                    'single' => 'Single',
                    'compilation' => 'Compilation',
                ]),
            DatePicker::make('release_date')
                ->displayFormat('Y-m-d')
                ->native(false),
            TextInput::make('total_tracks')
                ->numeric(),
        ];
    }
}
