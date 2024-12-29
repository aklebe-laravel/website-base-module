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
        // add notification_channel to notification_templates
        if (!Schema::hasColumn('notification_templates', 'notification_channel')) {
            Schema::table('notification_templates', function (Blueprint $table) {
                $table->dropUnique(['code']);
                $table->string('notification_channel')->nullable()->after('code')->comment('Channel or null for non specific');
                $table->unique(['code', 'notification_channel']);
            });
        }
        // drop notification channel from notification_events
        if (Schema::hasColumn('notification_events', 'preferred_channel')) {
            Schema::table('notification_events', function (Blueprint $table) {
                $table->dropColumn(['preferred_channel']);
            });
        }
        // fix foreign from notification_templates to notification_concerns
        Schema::table('notification_events', function (Blueprint $table) {
            $table->dropForeign(['notification_concern_id']);
            $table->foreign('notification_concern_id')
                ->references('id')
                ->on('notification_concerns')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            $table->dropColumn('notification_channel');
        });
    }

};
