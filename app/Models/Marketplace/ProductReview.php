<?php
namespace App\Models\Marketplace;
use Illuminate\Database\Eloquent\Model;
class ProductReview extends Model
{
    protected $fillable = ['product_id','user_id','order_id','rating','review'];
    public function product() { return $this->belongsTo(Product::class); }
    public function user() { return $this->belongsTo(\App\Models\User::class); }
}
