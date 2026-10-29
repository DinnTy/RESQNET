
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribution_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('distribution_id')
                ->constrained('distributions')
                ->cascadeOnDelete();

            $table->foreignId('aid_item_id')
                ->constrained('aid_items')
                ->restrictOnDelete();

            $table->unsignedInteger('quantity');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_details');
    }
};
