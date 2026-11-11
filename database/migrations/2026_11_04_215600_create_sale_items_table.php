<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            // ربط صحيح مع جدول sales وجدول medicines مع الحذف المتسلسل أو المقيد
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('medicine_id')->constrained('medicines')->onDelete('restrict');
            $table->integer('quantity');
            $table->decimal('unit_price', 8, 2);
            $table->decimal('subtotal', 8, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
?>
