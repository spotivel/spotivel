<?php

namespace Tests\Unit\Services;

use App\Enums\HttpMethod;
use App\Services\ExternalClient;
use App\Services\SpotifyClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SpotifyClientRequestTest extends TestCase
{
    protected SpotifyClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $httpClient = new ExternalClient(
            baseUrl: 'https://api.spotify.com/v1',
            headers: ['Authorization' => 'Bearer test-token']
        );

        $this->client = new SpotifyClient($httpClient);
    }

    /** @test */
    public function it_returns_pending_request_when_called_without_parameters(): void
    {
        $request = $this->client->request();

        $this->assertInstanceOf(\Illuminate\Http\Client\PendingRequest::class, $request);
    }

    /** @test */
    public function it_executes_get_request_with_method_parameter(): void
    {
        Http::fake([
            'https://api.spotify.com/v1/tracks/123' => Http::response([
                'id' => '123',
                'name' => 'Test Track',
            ], 200),
        ]);

        $response = $this->client->request(HttpMethod::GET, '/tracks/123');

        $this->assertNotNull($response);
        Http::assertSent(function ($request) {
            return $request->method() === 'GET' &&
                   str_contains($request->url(), '/tracks/123');
        });
    }

    /** @test */
    public function it_executes_post_request_with_json_body(): void
    {
        Http::fake([
            'https://api.spotify.com/v1/playlists/123/tracks' => Http::response([
                'snapshot_id' => 'snapshot123',
            ], 200),
        ]);

        $response = $this->client->request(HttpMethod::POST, '/playlists/123/tracks', [
            'json' => ['uris' => ['spotify:track:1', 'spotify:track:2']],
        ]);

        $this->assertNotNull($response);
        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->method() === 'POST' &&
                   str_contains($request->url(), '/playlists/123/tracks') &&
                   isset($data['uris']) &&
                   count($data['uris']) === 2;
        });
    }

    /** @test */
    public function it_executes_put_request_with_json_body(): void
    {
        Http::fake([
            'https://api.spotify.com/v1/playlists/123/tracks' => Http::response([
                'snapshot_id' => 'snapshot123',
            ], 200),
        ]);

        $response = $this->client->request(HttpMethod::PUT, '/playlists/123/tracks', [
            'json' => ['uris' => ['spotify:track:1']],
        ]);

        $this->assertNotNull($response);
        Http::assertSent(function ($request) {
            return $request->method() === 'PUT' &&
                   str_contains($request->url(), '/playlists/123/tracks');
        });
    }

    /** @test */
    public function it_executes_delete_request(): void
    {
        Http::fake([
            'https://api.spotify.com/v1/me/tracks' => Http::response([], 200),
        ]);

        $response = $this->client->request(HttpMethod::DELETE, '/me/tracks', [
            'json' => ['ids' => ['track1', 'track2']],
        ]);

        $this->assertNotNull($response);
        Http::assertSent(function ($request) {
            return $request->method() === 'DELETE' &&
                   str_contains($request->url(), '/me/tracks');
        });
    }

    /** @test */
    public function it_executes_patch_request(): void
    {
        Http::fake([
            'https://api.spotify.com/v1/playlists/123' => Http::response([], 200),
        ]);

        $response = $this->client->request(HttpMethod::PATCH, '/playlists/123', [
            'json' => ['name' => 'Updated Name'],
        ]);

        $this->assertNotNull($response);
        Http::assertSent(function ($request) {
            return $request->method() === 'PATCH' &&
                   str_contains($request->url(), '/playlists/123');
        });
    }

    /** @test */
    public function it_handles_get_request_with_query_parameters(): void
    {
        Http::fake([
            'https://api.spotify.com/v1/search*' => Http::response([
                'tracks' => ['items' => []],
            ], 200),
        ]);

        $response = $this->client->request(HttpMethod::GET, '/search', [
            'query' => ['q' => 'test', 'type' => 'track'],
        ]);

        $this->assertNotNull($response);
        Http::assertSent(function ($request) {
            return $request->method() === 'GET' &&
                   str_contains($request->url(), '/search') &&
                   str_contains($request->url(), 'q=test') &&
                   str_contains($request->url(), 'type=track');
        });
    }

    /** @test */
    public function it_supports_request_with_additional_headers(): void
    {
        Http::fake([
            'https://api.spotify.com/v1/me' => Http::response([
                'id' => 'user123',
            ], 200),
        ]);

        $request = $this->client->requestWithHeaders([
            'X-Custom-Header' => 'custom-value',
        ]);

        $response = $request->get('/me');

        $this->assertNotNull($response);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/me');
        });
    }
}
