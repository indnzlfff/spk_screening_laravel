<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('screening_validations')) {
            return;
        }

        Schema::create('screening_validations', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('screening_id')->unique();
            $table->enum('status', ['menunggu', 'dikonfirmasi'])->default('menunggu');
            $table->text('catatan_admin')->nullable();
            $table->unsignedInteger('validated_by')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->foreign('screening_id')->references('id')->on('screening')->cascadeOnDelete();
            $table->foreign('validated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_validations');
    }
};
