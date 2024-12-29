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
        if (!Schema::hasTable('navigations')) {
            Schema::create('navigations', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_enabled')->default(true)->comment('True when enabled and listed');
                $table->unsignedBigInteger('parent_id')->nullable()->index();
                $table->foreign('parent_id')
                    ->references('id')
                    ->on($table->getTable())
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->mediumText('acl_resources')->nullable()->comment('json');
                $table->mediumText('groups')->nullable()->comment('json used for rebuild and filters');
                $table->string('label', 255)->nullable();
                $table->string('code', 255)->unique()->nullable()->comment('Ident this item if needed');
                $table->string('icon_class', 255)->nullable();
                $table->integer('position')->default(1000)->comment('Position in same level');
                $table->string('route', 255)->nullable()->comment('Used if not null');
                $table->mediumText('route_params')->nullable()->comment('json');
                $table->mediumText('tags')->nullable()->comment('json');
                $table->string('uri', 255)->nullable()->comment('Only used if route is null');
                $table->timestamps();
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
        Schema::dropIfExists('navigations');
    }

};
