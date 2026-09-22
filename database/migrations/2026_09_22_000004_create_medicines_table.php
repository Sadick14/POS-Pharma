<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('dosage_form')->nullable(); // Tablet, Capsule, Syrup, Injection, Cream, Drops, Inhaler, etc.
            $table->string('strength')->nullable(); // e.g. 500mg, 100ml, 5mg/ml
            $table->string('unit')->default('Box'); // Box, Strip, Bottle, Ampoule, Tube, Piece
            $table->string('barcode')->nullable()->unique()->index();
            $table->string('manufacturer')->nullable();
            $table->text('description')->nullable();
            $table->integer('reorder_level')->default(20);
            $table->decimal('selling_price', 12, 2)->default(0.00);
            $table->string('status')->default('active'); // active, inactive
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
