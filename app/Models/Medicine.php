<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'barcode',
        'name',
        'category',
        'quantity',
        'alert_threshold',
        'purchase_price',
        'selling_price',
        'expiry_date',
        'supplier_name',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'expiry_date' => 'date', 
    ];

   
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    use Illuminate\Database\Eloquent\Relations\HasMany;

public function batches(): HasMany
{
    return $this->hasMany(MedicineBatch::class);
}


public function getTotalStockAttribute()
{
    return $this->batches()->sum('quantity');
}
}
?>