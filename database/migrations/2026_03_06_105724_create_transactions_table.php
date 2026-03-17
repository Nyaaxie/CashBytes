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
        Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('wallet_id'); 
                $table->enum('type', ['transfer', 'save', 'load', 'bill_payment']);
                $table->enum('direction', ['debit', 'credit']);
                $table->decimal('amount', 15, 2);
                $table->string('reference_no')->unique();
                $table->string('description')->nullable();
                $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
                $table->nullableMorphs('transactable');
                $table->timestamps();
    // Foreign key at the bottom
                $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
             
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
