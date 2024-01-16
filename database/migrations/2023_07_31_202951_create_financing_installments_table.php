<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('financing_installments', function (Blueprint $table) {
            $table->id();
            $table->decimal('value')->comment('Valor da parcela');
            $table->decimal('paid_value')->nullable()->comment('Valor pago da parcela');
            $table->integer('portion')->comment('Parcela atual');
            $table->date('date')->comment('Data da vencimento');
            $table->date('payment_date')->nullable()->comment('Data de Pagamento');
            $table->boolean('paid')->default(false);
            $table->unsignedBigInteger('financing_id');
            $table->foreign('financing_id')->references('id')->on('financings');
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financing_installments');
    }
};
