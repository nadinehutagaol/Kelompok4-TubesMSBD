<?php
namespace App\Models\Marketplace;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
class Order extends Model
{
    protected $fillable = ['order_number','buyer_id','subtotal','total','status','placed_at'];
    protected $casts = ['subtotal'=>'decimal:2','total'=>'decimal:2','placed_at'=>'datetime'];
    public function buyer() { return $this->belongsTo(User::class, 'buyer_id'); }
    public function items() { return $this->hasMany(OrderItem::class); }
    public function payment() { return $this->hasOne(Payment::class); }
}
