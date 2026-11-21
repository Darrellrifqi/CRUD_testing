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
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20)->unique();
            $table->string('email')->unique();
            $table->string('vehicle_type', 50)->comment('motorcycle, bicycle, car, truck, van');
            $table->string('vehicle_plate', 20)->unique();
            $table->tinyInteger('level')->unsigned()->default(1)->comment('Courier level: 1 (junior) to 5 (elite)');
            $table->boolean('is_active')->default(true);
            $table->string('nik', 16)->unique()->comment('Indonesian national ID number');
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->decimal('rating', 3, 2)->default(5.00)->comment('Average rating 0.00 - 5.00');
            $table->unsignedInteger('total_deliveries')->default(0);
            $table->date('registered_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
