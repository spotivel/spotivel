<?php

namespace App\Filament\Resources\ArtistResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class ArtistFormSchema
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
            Toggle::make('is_interesting')
                ->label('Mark as Interesting'),
            TextInput::make('popularity')
                ->numeric(),
            TextInput::make('followers')
                ->numeric(),
        ];
    }
}
