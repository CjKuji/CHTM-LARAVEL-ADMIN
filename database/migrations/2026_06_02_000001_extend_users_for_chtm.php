<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email_hash', 64)->nullable()->unique()->after('id');
            $table->text('fname')->nullable()->after('email');
            $table->text('lname')->nullable()->after('fname');
            $table->string('role', 32)->default('user')->after('lname');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->dropColumn(['email_hash', 'fname', 'lname', 'role']);
            $table->string('email')->change();
        });
    }
};
