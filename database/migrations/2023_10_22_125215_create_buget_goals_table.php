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
        Schema::create('buget_goals', function (Blueprint $table) {
            $table->id();
            $table->string('description')->comment('Descrição');
            $table->decimal('value')->comment('Valor da meta');
            $table->enum('group', ['PORTION', 'MONTHLY', 'WEEK_1', 'WEEK_2', 'WEEK_3', 'WEEK_4'])->nullable()->comment('Observacoes');
            $table->boolean('count_share')->default(false);
            $table->unsignedBigInteger('budget_id');
            $table->foreign('budget_id')->references('id')->on('budgets');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buget_goals');
    }
};
