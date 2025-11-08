<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlaylistResource\Pages;
use App\Models\Playlist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlaylistResource extends Resource
{
    protected static ?string $model = Playlist::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('spotify_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('public')
                    ->required(),
                Forms\Components\Toggle::make('collaborative')
                    ->required(),
                Forms\Components\TextInput::make('total_tracks')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('spotify_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('public')
                    ->boolean(),
                Tables\Columns\IconColumn::make('collaborative')
                    ->boolean(),
                Tables\Columns\TextColumn::make('total_tracks')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('owner_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('public'),
                Tables\Filters\TernaryFilter::make('collaborative'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('populate')
                    ->label('Populate')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        // Queue population job
                        \App\Jobs\PopulatePlaylistsJob::dispatch();
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Playlists population queued')
                            ->success()
                            ->send();
                    }),
            ]);
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
