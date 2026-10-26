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
        if (!Schema::hasColumn('notification_concerns', 'sender_id')) {
            Schema::table('notification_concerns', function (Blueprint $table) {
                $table->dropColumn('sender');
                $table->unsignedBigInteger('sender_id')->after('reason_code')->nullable()->index();
                $table->foreign('sender_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
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
        Schema::table('notification_concerns', function (Blueprint $table) {
            $table->dropColumn('sender_id');
            $table->string('sender', 255)->after('reason_code')->nullable()->comment('email address of sender');
        });
    }

};
