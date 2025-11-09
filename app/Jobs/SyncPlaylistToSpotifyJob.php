<?php

namespace App\Jobs;

use App\Models\Playlist;
use App\Services\SpotifyPlaylistsClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncPlaylistToSpotifyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $playlistId,
        public array $trackUris
    ) {}

    /**
     * Execute the job.
     * Handles the special two-step Spotify API sync process.
     */
    public function handle(SpotifyPlaylistsClient $playlistsClient): void
    {
        $playlist = Playlist::findOrFail($this->playlistId);

        Log::info("Syncing playlist to Spotify: {$playlist->name} ({$playlist->spotify_id}) with ".count($this->trackUris).' tracks');

        try {
            $playlistsClient->replaceTracks($playlist->spotify_id, $this->trackUris);

            Log::info("Successfully synced playlist to Spotify: {$playlist->name}");
        } catch (\Exception $e) {
            Log::error("Failed to sync playlist to Spotify: {$e->getMessage()}", [
                'playlist_id' => $this->playlistId,
                'spotify_id' => $playlist->spotify_id,
            ]);
            throw $e;
        }
    }
}
