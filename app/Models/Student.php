<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'name',
        'mother_name',
        'phone',
        'age',
        'gender',
        'school_class_id',
        'status',
        'registration_date',
    ];

    protected $casts = [
        'registration_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-generate student_id in format XIR-001, XIR-002, ...
        static::creating(function (Student $student): void {
            if (empty($student->student_id)) {
                $student->student_id = static::generateNextStudentId();
            }
        });
    }

    public static function generateNextStudentId(): string
    {
        // DB-agnostic generator: parse existing XIR-### values and increment.
        // (Avoids DB-specific substring/cast differences between MySQL/SQLite.)
        $maxSuffix = 0;

        $existing = static::query()
            ->where('student_id', 'like', 'XIR-%')
            ->pluck('student_id');

        foreach ($existing as $value) {
            if (preg_match('/^XIR-(\d+)$/', (string) $value, $matches)) {
                $maxSuffix = max($maxSuffix, (int) $matches[1]);
            }
        }

        $next = $maxSuffix + 1;

        return 'XIR-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class);
    }

    public function additionalFeeCharges(): HasMany
    {
        return $this->hasMany(AdditionalFeeCharge::class);
    }

    public function examResults(): HasMany
    {
        return $this->hasMany(ExamResult::class);
    }
}
