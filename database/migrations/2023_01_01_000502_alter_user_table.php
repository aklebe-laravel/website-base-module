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
        if (!Schema::hasColumn('users', 'shared_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('shared_id', 255)->unique()->nullable()->after('email');
                $table->timestamp('last_visited_at')->nullable()->after('email_verified_at');
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_visited_at');
            $table->dropColumn('shared_id');
        });
    }

};
