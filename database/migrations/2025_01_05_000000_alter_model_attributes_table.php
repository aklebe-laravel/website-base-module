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
        if (!Schema::hasColumn('model_attributes', 'module')) {
            Schema::table('model_attributes', function (Blueprint $table) {
                $table->string('module')->nullable()->after('id')->comment('module snake name or null for app or universal');
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
        Schema::table('core_configs', function (Blueprint $table) {
            $table->dropColumn('module');
        });
    }

};
