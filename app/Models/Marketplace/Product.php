<?php
namespace App\Models\Marketplace;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
class Product extends Model
{
    protected $fillable = ['seller_id','category_id','title','slug','description','price','preview_path','file_path','stock','is_active'];
    protected $casts = ['price' => 'decimal:2', 'is_active' => 'boolean'];
    public function getRouteKeyName(): string { return 'slug'; }
    public function seller() { return $this->belongsTo(User::class, 'seller_id'); }
    public function category() { return $this->belongsTo(ProductCategory::class, 'category_id'); }
    public function reviews() { return $this->hasMany(ProductReview::class); }
}
