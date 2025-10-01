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
        Schema::create(config('order-management.tables.template_fields', 'template_fields'), function (Blueprint $table) {
            $table->id('field_id');
            $table->unsignedBigInteger('template_id');
            $table->string('field_type');
            $table->string('field_name');
            $table->text('settings')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('template_id')
                ->references('template_id')
                ->on(config('order-management.tables.workflow_templates', 'workflow_templates'))
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('order-management.tables.template_fields', 'template_fields'));
    }
};
