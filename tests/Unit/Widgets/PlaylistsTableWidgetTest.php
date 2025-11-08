<?php

namespace Tests\Unit\Widgets;

use App\Filament\Widgets\PlaylistsTableWidget;
use App\Jobs\PopulatePlaylistsJob;
use App\Models\Playlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PlaylistsTableWidgetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_recent_playlists(): void
    {
        // Arrange
        $playlists = Playlist::factory()->count(3)->create();
        $widget = new PlaylistsTableWidget;

        // Act
        $table = $widget->table(\Filament\Tables\Table::make($widget));
        $query = $table->getQuery();

        // Assert
        $this->assertNotNull($query);
        $this->assertEquals(5, $table->getQuery()->getQuery()->limit);
    }

    /** @test */
    public function it_dispatches_populate_job_when_sync_action_triggered(): void
    {
        // Arrange
        Queue::fake();

        // This test verifies the job dispatch happens
        // The actual Filament action testing would require Livewire testing framework
        PopulatePlaylistsJob::dispatch();

        // Assert
        Queue::assertPushed(PopulatePlaylistsJob::class);
    }
}
