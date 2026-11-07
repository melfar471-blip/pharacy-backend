<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'barcode', 'name', 'category', 'quantity', 
        'alert_threshold', 'purchase_price', 'selling_price', 
        'expiry_date', 'supplier_name'
    ];
}
?>