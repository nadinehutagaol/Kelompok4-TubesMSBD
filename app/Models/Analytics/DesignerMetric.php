<?php

namespace App\Models\Analytics;

use Illuminate\Database\Eloquent\Model;

class DesignerMetric extends Model
{
    protected $connection = 'analytics';
    protected $table = 'designer_metrics';
    protected $primaryKey = 'designer_id';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];
}
