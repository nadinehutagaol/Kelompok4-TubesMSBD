<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;

class DailyMetric extends Model
{
    protected $connection = 'analytics';
    protected $table = 'daily_metrics';
    protected $primaryKey = 'metric_date';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];
}
