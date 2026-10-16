<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where(fn ($q) => $q->whereNull('username')->orWhere('username', ''))->orderBy('id')->chunkById(200, function ($users) {
            foreach ($users as $user) {
                $base = 'user_'.$user->id;
                $name = $base;
                $suffix = 1;
                while (DB::table('users')->where('username', $name)->exists()) {
                    $name = $base.'_'.$suffix++;
                }
                DB::table('users')->where('id', $user->id)->update(['username' => $name]);
            }
        });
    }

    public function down(): void
    {
        // Generated usernames may have been shared; do not erase them on rollback.
    }
};
