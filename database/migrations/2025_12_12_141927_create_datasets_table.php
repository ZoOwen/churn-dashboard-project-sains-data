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
        Schema::create('datasets', function (Blueprint $table) {
           $table->id();

        // nama dataset sebagai label
        // $table->string('name');

        // file info
        $table->string('original_filename'); // nama file asli
        $table->string('stored_filename');   // nama file disimpan di storage
        $table->string('file_type');         // csv / xlsx / xls
        $table->integer('file_size');        // byte

        // metadata tambahan
        $table->integer('total_rows')->nullable(); // optional, bisa diisi habis parsing
        $table->json('columns')->nullable();       // simpan nama kolom (optional)

        // for V2: logs / processing info
        $table->timestamp('processed_at')->nullable();
        $table->string('processed_by')->nullable();

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('datasets');
    }
};
