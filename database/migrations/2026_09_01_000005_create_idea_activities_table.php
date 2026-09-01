<?php

use App\Models\Idea;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idea_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Idea::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->string('type', 40);
            $table->string('description');
            $table->timestamps();

            $table->index(['idea_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idea_activities');
    }
};
