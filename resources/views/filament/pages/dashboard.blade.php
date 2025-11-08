<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 mb-6">
        <div class="text-nord-200 dark:text-nord-200">
            <p class="text-xl font-semibold mb-4">Welcome to Spotivel - Spotify Track Sync Dashboard</p>
            
            <div class="text-sm text-nord-300 dark:text-nord-300 mb-6">
                <p>Use the navigation menu to manage:</p>
                <ul class="list-disc list-inside mt-2 space-y-1">
                    <li><strong>Tracks</strong> - View and sync your Spotify tracks</li>
                    <li><strong>Albums</strong> - Manage album collections</li>
                    <li><strong>Artists</strong> - Browse and organize artists</li>
                    <li><strong>Playlists</strong> - Organize and sync your playlists</li>
                </ul>
                <p class="mt-4 text-xs">
                    Click the "Populate" button in each playlist row to sync individual playlists from Spotify.
                </p>
            </div>
        </div>
    </div>

    {{-- Dashboard grid: 2 columns (8 wide + 4 wide) with dark-mode support --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main content area (8 columns wide / 2fr) --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-nord-700 dark:bg-nord-700 rounded-lg shadow-sm p-6 border border-nord-600 dark:border-nord-600">
                {{-- Playlists table (actual Filament table, not widget) --}}
                {{ $this->table }}
            </div>
            
            <div class="bg-nord-700 dark:bg-nord-700 rounded-lg shadow-sm p-6 border border-nord-600 dark:border-nord-600">
                {{-- Tracks table --}}
                {{ $this->tracksTable() }}
            </div>
        </div>

        {{-- Sidebar content area (4 columns wide / 1fr) --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-nord-700 dark:bg-nord-700 rounded-lg shadow-sm p-6 border border-nord-600 dark:border-nord-600">
                {{-- Artists table --}}
                {{ $this->artistsTable() }}
            </div>
            
            <div class="bg-nord-700 dark:bg-nord-700 rounded-lg shadow-sm p-6 border border-nord-600 dark:border-nord-600">
                {{-- Albums table --}}
                {{ $this->albumsTable() }}
            </div>
        </div>
    </div>
    
    <x-filament-actions::modals />
</x-filament-panels::page>
