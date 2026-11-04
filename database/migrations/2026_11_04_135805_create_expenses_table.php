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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_report_id')
                ->constrained('expense_reports');
            $table->foreignId('expense_type_id')
                ->constrained('expense_types');

            $table->unsignedSmallInteger('line_number');
            $table->string('receipt_type', 30);
            $table->string('invoice_number', 100)->nullable();
            $table->char('cfdi_uuid', 36)->nullable()->unique();
            $table->string('issuer_rfc', 13)->nullable();
            $table->string('vendor_name', 200);
            $table->date('incurred_on');

            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax_amount', 12, 2);
            $table->decimal('total_amount', 12, 2);

            $table->text('justification')->nullable();
            $table->string('xml_path', 255)->nullable();
            $table->string('receipt_path', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
