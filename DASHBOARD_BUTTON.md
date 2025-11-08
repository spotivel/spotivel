# Dashboard Widget - Sync All Playlists Button

## Visual Representation

```
┌─────────────────────────────────────────────────────────────────────┐
│ Dashboard                                                            │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│ ┌──────────────────────────────────────────────────────────────────┐│
│ │ Recent Playlists                    [🔄 Sync All Playlists]      ││
│ ├──────────────────────────────────────────────────────────────────┤│
│ │ Name              Public  Collab  Tracks  Owner    Created       ││
│ ├──────────────────────────────────────────────────────────────────┤│
│ │ My Favorites      ✓       ✗       45      Me       2 hours ago   ││
│ │ Workout Mix       ✓       ✗       32      Me       5 hours ago   ││
│ │ Chill Vibes       ✗       ✓       28      Me       1 day ago     ││
│ │ Road Trip         ✓       ✗       67      Me       2 days ago    ││
│ │ 90s Throwbacks    ✓       ✗       53      Me       3 days ago    ││
│ └──────────────────────────────────────────────────────────────────┘│
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

## Button Click Flow

When user clicks "Sync All Playlists":

```
┌─────────────────────────────────────────────────────┐
│ Sync All Playlists                                  │
├─────────────────────────────────────────────────────┤
│                                                     │
│ This will fetch all your playlists from Spotify,   │
│ sync their tracks (including finding live          │
│ versions), and update both the database and        │
│ Spotify. This process may take several minutes.    │
│                                                     │
│                                                     │
│              [Cancel]  [Start Sync]                 │
└─────────────────────────────────────────────────────┘
```

After clicking "Start Sync":

```
┌─────────────────────────────────────────────────────┐
│ ✓ Success                                           │
├─────────────────────────────────────────────────────┤
│ Playlist sync started                               │
│                                                     │
│ Your playlists are being synced in the background. │
│ This may take several minutes.                     │
│                                                     │
│                                   [Dismiss]          │
└─────────────────────────────────────────────────────┘
```

## Button Implementation

**File**: `app/Filament/Widgets/PlaylistsTableWidget.php`

```php
->headerActions([
    Action::make('sync_playlists')
        ->label('Sync All Playlists')
        ->icon('heroicon-o-arrow-path')
        ->color('primary')
        ->requiresConfirmation()
        ->modalHeading('Sync All Playlists')
        ->modalDescription('This will fetch all your playlists...')
        ->modalSubmitActionLabel('Start Sync')
        ->action(function () {
            PopulatePlaylistsJob::dispatch();
            
            Notification::make()
                ->title('Playlist sync started')
                ->body('Your playlists are being synced...')
                ->success()
                ->send();
        }),
])
```

## Processing Flow (Background Queue)

```
[Sync Button Clicked]
         ↓
PopulatePlaylistsJob dispatched to queue
         ↓
[Fetches all user playlists from Spotify]
         ↓
For each playlist:
    ├─→ Creates/updates playlist in DB
    └─→ Dispatches SyncPlaylistJob
              ↓
    [Fetch playlist tracks from Spotify]
              ↓
    [Run through pipeline:]
        1. Remove duplicates
        2. Normalize data
        3. Validate tracks
        4. Find 2 live versions per track ⭐ NEW
              ↓
    [Save enriched tracks to database]
              ↓
    [Push complete playlist to Spotify API] ⭐ NEW
              ↓
    [Spotify playlist updated with live versions!]
```

## Example Result

**Before Sync:**
- Playlist has 10 tracks

**After Sync:**
- Playlist has ~30 tracks (10 original + ~20 live versions)
- Each original track now has up to 2 live performance versions
- All saved to database AND updated on Spotify

## Key Features

✅ **Non-blocking**: Returns immediately, processing in background
✅ **Queue-based**: Handles thousands of playlists efficiently  
✅ **Fault-tolerant**: Continues if individual track search fails
✅ **Bi-directional**: Updates both local DB and Spotify
✅ **Automatic**: Finds live versions without manual intervention
✅ **Scalable**: Chunks large playlists (100 tracks per API call)
