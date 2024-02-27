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
        Schema::create('budget_provisions', function (Blueprint $table) {
            $table->id();
            $table->string('description')->comment('Descricao');
            $table->decimal('value');
            $table->enum('group', ['MONTHLY', 'WEEK_1', 'WEEK_2', 'WEEK_3', 'WEEK_4']);
            $table->string('remarks')->nullable()->comment('Observacoes');
            $table->decimal('share_value')->nullable()->comment('Valor total compartilhado');
            $table->unsignedBigInteger('share_user_id')->nullable()->comment('Id do usuario que sera compartilhado o gasto');
            $table->foreign('share_user_id')->references('id')->on('users');
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
        Schema::dropIfExists('budget_provisions');
    }
};
