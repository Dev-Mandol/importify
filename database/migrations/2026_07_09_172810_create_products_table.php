<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('upload_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('shopify_product_id')->nullable();

            $table->string('handle')->nullable();
            $table->string('title');
            $table->string('vendor')->nullable();
            $table->string('product_type')->nullable();
            $table->string('sku')->nullable();

            $table->decimal('price', 10, 2)->nullable();

            $table->string('status')->default('pending');

            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('sku');
            $table->index('shopify_product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
