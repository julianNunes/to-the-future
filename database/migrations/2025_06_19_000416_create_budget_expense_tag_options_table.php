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
        Schema::create('budget_expense_tag_options', function (Blueprint $table) {
            $table->id();
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
        Schema::dropIfExists('budget_expense_tag_options');
    }
};
