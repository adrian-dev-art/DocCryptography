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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            // Add your desired columns here
            $table->string('original_file_name');
            $table->string('encrypted_file_name')->nullable();
            $table->string('decrypted_file_name')->nullable();
            $table->integer('file_size')->nullable(); // Added file_size column
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('receiver_id');
            $table->enum('status', ['encrypted', 'decrypted', 'opened']);
            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users');
            $table->foreign('receiver_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
