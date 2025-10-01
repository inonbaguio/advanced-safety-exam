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
        Schema::create(config('order-management.tables.companies', 'companies'), function (Blueprint $table) {
            $table->id('company_id');
            $table->string('name');
            $table->enum('approval_style', ['Per User', 'Global'])->default('Per User');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('order-management.tables.companies', 'companies'));
    }
};
