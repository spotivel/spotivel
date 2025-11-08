<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\TrackResource;
use App\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TrackResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_tracks(): void
    {
        $tracks = Track::factory()->count(10)->create();

        Livewire::test(TrackResource\Pages\ListTracks::class)
            ->assertCanSeeTableRecords($tracks);
    }

    public function test_can_create_track(): void
    {
        $newData = Track::factory()->make();

        Livewire::test(TrackResource\Pages\CreateTrack::class)
            ->fillForm([
                'spotify_id' => $newData->spotify_id,
                'name' => $newData->name,
                'duration_ms' => $newData->duration_ms,
                'explicit' => $newData->explicit,
                'is_interesting' => $newData->is_interesting ?? false,
                'popularity' => $newData->popularity,
                'preview_url' => $newData->preview_url,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Track::class, [
            'spotify_id' => $newData->spotify_id,
            'name' => $newData->name,
        ]);
    }

    public function test_can_validate_track_required_fields(): void
    {
        Livewire::test(TrackResource\Pages\CreateTrack::class)
            ->fillForm([
                'spotify_id' => null,
                'name' => null,
                'duration_ms' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['spotify_id', 'name', 'duration_ms']);
    }

    public function test_can_validate_track_max_length(): void
    {
        Livewire::test(TrackResource\Pages\CreateTrack::class)
            ->fillForm([
                'spotify_id' => str_repeat('a', 256),
                'name' => str_repeat('b', 256),
                'duration_ms' => 180000,
                'explicit' => false,
            ])
            ->call('create')
            ->assertHasFormErrors(['spotify_id', 'name']);
    }

    public function test_can_retrieve_track_data_in_edit_form(): void
    {
        $track = Track::factory()->create();

        Livewire::test(TrackResource\Pages\EditTrack::class, ['record' => $track->getRouteKey()])
            ->assertFormSet([
                'spotify_id' => $track->spotify_id,
                'name' => $track->name,
                'duration_ms' => $track->duration_ms,
                'explicit' => $track->explicit,
            ]);
    }

    public function test_can_update_track(): void
    {
        $track = Track::factory()->create();
        $newData = Track::factory()->make();

        Livewire::test(TrackResource\Pages\EditTrack::class, ['record' => $track->getRouteKey()])
            ->fillForm([
                'name' => $newData->name,
                'duration_ms' => $newData->duration_ms,
                'popularity' => $newData->popularity,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Track::class, [
            'id' => $track->id,
            'name' => $newData->name,
            'duration_ms' => $newData->duration_ms,
        ]);
    }

    public function test_can_delete_track(): void
    {
        $track = Track::factory()->create();

        Livewire::test(TrackResource\Pages\EditTrack::class, ['record' => $track->getRouteKey()])
            ->callAction('delete');

        $this->assertModelMissing($track);
    }

    public function test_can_filter_tracks_by_explicit(): void
    {
        $explicitTracks = Track::factory()->count(3)->create(['explicit' => true]);
        $cleanTracks = Track::factory()->count(3)->create(['explicit' => false]);

        Livewire::test(TrackResource\Pages\ListTracks::class)
            ->filterTable('explicit', true)
            ->assertCanSeeTableRecords($explicitTracks)
            ->assertCanNotSeeTableRecords($cleanTracks);
    }

    public function test_can_filter_tracks_by_interesting(): void
    {
        $interestingTracks = Track::factory()->count(3)->create(['is_interesting' => true]);
        $regularTracks = Track::factory()->count(3)->create(['is_interesting' => false]);

        Livewire::test(TrackResource\Pages\ListTracks::class)
            ->filterTable('is_interesting', true)
            ->assertCanSeeTableRecords($interestingTracks)
            ->assertCanNotSeeTableRecords($regularTracks);
    }

    public function test_can_search_tracks_by_name(): void
    {
        $tracks = Track::factory()->count(3)->create();
        $searchTrack = $tracks->first();

        Livewire::test(TrackResource\Pages\ListTracks::class)
            ->searchTable($searchTrack->name)
            ->assertCanSeeTableRecords([$searchTrack])
            ->assertCanNotSeeTableRecords($tracks->skip(1));
    }

    public function test_can_sort_tracks_by_name(): void
    {
        $tracks = Track::factory()->count(5)->create();

        Livewire::test(TrackResource\Pages\ListTracks::class)
            ->sortTable('name')
            ->assertCanSeeTableRecords($tracks->sortBy('name'), inOrder: true);
    }

    public function test_can_sort_tracks_by_popularity(): void
    {
        $tracks = Track::factory()->count(5)->create();

        Livewire::test(TrackResource\Pages\ListTracks::class)
            ->sortTable('popularity')
            ->assertCanSeeTableRecords($tracks->sortBy('popularity'), inOrder: true);
    }

    public function test_preview_url_must_be_valid_url(): void
    {
        Livewire::test(TrackResource\Pages\CreateTrack::class)
            ->fillForm([
                'spotify_id' => 'test_id',
                'name' => 'Test Track',
                'duration_ms' => 180000,
                'explicit' => false,
                'preview_url' => 'not-a-valid-url',
            ])
            ->call('create')
            ->assertHasFormErrors(['preview_url']);
    }

    public function test_handles_optional_fields_correctly(): void
    {
        Livewire::test(TrackResource\Pages\CreateTrack::class)
            ->fillForm([
                'spotify_id' => 'test_id_123',
                'name' => 'Test Track',
                'duration_ms' => 180000,
                'explicit' => false,
                // Optional fields not provided
                'is_interesting' => false,
                'popularity' => null,
                'preview_url' => null,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Track::class, [
            'spotify_id' => 'test_id_123',
            'name' => 'Test Track',
        ]);
    }

    public function test_can_bulk_delete_tracks(): void
    {
        $tracks = Track::factory()->count(3)->create();

        Livewire::test(TrackResource\Pages\ListTracks::class)
            ->callTableBulkAction('delete', $tracks);

        foreach ($tracks as $track) {
            $this->assertModelMissing($track);
        }
    }

    public function test_tracks_appear_in_correct_order(): void
    {
        // Create tracks with specific names to test ordering
        Track::factory()->create(['name' => 'Zulu Track']);
        Track::factory()->create(['name' => 'Alpha Track']);
        Track::factory()->create(['name' => 'Beta Track']);

        Livewire::test(TrackResource\Pages\ListTracks::class)
            ->sortTable('name', 'asc')
            ->assertSeeInOrder(['Alpha Track', 'Beta Track', 'Zulu Track']);
    }
}
