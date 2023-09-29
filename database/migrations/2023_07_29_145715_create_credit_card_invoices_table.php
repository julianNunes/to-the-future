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
        Schema::create('credit_card_invoices', function (Blueprint $table) {
            $table->id();
            $table->date('due_date')->comment('Data de vencimento');
            $table->date('closing_date')->comment('Data de fechamento');
            $table->char('year', 4)->comment('Ano da fatura');
            $table->char('month', 2)->comment('Mês de vencimento');
            $table->decimal('total')->default(0);
            $table->decimal('total_paid')->nullable();
            $table->boolean('closed')->default(false)->comment('Fatura fechada');
            $table->string('remarks')->comment('Observações');
            $table->unsignedBigInteger('credit_card_id');
            $table->foreign('credit_card_id')->references('id')->on('credit_cards');
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_card_invoices');
    }
};
