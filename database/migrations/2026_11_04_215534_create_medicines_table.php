<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->unique();
            $table->string('name');
            $table->string('category');
            $table->integer('quantity');
            $table->integer('alert_threshold')->default(10); // حد الطلب للتنبيه
            $table->decimal('purchase_price', 8, 2);
            $table->decimal('selling_price', 8, 2);
            $table->date('expiry_date');
            $table->string('supplier_name')->nullable();
            $table->timestamps();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_method'); // cash, visa
            $table->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('medicine_id')->constrained();
            $table->integer('quantity');
            $table->decimal('unit_price', 8, 2);
            $table->decimal('subtotal', 8, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('medicines');
    }
};
 ?>
