<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChurnDataset extends Model
{
    use HasFactory;

    protected $table = 'churn_datasets';

    protected $fillable = [
        'tenure',
        'contract',
        'payment_method',
        'monthly_charges',
        'total_charges',
        'internet_service',
        'online_security',
        'tech_support',
        'senior_citizen',
        'churn',
        'tenure_group',
    ];

    protected $casts = [
        'tenure'           => 'integer',
        'monthly_charges'  => 'float',
        'total_charges'    => 'float',
        'senior_citizen'   => 'boolean',
        // 'churn'            => 'boolean',
    ];
}
