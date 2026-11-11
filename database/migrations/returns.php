<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users'); // الموظف الذي قام بعملية الإرجاع
            $table->string('return_number')->unique();
            $table->decimal('total_refund_amount', 10, 2); // إجمالي المبلغ المسترد للعميل
            $table->text('reason')->nullable(); // سبب الإرجاع
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};

?>