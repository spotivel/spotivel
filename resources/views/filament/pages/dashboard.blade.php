<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 mb-6">
        <div class="text-gray-600 dark:text-gray-400">
            <p class="text-xl font-semibold mb-4">Welcome to Spotivel - Spotify Track Sync Dashboard</p>
            
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                <p>Use the navigation menu to manage:</p>
                <ul class="list-disc list-inside mt-2 space-y-1">
                    <li><strong>Tracks</strong> - View and sync your Spotify tracks</li>
                    <li><strong>Albums</strong> - Manage album collections</li>
                    <li><strong>Artists</strong> - Browse and organize artists</li>
                    <li><strong>Playlists</strong> - Organize and sync your playlists</li>
                </ul>
                <p class="mt-4 text-xs">
                    Click the "Populate" button in each section to sync data from Spotify.
                </p>
            </div>
        </div>
    </div>

    {{-- Dashboard grid: 2 columns (8 wide + 4 wide) with dark-mode support --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main content area (8 columns wide / 2fr) --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                {{-- Playlists table with Populate button --}}
                <x-filament-widgets::widgets
                    :widgets="[
                        \App\Filament\Widgets\PlaylistsTableWidget::class,
                    ]"
                />
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                {{-- Tracks table --}}
                <x-filament-widgets::widgets
                    :widgets="[
                        \App\Filament\Widgets\TracksTableWidget::class,
                    ]"
                />
            </div>
        </div>

        {{-- Sidebar content area (4 columns wide / 1fr) --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                {{-- Artists table --}}
                <x-filament-widgets::widgets
                    :widgets="[
                        \App\Filament\Widgets\ArtistsTableWidget::class,
                    ]"
                />
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                {{-- Albums table --}}
                <x-filament-widgets::widgets
                    :widgets="[
                        \App\Filament\Widgets\AlbumsTableWidget::class,
                    ]"
                />
            </div>
        </div>
    </div>
</x-filament-panels::page>
