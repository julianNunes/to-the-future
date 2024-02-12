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
        Schema::create('financings', function (Blueprint $table) {
            $table->id();
            $table->string('description')->comment('Descrição');
            $table->date('start_date')->comment('Data de contratação do financiamento');
            $table->decimal('total')->comment('Valor total do emprestimo');
            $table->decimal('fees_monthly')->nullable()->comment('Valor mensal de juros');
            $table->char('portion_total', 3)->comment('Total de Parcelas');
            $table->string('remarks')->nullable()->comment('Observacoes');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financings');
    }
};
