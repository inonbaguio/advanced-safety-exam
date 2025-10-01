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
        Schema::create(config('order-management.tables.products', 'products'), function (Blueprint $table) {
            $table->id('product_id');
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('template_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('template_id')
                ->references('template_id')
                ->on(config('order-management.tables.workflow_templates', 'workflow_templates'))
                ->onDelete('cascade');

            // Assumes users table exists - customize as needed
            $table->foreign('owner_id')
                ->references('id')
                ->on(config('order-management.tables.user_accounts', 'users'))
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('order-management.tables.products', 'products'));
    }
};
