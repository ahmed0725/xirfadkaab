<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use HasFactory;

    public const SHIFTS = ['morning', 'afternoon', 'evening'];

    protected $fillable = [
        'class_name',
        'course_type_id',
        'start_date',
        'duration_months',
        'end_date',
        'class_time',
        'classroom',
        'monthly_fee_amount',
        'shift',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'monthly_fee_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (SchoolClass $class): void {
            if ($class->start_date && $class->duration_months !== null) {
                $start = $class->start_date instanceof CarbonInterface
                    ? $class->start_date->copy()
                    : Carbon::parse($class->start_date)->startOfDay();
                $class->end_date = $start->copy()->addMonths((int) $class->duration_months);
            }
        });

        // Deactivating a class deactivates its students (one-way: reactivating
        // a class does not reactivate students automatically).
        static::updated(function (SchoolClass $class): void {
            if ($class->wasChanged('is_active') && ! $class->is_active) {
                $class->students()->update(['status' => 'inactive']);
            }
        });
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Classes visible to a teacher (assigned via pivot).
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForTeacher($query, User $user)
    {
        return $query->whereHas('teachers', fn ($q) => $q->whereKey($user->id));
    }

    public function courseType(): BelongsTo
    {
        return $this->belongsTo(CourseType::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'school_class_user')->withTimestamps();
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'school_class_id');
    }

    /**
     * Human-readable label for dropdowns (disambiguates parallel cohorts).
     */
    public function getDisplayNameAttribute(): string
    {
        $parts = [$this->class_name];

        if ($this->relationLoaded('courseType') && $this->courseType) {
            $parts[] = $this->courseType->name;
        }

        if ($this->class_time) {
            $parts[] = Carbon::parse($this->class_time)->format('g:i A');
        }

        if ($this->shift) {
            $parts[] = ucfirst((string) $this->shift);
        }

        return implode(' · ', array_filter($parts));
    }

    public function formattedClassTime(): string
    {
        if (! $this->class_time) {
            return '';
        }

        return Carbon::parse($this->class_time)->format('g:i A');
    }
}
