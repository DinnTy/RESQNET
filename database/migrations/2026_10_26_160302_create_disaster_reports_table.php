
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disaster_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->constrained('disaster_categories')
                ->restrictOnDelete();

            $table->foreignId('location_id')
                ->constrained('locations')
                ->restrictOnDelete();

            $table->string('title');
            $table->text('description');
            $table->string('severity')->default('sedang');
            $table->string('status')->default('menunggu_verifikasi');
            $table->string('evidence')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disaster_reports');
    }
};
