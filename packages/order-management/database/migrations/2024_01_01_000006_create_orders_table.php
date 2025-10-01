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
        Schema::create(config('order-management.tables.orders', 'orders'), function (Blueprint $table) {
            $table->id('order_id');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('workflow_id')->nullable();
            $table->unsignedBigInteger('template_id');
            $table->string('title');
            $table->text('notes')->nullable();

            // User assignments
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('shipped_by')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();

            // Timestamps for workflow
            $table->timestamp('dt_created')->nullable();
            $table->timestamp('dt_required')->nullable();
            $table->timestamp('dt_deadline')->nullable();
            $table->timestamp('dt_completed')->nullable();
            $table->timestamp('dt_approved')->nullable();
            $table->timestamp('dt_shipped')->nullable();
            $table->timestamp('dt_cancelled')->nullable();

            $table->text('cancel_reason')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('product_id')
                ->references('product_id')
                ->on(config('order-management.tables.products', 'products'))
                ->onDelete('cascade');

            $table->foreign('workflow_id')
                ->references('workflow_id')
                ->on(config('order-management.tables.workflows', 'workflows'))
                ->onDelete('set null');

            $table->foreign('template_id')
                ->references('template_id')
                ->on(config('order-management.tables.workflow_templates', 'workflow_templates'))
                ->onDelete('cascade');

            $table->foreign('store_id')
                ->references('store_id')
                ->on(config('order-management.tables.stores', 'stores'))
                ->onDelete('set null');

            // User foreign keys
            $table->foreign('assigned_to')
                ->references('id')
                ->on(config('order-management.tables.user_accounts', 'users'))
                ->onDelete('set null');

            $table->foreign('created_by')
                ->references('id')
                ->on(config('order-management.tables.user_accounts', 'users'))
                ->onDelete('set null');

            $table->foreign('approved_by')
                ->references('id')
                ->on(config('order-management.tables.user_accounts', 'users'))
                ->onDelete('set null');

            $table->foreign('shipped_by')
                ->references('id')
                ->on(config('order-management.tables.user_accounts', 'users'))
                ->onDelete('set null');

            $table->foreign('cancelled_by')
                ->references('id')
                ->on(config('order-management.tables.user_accounts', 'users'))
                ->onDelete('set null');

            $table->foreign('completed_by')
                ->references('id')
                ->on(config('order-management.tables.user_accounts', 'users'))
                ->onDelete('set null');

            // Indexes
            $table->index('assigned_to');
            $table->index('dt_required');
            $table->index('dt_deadline');
            $table->index(['dt_cancelled', 'dt_shipped']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('order-management.tables.orders', 'orders'));
    }
};
