<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Notifications\Notification;

class Unit extends Model
{
    protected static function booted()
    {
        static::saving(function ($unit) {
            $titleSlug = \Illuminate\Support\Str::slug($unit->title);
            if (($unit->isDirty('content_blocks') || $unit->isDirty('title')) && is_array($unit->content_blocks)) {
                $unit->content_blocks = \App\Services\AutopilotImageService::optimizeContentBlocks($unit->content_blocks, $titleSlug);
            }
            if (($unit->isDirty('thumbnail') || $unit->isDirty('title')) && $unit->thumbnail) {
                $cleanThumb = ltrim($unit->thumbnail, '/');
                if (str_starts_with($cleanThumb, 'storage/')) {
                    $cleanThumb = substr($cleanThumb, 8);
                }
                // Do not optimize if it's a shared Media Library item
                if (!str_starts_with($cleanThumb, 'media-library/')) {
                    $newThumbnail = \App\Services\AutopilotImageService::convertAndReplaceOriginal($unit->thumbnail, $titleSlug);
                    if ($newThumbnail) {
                        $unit->thumbnail = $newThumbnail;
                    }
                }
            }
        });

        static::updated(function ($unit) {
            if ($unit->wasChanged('is_published') && $unit->is_published) {
                // Find all users enrolled in any course
                $users = \App\Models\User::whereHas('courses')->get();

                if ($users->isNotEmpty()) {
                    Notification::make()
                        ->title('New Lesson Alert! 🎬')
                        ->body("Lesson {$unit->sort_order}: {$unit->title} is now available in your dashboard.")
                        ->success()
                        ->sendToDatabase($users);
                }
            }
        });
    }

    // This allows us to mass-assign these fields
    protected $fillable = [
        'course_session_id', 
        'title', 
        'is_free_sample', 
        'sort_order', 
        'video_id', 
        'transcript', 
        'is_published',
        'is_registered_only',
        'type',
        'content_data',
        'content_blocks',
        'audio_url',
        'thumbnail'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_free_sample' => 'boolean',
        'is_registered_only' => 'boolean',
        'content_data' => 'array',
        'content_blocks' => 'array',
    ];

    // Relationship: A Unit belongs to a Session
    public function courseSession(): BelongsTo
    {
        return $this->belongsTo(CourseSession::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function isAccessibleBy(?User $user): bool
    {
        // 0. If unit is not published, nobody can see it
        if (!$this->is_published) return false;

        // 1. Free samples are always open, even for guests
        if ($this->is_free_sample) return true;

        // If no user is logged in, they can ONLY see free samples (handled above)
        if (!$user) return false;

        // 2. Registered only content: accessible if logged in
        // 2. Registered only content: accessible if logged in
        if ($this->is_registered_only) return true;

        // 3. Premium content: Check for active subscription
        if ($user->hasActiveSubscription()) return true;

        // If admin, always allow (fallback)
        if ($user->isAdmin()) return true;

        return false;


        /* 
         * OLD SEQUENTIAL LOGIC DISABLED FOR NOW
         * We are moving to a Paid vs Free model rather than pure sequential unlock.
         * Sequential unlock can be re-enabled later if needed on top of subscription.
         */
        
        $previousUnit = null;
        
        /* 
        // 2. Find previous unit in SAME session
        $previousUnit = Unit::where('course_session_id', $this->course_session_id)
            ->where('sort_order', '<', $this->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();
       ... disabled ...
        */

        // 3. If no previous unit in this session, check previous SESSION's last unit
        if (!$previousUnit) {
            $currentSession = $this->courseSession;
            $previousSession = CourseSession::where('module_id', $currentSession->module_id)
                ->where('sort_order', '<', $currentSession->sort_order)
                ->orderBy('sort_order', 'desc')
                ->first();

            // If no previous session, this is the very first unit of the very first session -> OPEN
            if (!$previousSession) return true;

            // Otherwise, we need to check the LAST unit of that previous session
            $previousUnit = $previousSession->units()->orderBy('sort_order', 'desc')->first();
            
            // If previous session has no units, we might want to check the session before THAT... 
            // strictly speaking, we recurse or just say "if previous session empty, allow?" 
            // For simplicity: if previous session exists but has no units, we treat it as "done" and allow access? 
            // Or better: require completion of *something*. 
            // Let's assume sessions allow empty units -> if no previous unit found, means we are at start.
            if (!$previousUnit) return true;
        }

        // 4. Check if user completed that previous unit
        return $user->completedUnits()->where('unit_id', $previousUnit->id)->exists();
    }

    /**
     * Get video statistics from Bunny.net.
     */
    public function getBunnyStats(): array
    {
        if (!$this->video_id) {
            return [];
        }

        return \Illuminate\Support\Facades\Cache::remember("unit_{$this->id}_bunny_stats", 600, function () {
            // Instantiate service directly or via container
            return app(\App\Services\BunnyService::class)->getVideoStats($this->video_id) ?? [];
        });
    }
    /**
     * Get the next unit in the sequence (even across sessions).
     */
    public function nextUnit(): ?Unit
    {
        // 1. Next unit in SAME session
        $next = Unit::where('course_session_id', $this->course_session_id)
            ->where('is_published', true)
            ->where('sort_order', '>', $this->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($next) return $next;

        // 2. First unit of NEXT session
        $currentSession = $this->courseSession;
        if (!$currentSession) return null;

        $nextSession = CourseSession::where('module_id', $currentSession->module_id)
            ->where('sort_order', '>', $currentSession->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        // If no next session in current module, check next module?
        // (Assuming for now we stay within module or simple session flow)
        
        if ($nextSession) {
            return $nextSession->units()
                ->where('is_published', true)
                ->orderBy('sort_order', 'asc')
                ->first();
        }

        return null;
    }

    public function previousUnit(): ?Unit
    {
        // 1. Previous unit in SAME session
        $prev = Unit::where('course_session_id', $this->course_session_id)
            ->where('is_published', true)
            ->where('sort_order', '<', $this->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($prev) return $prev;

        // 2. Last unit of PREVIOUS session
        $currentSession = $this->courseSession;
        if (!$currentSession) return null;

        $prevSession = CourseSession::where('module_id', $currentSession->module_id)
            ->where('sort_order', '<', $currentSession->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();
        
        if ($prevSession) {
            return $prevSession->units()
                ->where('is_published', true)
                ->orderBy('sort_order', 'desc')
                ->first();
        }

        return null;
    }
}