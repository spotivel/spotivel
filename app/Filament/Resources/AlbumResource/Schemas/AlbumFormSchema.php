<?php

namespace App\Filament\Resources\AlbumResource\Schemas;

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
            TextInput::make('album_type')
                ->maxLength(255),
            TextInput::make('release_date')
                ->maxLength(255),
            TextInput::make('total_tracks')
                ->numeric(),
        ];
    }
}
