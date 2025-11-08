<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6">
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

        <x-filament-widgets::widgets
            :widgets="$this->getHeaderWidgets()"
            :columns="$this->getHeaderWidgetsColumns()"
        />
    </div>
</x-filament-panels::page>
