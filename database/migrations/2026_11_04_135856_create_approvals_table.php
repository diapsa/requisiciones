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
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->string('approvable_type', 50);
            $table->unsignedBigInteger('approvable_id');
            $table->index(['approvable_type', 'approvable_id']);

            $table->foreignId('approver_id')->constrained('users');

            $table->string('stage', 30);
            $table->string('decision', 20);
            $table->text('comments')->nullable();
            $table->timestamp('decided_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
