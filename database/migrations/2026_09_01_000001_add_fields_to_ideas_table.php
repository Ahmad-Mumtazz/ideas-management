<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ideas', function (Blueprint $table) {
            $table->string('title')->nullable()->after('user_id');
            $table->string('status', 20)->default('pending')->after('description');
            $table->string('priority', 20)->default('medium')->after('status');
            $table->string('category')->nullable()->after('priority');
            $table->json('tags')->nullable()->after('category');
            $table->date('due_date')->nullable()->after('tags');
            $table->string('cover_image')->nullable()->after('due_date');
            $table->timestamp('archived_at')->nullable()->after('cover_image');

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'priority']);
            $table->index(['user_id', 'archived_at']);
            $table->index('due_date');
        });

        // Backfill a title for any rows that pre-date this column.
        DB::table('ideas')->whereNull('title')->update([
            'title' => DB::raw('TRIM(SUBSTRING(description, 1, 80))'),
        ]);
        DB::table('ideas')->where('title', '')->update(['title' => 'Untitled idea']);

        Schema::table('ideas', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('ideas', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['user_id', 'priority']);
            $table->dropIndex(['user_id', 'archived_at']);
            $table->dropIndex(['due_date']);

            $table->dropColumn([
                'title', 'status', 'priority', 'category',
                'tags', 'due_date', 'cover_image', 'archived_at',
            ]);
        });
    }
};
