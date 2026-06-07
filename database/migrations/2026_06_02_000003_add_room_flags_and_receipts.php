<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->boolean('make_up_room')->default(false)->after('status');
            $table->boolean('checkout_requested')->default(false)->after('make_up_room');
        });

        Schema::table('housekeeping_templates', function (Blueprint $table) {
            $table->foreignId('room_type_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::table('housekeeping_tasks', function (Blueprint $table) {
            $table->text('note')->nullable()->after('status');
        });

        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->text('original_filename');
            $table->text('storage_path');
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_receipts');

        Schema::table('housekeeping_tasks', function (Blueprint $table) {
            $table->dropColumn('note');
        });

        Schema::table('housekeeping_templates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_type_id');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['make_up_room', 'checkout_requested']);
        });
    }
};
