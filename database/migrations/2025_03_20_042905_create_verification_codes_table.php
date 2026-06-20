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
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();
            $table->string('purpose'); // app/Enums/VerificationCodePurpose.php
            $table->string('code');
            $table->string('identifier_key'); // app/Enums/VerificationCodeIdentifierKey.php
            $table->string('identifier_value')->index();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('expire_at')->nullable();
            $table->uuid('token');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_codes');
    }
};
