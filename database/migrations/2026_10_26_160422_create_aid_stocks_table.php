
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aid_stocks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('warehouse_id')
    ->constrained('warehouses')
    ->restrictOnDelete();

            $table->foreignId('aid_item_id')
                ->constrained('aid_items')
                ->restrictOnDelete();

            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('reserved_quantity')->default(0);

            $table->timestamps();

            $table->unique(['warehouse_id', 'aid_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aid_stocks');
    }
};
