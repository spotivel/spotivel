<x-filament-panels::page>
    <div class="flex gap-4 h-[calc(100vh-12rem)]" x-data="{ leftSidebarMini: false, rightSidebarOpen: true }">
        
        {{-- Left Sidebar - Collapsible to mini --}}
        <div 
            :class="leftSidebarMini ? 'w-16' : 'w-64'" 
            class="transition-all duration-300 bg-nord-700 dark:bg-nord-700 rounded-lg shadow-sm border border-nord-600 dark:border-nord-600 overflow-hidden flex flex-col"
        >
            {{-- Sidebar Header --}}
            <div class="p-4 border-b border-nord-600 flex justify-between items-center">
                <h3 x-show="!leftSidebarMini" class="text-lg font-semibold text-nord-200">Navigation</h3>
                <button 
                    @click="leftSidebarMini = !leftSidebarMini"
                    class="p-2 hover:bg-nord-600 rounded transition-colors"
                    :title="leftSidebarMini ? 'Expand sidebar' : 'Collapse sidebar'"
                >
                    <svg x-show="!leftSidebarMini" class="w-5 h-5 text-nord-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                    <svg x-show="leftSidebarMini" class="w-5 h-5 text-nord-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            {{-- Sidebar Content --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-6">
                {{-- Menu Items --}}
                <div class="space-y-2">
                    <button 
                        wire:click="showPlaylists"
                        class="w-full flex items-center gap-3 p-3 hover:bg-nord-600 rounded transition-colors text-left"
                        :class="leftSidebarMini && 'justify-center'"
                    >
                        <svg class="w-5 h-5 text-nord-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                        <span x-show="!leftSidebarMini" class="text-nord-200">Playlists</span>
                    </button>

                    <button 
                        wire:click="showArtists"
                        class="w-full flex items-center gap-3 p-3 hover:bg-nord-600 rounded transition-colors text-left"
                        :class="leftSidebarMini && 'justify-center'"
                    >
                        <svg class="w-5 h-5 text-nord-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span x-show="!leftSidebarMini" class="text-nord-200">Artists</span>
                    </button>
                </div>

                {{-- Top Playlists --}}
                <div x-show="!leftSidebarMini" class="space-y-2">
                    <h4 class="text-sm font-semibold text-nord-300 px-3">Top Playlists</h4>
                    @foreach($this->getTopPlaylists() as $playlist)
                        <button 
                            wire:click="viewPlaylist({{ $playlist->id }})"
                            class="w-full text-left p-2 hover:bg-nord-600 rounded transition-colors text-sm text-nord-200 truncate"
                            title="{{ $playlist->name }}"
                        >
                            {{ $playlist->name }}
                        </button>
                    @endforeach
                </div>

                {{-- Top Artists --}}
                <div x-show="!leftSidebarMini" class="space-y-2">
                    <h4 class="text-sm font-semibold text-nord-300 px-3">Top Artists</h4>
                    @foreach($this->getTopArtists() as $artist)
                        <button 
                            wire:click="viewArtist({{ $artist->id }})"
                            class="w-full text-left p-2 hover:bg-nord-600 rounded transition-colors text-sm text-nord-200 truncate flex items-center gap-2"
                            title="{{ $artist->name }}"
                        >
                            <svg class="w-3 h-3 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                            {{ $artist->name }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Middle Section - Main Content --}}
        <div class="flex-1 bg-nord-700 dark:bg-nord-700 rounded-lg shadow-sm border border-nord-600 dark:border-nord-600 overflow-hidden flex flex-col">
            <div class="p-6 flex-1 overflow-auto">
                {{ $this->table }}
            </div>
        </div>

        {{-- Right Sidebar - Now Playing (Fully Collapsible) --}}
        <div 
            x-show="rightSidebarOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-x-full"
            x-transition:enter-end="opacity-100 transform translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-x-0"
            x-transition:leave-end="opacity-0 transform translate-x-full"
            class="w-80 bg-nord-700 dark:bg-nord-700 rounded-lg shadow-sm border border-nord-600 dark:border-nord-600 overflow-hidden flex flex-col"
        >
            {{-- Now Playing Header --}}
            <div class="p-4 border-b border-nord-600 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-nord-200">Now Playing</h3>
                <button 
                    @click="rightSidebarOpen = false"
                    class="p-2 hover:bg-nord-600 rounded transition-colors"
                    title="Close sidebar"
                >
                    <svg class="w-5 h-5 text-nord-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Now Playing Content --}}
            <div class="flex-1 overflow-y-auto p-6">
                <div class="text-center space-y-4">
                    {{-- Album Art Placeholder --}}
                    <div class="w-full aspect-square bg-nord-600 rounded-lg flex items-center justify-center">
                        <svg class="w-24 h-24 text-nord-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </div>

                    {{-- Track Info --}}
                    <div class="space-y-2">
                        <h4 class="text-lg font-semibold text-nord-200">No track playing</h4>
                        <p class="text-sm text-nord-300">Select a track to start playing</p>
                    </div>

                    {{-- Playback Controls Placeholder --}}
                    <div class="flex justify-center items-center gap-4 pt-4">
                        <button class="p-3 hover:bg-nord-600 rounded-full transition-colors" disabled>
                            <svg class="w-6 h-6 text-nord-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.445 14.832A1 1 0 0010 14v-2.798l5.445 3.63A1 1 0 0017 14V6a1 1 0 00-1.555-.832L10 8.798V6a1 1 0 00-1.555-.832l-6 4a1 1 0 000 1.664l6 4z" />
                            </svg>
                        </button>
                        <button class="p-4 bg-nord-600 hover:bg-nord-500 rounded-full transition-colors" disabled>
                            <svg class="w-8 h-8 text-nord-200" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <button class="p-3 hover:bg-nord-600 rounded-full transition-colors" disabled>
                            <svg class="w-6 h-6 text-nord-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4.555 5.168A1 1 0 003 6v8a1 1 0 001.555.832L10 11.202V14a1 1 0 001.555.832l6-4a1 1 0 000-1.664l-6-4A1 1 0 0010 6v2.798l-5.445-3.63z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Toggle Right Sidebar Button (when closed) --}}
        <button 
            x-show="!rightSidebarOpen"
            @click="rightSidebarOpen = true"
            class="w-12 h-12 bg-nord-600 hover:bg-nord-500 rounded-lg shadow-lg flex items-center justify-center transition-colors fixed right-4 bottom-4"
            title="Show Now Playing"
        >
            <svg class="w-6 h-6 text-nord-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
            </svg>
        </button>

    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
