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
        Schema::create('budget_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('description')->comment('Descrição');
            $table->date('date')->comment('Data de Vencimento');
            $table->decimal('value');
            $table->string('remarks');
            $table->decimal('share_value')->nullable()->comment('Valor total compartilhado');
            $table->boolean('paid')->default(false);
            $table->unsignedBigInteger('budget_id');
            $table->foreign('budget_id')->references('id')->on('budgets');
            $table->unsignedBigInteger('share_user_id')->nullable()->comment('Id do usuario que sera compartilhado o gasto');
            $table->foreign('share_user_id')->references('id')->on('users');
            $table->unsignedBigInteger('financing_installment_id')->nullable()->comment('Id da parcela de financiamento');
            $table->foreign('financing_installment_id')->references('id')->on('financing_installments');
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_expenses');
    }
};
