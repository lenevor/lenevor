<?php

use Syscodes\Components\Database\Migrations\Migration;
use Syscodes\Components\Database\Schema\Dataprint;
use Syscodes\Components\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
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
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('{{ table }}');
    }
};