<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix saldo madhan: penerimaan bersih 427.500 - penarikan 50.000 = 377.500
        DB::table('users')
            ->where('username', 'madhan')
            ->update(['saldo' => 377500]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->where('username', 'madhan')
            ->update(['saldo' => 92500]);
    }
};
