
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aid_request_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('aid_request_id')
                ->constrained('aid_requests')
                ->cascadeOnDelete();

            $table->foreignId('aid_item_id')
                ->constrained('aid_items')
                ->restrictOnDelete();

            $table->unsignedInteger('quantity');
            $table->string('unit')->default('unit');
            $table->string('priority')->default('sedang');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['aid_request_id', 'aid_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aid_request_items');
    }
};
