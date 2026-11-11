<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'batch_number',
        'expiry_date',
        'purchase_price',
        'quantity',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'purchase_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    // علاقة: الدفعة تنتمي لدواء معين
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
?>