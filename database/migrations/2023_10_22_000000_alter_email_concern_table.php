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
        if (!Schema::hasColumn('notification_concerns', 'tags')) {
            Schema::table('notification_concerns', function (Blueprint $table) {
                $table->mediumText('tags')->nullable()->after('priority')->comment('json');
                $table->mediumText('meta_data')->nullable()->after('tags')->comment('json');
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
            $table->dropColumn('tags');
            $table->dropColumn('meta_data');
        });
    }

};
