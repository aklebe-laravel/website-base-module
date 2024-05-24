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
        if (Schema::hasColumn('notification_events', 'notification_concern_id')) {
            Schema::table('notification_events', function (Blueprint $table) {
                $table->dropForeign(['notification_concern_id']);
                $table->dropColumn(['notification_concern_id']);
            });
        }

        if (!Schema::hasTable('notification_concern_notification_event')) {
            Schema::create('notification_concern_notification_event', function (Blueprint $table) {
                $table->unsignedBigInteger('notification_event_id')->unsigned();
                $table->unsignedBigInteger('notification_concern_id')->unsigned();

                $table->unique(['notification_event_id', 'notification_concern_id'], 'u_not_ev_email_conc_id');
                $table->foreign('notification_event_id', 'fk_not_ev_email_conc_ne_id')
                    ->references('id')
                    ->on('notification_events')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->foreign('notification_concern_id', 'fk_not_ev_email_conc_ec_id')
                    ->references('id')
                    ->on('notification_concerns')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->timestamps();
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
        Schema::dropIfExists('notification_concern_notification_event');
    }

};
