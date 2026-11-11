<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->decimal('total_amount', 10, 2);
            $table->string('payment_method'); // cash, visa
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // لتسجيل من هو الكاشير الذي أصدر الفاتورة
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
?>