<?php

namespace App\Filament\Resources\PlaylistResource\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class PlaylistFormSchema
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
            Textarea::make('description')
                ->maxLength(65535)
                ->columnSpanFull(),
            Toggle::make('public')
                ->required(),
            Toggle::make('collaborative')
                ->required(),
            TextInput::make('total_tracks')
                ->required()
                ->numeric()
                ->default(0),
        ];
    }
}
