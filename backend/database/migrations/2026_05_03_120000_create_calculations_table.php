<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculations', function (Blueprint $table): void {
            $table->id();
            $table->string('expression', 1000);
            $table->string('result', 255)->nullable();
            $table->string('error_message', 500)->nullable();
            $table->string('status', 32);
            $table->timestamp('evaluated_at')->useCurrent();
            $table->index('evaluated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculations');
    }
};
