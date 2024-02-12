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
        Schema::create('bugdet_incomes', function (Blueprint $table) {
            $table->id();
            $table->string('description')->comment('Descrição');
            $table->date('date')->comment('Data');
            $table->decimal('value');
            $table->string('remarks')->nullable()->comment('Observacoes');
            $table->unsignedBigInteger('budget_id');
            $table->foreign('budget_id')->references('id')->on('budgets');
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bugdet_incomes');
    }
};
