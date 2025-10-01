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
        Schema::create(config('order-management.tables.order_permissions', 'order_permissions'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('user_id');
            $table->string('module');
            $table->string('permission_type'); // 'manager', 'editor', etc.
            $table->boolean('can_approve')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_ship')->default(false);
            $table->boolean('can_cancel')->default(false);
            $table->timestamps();

            $table->foreign('order_id')
                ->references('order_id')
                ->on(config('order-management.tables.orders', 'orders'))
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on(config('order-management.tables.user_accounts', 'users'))
                ->onDelete('cascade');

            $table->unique(['order_id', 'user_id', 'module']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('order-management.tables.order_permissions', 'order_permissions'));
    }
};
