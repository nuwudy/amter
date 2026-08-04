<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted()
    {
        static::creating(function ($user) {
            \Illuminate\Support\Facades\Log::info('Creating User', ['role' => $user->role]);
            if (empty($user->role)) {
                $user->role = 'student';
                \Illuminate\Support\Facades\Log::info('Role set to student default');
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'plan_id',
        'level',
        'is_suspended',
        'last_session_id',
        'subscription_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'subscription_expires_at' => 'datetime',
            'is_suspended' => 'boolean',
        ];
    }

    public function plan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function completedUnits(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class, 'completed_units')->withTimestamps();
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class)->withPivot(['starts_at', 'ends_at', 'payment_status'])->withTimestamps();
    }

    public function milestones(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    public function getNextIncompleteUnit()
    {
        // 1. Get the ID of the last completed unit
        // We use the relationship to handle the join, getting the 'unit_id' from the pivot or the related model ID
        // strict ordering by 'completed_at' desc
        $lastCompletedId = $this->completedUnits()
            ->orderBy('completed_units.completed_at', 'desc')
            ->value('units.id');

        if (!$lastCompletedId) {
            // 2. If nothing completed, get the first unit of their first enrolled course
            // Note: This picks the first unit in the DB. Logic might need refinement for multiple courses.
            return \App\Models\Unit::where('is_published', true)->orderBy('id', 'asc')->first();
        }

        // 3. Find the unit with the next ID
        return \App\Models\Unit::where('id', '>', $lastCompletedId)
            ->where('is_published', true)
            ->orderBy('id', 'asc')
            ->first();
    }

    public function getMasteryStreak(): int
    {
        $dates = $this->completedUnits()
            ->select(DB::raw('DATE(completed_units.completed_at) as date'))
            ->distinct()
            ->orderBy('date', 'desc')
            ->pluck('date');

        if ($dates->isEmpty()) return 0;

        $streak = 0;
        $currentDate = now()->startOfDay();

        // Check if they mastered something today or yesterday to keep streak alive
        if ($dates[0] != $currentDate->format('Y-m-d') && 
            $dates[0] != $currentDate->copy()->subDay()->format('Y-m-d')) {
            return 0;
        }

        $lastDate = null;
        foreach ($dates as $date) {
            $carbonDate = \Carbon\Carbon::parse($date);
            
            if ($lastDate === null || $carbonDate->diffInDays($lastDate) === 1) {
                $streak++;
                $lastDate = $carbonDate;
            } else {
                break;
            }
        }

        return $streak;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Debugging Access
        \Illuminate\Support\Facades\Log::info('canAccessPanel Check', [
            'user' => $this->email,
            'role' => $this->role,
            'panel' => $panel->getId()
        ]);

        if ($panel->getId() === 'admin') {
            return strtolower(trim($this->role)) === 'admin';
        }

        if ($this->is_suspended) {
            return false;
        }

        if ($panel->getId() === 'student') {
            return true; // All authenticated users can see the student panel
        }

        return false;
    }

    public function isAdmin(): bool
    {
        return strtolower(trim($this->role)) === 'admin';
    }

    public function hasActiveSubscription(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->subscription_expires_at && $this->subscription_expires_at->isFuture();
    }

    public function isPaid(): bool
    {
        return $this->hasActiveSubscription();
    }

    public function isRegisteredOnly(): bool
    {
        return !$this->isAdmin() && !$this->isPaid();
    }

    public function getMembershipTypeAttribute(): string
    {
        if ($this->isAdmin()) return 'Admin';
        return $this->isPaid() ? 'Paid' : 'Registered';
    }
}
