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
            $table->integer('portion')->nullable()->comment('Parcela atual');
            $table->integer('portion_total')->nullable()->comment('Total de Parcelas');
            $table->uuid('group_portion')->nullable()->comment('Grupo de parcelas, usado para agrupar despesas que foram parceladas');
            $table->decimal('share_value')->nullable()->comment('Valor total compartilhado');
            $table->enum('group', ['MONTHLY', 'INDIVIDUAL'])->default('MONTHLY')->comment('Valor fixo demonstrar que os valores são RECORRENTES');
            $table->string('remarks')->nullable()->comment('Observacoes');
            $table->boolean('paid')->default(false);
            $table->index('group_portion');
            $table->unsignedBigInteger('budget_id');
            $table->foreign('budget_id')->references('id')->on('budgets');
            $table->unsignedBigInteger('share_user_id')->nullable()->comment('Id do usuario que sera compartilhado o gasto');
            $table->foreign('share_user_id')->references('id')->on('users');
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
