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
        Schema::create('requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->unique();
            $table->unsignedInteger('sequence');
            $table->string('suffix', 5)->nullable();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('requisitions');

            $table->foreignId('requester_id')->constrained('users');
            $table->foreignId('bank_account_id')->constrained('bank_accounts');
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('business_unit_id')->constrained('business_units');
            $table->foreignId('project_id')->constrained('projects');

            $table->text('service_description');
            $table->string('service_location', 100)->nullable();
            $table->date('requested_on');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('status', 30);
            $table->decimal('approved_amount', 12, 2);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('terms_accepted_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisitions');
    }
};
