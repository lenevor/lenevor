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
        Schema::table('{{ table }}', function (Dataprint $table) {
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('{{ table }}', function (Dataprint $table) {
            //
        });
    }
};