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
        Schema::create('fix_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('description')->comment('Descrição');
            $table->date('due_date')->comment('Data de vencimento');
            $table->decimal('value');
            $table->char('portion', 3)->nullable()->comment('Parcela atual');
            $table->char('portion_total', 3)->nullable()->comment('Total de Parcelas');
            $table->string('remarks');
            $table->decimal('share_value')->nullable()->comment('Valor total compartilhado');
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
        Schema::dropIfExists('fix_expenses');
    }
};