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
        if (!Schema::hasColumn('users', 'is_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_enabled')->default(true)->after('id')->comment('System based switch to ban user');
                $table->boolean('is_deleted')->default(false)->after('is_enabled')->comment('User deleted, data were anonymized');
                $table->mediumText('options')->nullable()->after('shared_id')->comment('json data like socials');
                $table->timestamp('order_to_delete_at')->nullable()->after('remember_token')->comment('the time the termination has ordered');
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
            $table->dropColumn('order_to_delete_at');
            $table->dropColumn('is_enabled');
            $table->dropColumn('is_deleted');
            $table->dropColumn('options');
        });
    }

};
