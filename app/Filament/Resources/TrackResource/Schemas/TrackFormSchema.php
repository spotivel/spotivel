<?php

namespace App\Filament\Resources\TrackResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class TrackFormSchema
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
            TextInput::make('duration_ms')
                ->required()
                ->numeric(),
            Toggle::make('explicit')
                ->required(),
            Toggle::make('is_interesting')
                ->label('Mark as Interesting'),
            TextInput::make('popularity')
                ->numeric(),
            TextInput::make('preview_url')
                ->url()
                ->maxLength(255),
        ];
    }
}
