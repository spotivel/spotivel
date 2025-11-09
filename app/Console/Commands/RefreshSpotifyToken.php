<?php

namespace App\Console\Commands;

use App\Contracts\OAuthServiceInterface;
use Illuminate\Console\Command;

class RefreshSpotifyToken extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'spotify:refresh-token';

    /**
     * The console command description.
     */
    protected $description = 'Refresh the Spotify OAuth access token before it expires';

    /**
     * Execute the console command.
     */
    public function handle(OAuthServiceInterface $oauthService): int
    {
        $timeUntilExpiry = $oauthService->getTimeUntilExpiry();

        if ($timeUntilExpiry === null) {
            $this->error('No token found in cache. Please authenticate first.');

            return self::FAILURE;
        }

        $this->info("Token expires in {$timeUntilExpiry} seconds.");

        if ($timeUntilExpiry > 300) {
            $this->info('Token is still valid. No refresh needed.');

            return self::SUCCESS;
        }

        $this->info('Refreshing token...');

        if ($oauthService->refreshCurrentToken()) {
            $newExpiry = $oauthService->getTimeUntilExpiry();
            $this->info("Token refreshed successfully! New token expires in {$newExpiry} seconds.");

            return self::SUCCESS;
        }

        $this->error('Failed to refresh token. Please re-authenticate.');

        return self::FAILURE;
    }
}
