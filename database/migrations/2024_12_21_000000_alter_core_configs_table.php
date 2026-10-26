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
        if (!Schema::hasColumn('core_configs', 'module')) {
            Schema::table('core_configs', function (Blueprint $table) {
                $table->string('module')->nullable()->after('store_id')->comment('module snake name or null for app or universal');
                $table->integer('position')->default(1000)->after('form_input')->comment('position in forms');
                $table->string('icon')->nullable()->after('form_input')->comment('icon for listings');
                $table->string('css_classes')->nullable()->after('icon')->comment('css classes for forms');
                $table->text('options')->nullable()->after('position')->comment('json data');
                $table->string('label')->nullable()->after('position')->comment('json data');
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
            $table->dropColumn('label');
            $table->dropColumn('options');
            $table->dropColumn('css_classes');
            $table->dropColumn('icon');
            $table->dropColumn('position');
            $table->dropColumn('module');
        });
    }

};
