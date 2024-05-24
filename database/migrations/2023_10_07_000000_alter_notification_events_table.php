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
        if (!Schema::hasColumn('notification_events', 'subject')) {
            Schema::table('notification_events', function (Blueprint $table) {
                $table->string('subject', 255)
                    ->nullable()
                    ->after('notification_concern_id')
                    ->comment('subject if notification concern is no set');;
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
        Schema::table('notification_events', function (Blueprint $table) {
            $table->dropColumn('subject');
        });
    }

};
