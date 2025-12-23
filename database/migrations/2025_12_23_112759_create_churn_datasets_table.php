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
       
    Schema::create('churn_datasets', function (Blueprint $table) {
        $table->id();

        $table->integer('tenure');
        $table->string('contract');
        $table->string('payment_method');
        $table->decimal('monthly_charges', 8, 2);
        $table->decimal('total_charges', 10, 2)->nullable();
        $table->string('internet_service');
        $table->string('online_security');
        $table->string('tech_support');
        $table->boolean('senior_citizen');
        $table->string('churn');
        $table->string('tenure_group');

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('churn_datasets');
    }
};
