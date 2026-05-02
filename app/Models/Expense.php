<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $table = 'school_expenses';

    public const CATEGORY_RUNNING = 'running';

    public const CATEGORY_PAYROLL = 'payroll';

    public const CATEGORY_UTILITIES = 'utilities';

    public const CATEGORY_MAINTENANCE = 'maintenance';

    public const CATEGORY_OTHER = 'other';

    public const CATEGORIES = [
        self::CATEGORY_RUNNING => 'Running expenses',
        self::CATEGORY_PAYROLL => 'Payroll (salaries)',
        self::CATEGORY_UTILITIES => 'Utilities',
        self::CATEGORY_MAINTENANCE => 'Maintenance',
        self::CATEGORY_OTHER => 'Other',
    ];

    public const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank transfer',
        'mobile_money' => 'Mobile money',
        'cheque' => 'Cheque',
        'other' => 'Other',
    ];

    public const STATUSES = [
        'paid' => 'Paid',
        'unpaid' => 'Unpaid',
    ];

    protected $fillable = [
        'category',
        'amount',
        'expense_date',
        'description',
        'payment_method',
        'staff_name',
        'status',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];
}
