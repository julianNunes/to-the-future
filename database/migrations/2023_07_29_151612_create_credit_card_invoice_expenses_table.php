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
        Schema::create('credit_card_invoice_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('description')->comment('Descricao');
            $table->date('date')->comment('Data da compra');
            $table->decimal('value');
            $table->enum('group', ['PORTION', 'WEEK_1', 'WEEK_2', 'WEEK_3', 'WEEK_4']);
            $table->integer('portion')->nullable()->comment('Parcela atual');
            $table->integer('portion_total')->nullable()->comment('Total de Parcelas');
            $table->string('remarks')->nullable()->comment('Observacoes');
            $table->decimal('share_value')->nullable()->comment('Valor total compartilhado');
            $table->unsignedBigInteger('invoice_id');
            $table->foreign('invoice_id')->references('id')->on('credit_card_invoices');
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
        Schema::dropIfExists('credit_card_invoice_expenses');
    }
};
