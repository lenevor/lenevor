<?php

use Syscodes\Components\Database\Migrations\Migration;
use Syscodes\Components\Database\Schema\Dataprint;
use Syscodes\Components\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('{{ table }}', function (Dataprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('{{ table }}');
    }
};