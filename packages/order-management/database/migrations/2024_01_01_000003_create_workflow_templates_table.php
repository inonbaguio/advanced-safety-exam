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
        Schema::create(config('order-management.tables.workflow_templates', 'workflow_templates'), function (Blueprint $table) {
            $table->id('template_id');
            $table->unsignedBigInteger('system_id')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('template_name');
            $table->string('workflow_name');
            $table->string('icon')->nullable();
            $table->text('intro_text')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('approval_required')->default(true);
            $table->timestamps();

            $table->foreign('company_id')
                ->references('company_id')
                ->on(config('order-management.tables.companies', 'companies'))
                ->onDelete('cascade');

            $table->foreign('store_id')
                ->references('store_id')
                ->on(config('order-management.tables.stores', 'stores'))
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('order-management.tables.workflow_templates', 'workflow_templates'));
    }
};
