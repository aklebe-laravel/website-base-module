<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('changelogs')) {
            Schema::create('changelogs', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_public')->default(true)->comment('True if can be shown');
                $table->string('path', 255)->nullable()->comment('Path to git repository');
                $table->string('hash', 255);
                $table->string('author', 255)->nullable()->comment('Git Author');
                $table->string('commit_created_at', 255)->nullable()->comment('Commit date');
                $table->mediumText('acl_resources')->nullable()->comment('json with permissions or for everyone');
                $table->mediumText('messages')
                    ->nullable()
                    ->comment('messages from git, can be corrected here (commonly for developers)');
                $table->mediumText('messages_staff')->nullable()->comment('messages for staff changelog');
                $table->mediumText('messages_public')->nullable()->comment('messages for public changelog');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('changelogs');
    }

};
