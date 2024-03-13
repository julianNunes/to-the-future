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
        Schema::create('prepaid_card_extracts', function (Blueprint $table) {
            $table->id();
            $table->char('year', 4)->comment('Ano do extrato');
            $table->char('month', 2)->comment('Mês do Extrato');
            $table->decimal('credit')->default(0);
            $table->date('credit_date')->comment('Data do Crédito');
            $table->string('remarks')->nullable()->comment('Observações');
            $table->unsignedBigInteger('prepaid_card_id');
            $table->foreign('prepaid_card_id')->references('id')->on('prepaid_cards');
            $table->unsignedBigInteger('budget_id')->nullable();
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
        Schema::dropIfExists('prepaid_card_extracts');
    }
};
