<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdditionalFeeCharge extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'books' => 'Books',
        'certificates' => 'Certificates',
        'other' => 'Other services',
    ];

    protected $fillable = [
        'student_id',
        'category',
        'title',
        'total_amount',
        'paid',
        'balance',
        'date',
        'receipt_no',
    ];

    protected $casts = [
        'date' => 'date',
        'total_amount' => 'decimal:2',
        'paid' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
