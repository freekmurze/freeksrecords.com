<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('passkeys');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
