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
    public function up()
    {
        // important: old migration already refactored for new installations,
        // so check if there is an old name present
        if (Schema::hasTable('email_concern_notification_event')) {
            Schema::rename('email_concerns', "notification_concerns");
            Schema::rename('email_templates', "notification_templates");
            Schema::rename('email_concern_notification_event', "notification_concern_notification_event");

            Schema::table('notification_concerns', function (Blueprint $table) {
                $table->renameColumn('email_template_id', 'notification_template_id');
            });
            Schema::table('notification_concern_notification_event', function (Blueprint $table) {
                $table->renameColumn('email_concern_id', 'notification_concern_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('notification_concern_notification_event');
    }

};
