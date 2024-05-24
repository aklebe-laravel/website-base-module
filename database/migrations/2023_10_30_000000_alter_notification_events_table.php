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
        if (!Schema::hasColumn('notification_events', 'event_data')) {
            Schema::table('notification_events', function (Blueprint $table) {
                $table->mediumText('event_data')->nullable()->after('event_code')->comment('event json data');
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
            $table->dropColumn('event_data');
        });
    }

};
