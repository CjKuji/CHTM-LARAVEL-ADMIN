<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->timestamps();
        });

        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('capacity')->default(1);
            $table->decimal('base_price', 12, 2)->default(0);
            $table->unsignedSmallInteger('min_guests')->default(1);
            $table->timestamps();
        });

        Schema::create('room_type_amenity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
            $table->unique(['room_type_id', 'amenity_id']);
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained();
            $table->string('room_number', 32);
            $table->unsignedTinyInteger('floor')->nullable();
            $table->string('status', 32)->default('available');
            $table->timestamps();

            $table->unique('room_number');
        });

        Schema::create('room_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->text('image_path');
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->unsignedSmallInteger('guests')->default(1);
            $table->unsignedTinyInteger('extra_beds')->default(0);
            $table->decimal('price_at_booking', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('message')->nullable();
            $table->string('status', 32)->default('pending');
            $table->string('payment_method', 32)->nullable();
            $table->boolean('has_child')->default(false);
            $table->text('child_age_group')->nullable();
            $table->boolean('has_pwd')->default(false);
            $table->boolean('has_senior')->default(false);
            $table->dateTime('checked_in_at')->nullable();
            $table->dateTime('checked_out_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checked_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'start_at']);
        });

        Schema::create('archived_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_booking_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->text('room_number')->nullable();
            $table->text('room_type_name')->nullable();
            $table->unsignedBigInteger('room_type_id')->nullable();
            $table->unsignedSmallInteger('room_capacity')->nullable();
            $table->decimal('room_base_price', 12, 2)->nullable();
            $table->unsignedTinyInteger('room_floor')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->dateTime('checked_in_at')->nullable();
            $table->dateTime('checked_out_at')->nullable();
            $table->unsignedSmallInteger('guests')->default(0);
            $table->string('status', 32)->nullable();
            $table->text('message')->nullable();
            $table->string('payment_method', 32)->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->unsignedTinyInteger('extra_beds')->default(0);
            $table->boolean('has_child')->default(false);
            $table->boolean('has_pwd')->default(false);
            $table->boolean('has_senior')->default(false);
            $table->text('child_age_group')->nullable();
            $table->text('guest_fname')->nullable();
            $table->text('guest_lname')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checked_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 64);
            $table->unsignedBigInteger('entity_id');
            $table->string('action', 64);
            $table->longText('old_value')->nullable();
            $table->longText('new_value')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
            $table->index('created_at');
        });

        Schema::create('housekeeping_templates', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->string('room_type_scope', 64)->nullable();
            $table->timestamps();
        });

        Schema::create('housekeeping_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('housekeeping_templates')->cascadeOnDelete();
            $table->text('item_name');
            $table->unsignedSmallInteger('default_quantity')->default(1);
            $table->timestamps();
        });

        Schema::create('housekeeping_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('housekeeping_templates')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 32)->default('pending');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('housekeeping_task_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('housekeeping_tasks')->cascadeOnDelete();
            $table->text('item_name');
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->boolean('is_done')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('housekeeping_task_items');
        Schema::dropIfExists('housekeeping_tasks');
        Schema::dropIfExists('housekeeping_template_items');
        Schema::dropIfExists('housekeeping_templates');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('archived_bookings');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('room_images');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('room_type_amenity');
        Schema::dropIfExists('room_types');
        Schema::dropIfExists('amenities');
    }
};
