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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->char('year', 4)->comment('Ano da fatura');
            $table->char('month', 2)->comment('Mês de vencimento');
            $table->date('start_week_1')->nullable()->comment('Data inicial da semana 1');
            $table->date('end_week_1')->nullable()->comment('Data final da semana 1');
            $table->date('start_week_2')->nullable()->comment('Data inicial da semana 2');
            $table->date('end_week_2')->nullable()->comment('Data final da semana 2');
            $table->date('start_week_3')->nullable()->comment('Data inicial da semana 3');
            $table->date('end_week_3')->nullable()->comment('Data final da semana 3');
            $table->date('start_week_4')->nullable()->comment('Data inicial da semana 4');
            $table->date('end_week_4')->nullable()->comment('Data final da semana 4');
            $table->decimal('total_expense')->default(0)->comment('Total de Despesas');
            $table->decimal('total_income')->default(0)->comment('Total da Receita');
            $table->boolean('closed')->default(false)->comment('Marcador para finalizar o Orçamento');
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
        Schema::dropIfExists('budgets');
    }
};
