<?php

namespace Tests\Unit\Widgets;

use App\Filament\Widgets\PlaylistsTableWidget;
use Tests\TestCase;

class PlaylistsTableWidgetTest extends TestCase
{
    /** @test */
    public function it_has_table_method(): void
    {
        // Arrange
        $widget = new PlaylistsTableWidget;

        // Assert
        $this->assertTrue(method_exists($widget, 'table'));
    }

    /** @test */
    public function it_can_be_instantiated(): void
    {
        // Arrange & Act
        $widget = new PlaylistsTableWidget;

        // Assert
        $this->assertInstanceOf(PlaylistsTableWidget::class, $widget);
    }
}
