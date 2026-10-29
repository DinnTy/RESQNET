    
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aid_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('disaster_report_id')
                ->constrained('disaster_reports')
                ->cascadeOnDelete();

            $table->string('item_name');
            $table->unsignedInteger('quantity');
            $table->string('unit')->default('unit');
            $table->string('priority')->default('sedang');
            $table->string('status')->default('menunggu');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aid_requests');
    }
};
