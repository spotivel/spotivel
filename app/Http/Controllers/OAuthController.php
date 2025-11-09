<?php

namespace App\Http\Controllers;

use App\Contracts\OAuthServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OAuthController
{
    /**
     * Create a new OAuth controller instance.
     */
    public function __construct(
        protected OAuthServiceInterface $oauthService
    ) {}

    /**
     * Redirect user to Spotify authorization page.
     */
    public function redirect(): RedirectResponse
    {
        $authUrl = $this->oauthService->getAuthorizationUrl();

        return redirect()->away($authUrl);
    }

    /**
     * Handle OAuth callback from Spotify.
     */
    public function callback(Request $request): RedirectResponse
    {
        // Early return if error in callback
        if ($request->has('error')) {
            return redirect('/admin')->with('error', 'Authorization failed: '.$request->get('error'));
        }

        // Early return if no code provided
        if (! $request->has('code')) {
            return redirect('/admin')->with('error', 'Authorization failed: No code received');
        }

        try {
            $tokenData = $this->oauthService->getAccessToken($request->get('code'));

            // Cache the tokens
            $this->oauthService->cacheToken(
                $tokenData['access_token'],
                $tokenData['refresh_token'],
                $tokenData['expires_in']
            );

            return redirect('/admin')->with('success', 'Successfully connected to Spotify!');
        } catch (\Exception $e) {
            return redirect('/admin')->with('error', 'Failed to obtain access token: '.$e->getMessage());
        }
    }

    /**
     * Disconnect from Spotify by clearing cached tokens.
     */
    public function disconnect(): RedirectResponse
    {
        $this->oauthService->clearCache();

        return redirect('/admin')->with('success', 'Disconnected from Spotify');
    }
}
