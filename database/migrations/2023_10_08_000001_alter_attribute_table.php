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
        if (!Schema::hasColumn('model_attribute_assignments', 'form_position')) {
            Schema::table('model_attribute_assignments', function (Blueprint $table) {
                $table->integer('form_position')->default(1000)->after('attribute_input')->comment('position in form');
                $table->string('form_css', 255)->nullable()->after('form_position')->comment('form element css');
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
        Schema::table('model_attribute_assignments', function (Blueprint $table) {
            $table->dropColumn('form_css');
            $table->dropColumn('form_position');
        });
    }

};
