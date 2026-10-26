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
        if (!Schema::hasTable('addresses')) {
            Schema::create('addresses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('parent_id')->nullable()->index();
                $table->foreign('parent_id')
                    ->references('id')
                    ->on($table->getTable())
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
                $table->boolean('is_enabled')->default(true)->comment('True when enabled for selection');
                $table->boolean('is_public')->default(true)->comment('Show in profile');
                $table->string('title', 20)->nullable();
                $table->string('firstname', 80)->nullable();
                $table->string('lastname', 80)->nullable();
                $table->string('email', 255)->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('country_iso', 10)->nullable();
                $table->string('street', 255)->nullable();
                $table->string('city', 100)->nullable();
                $table->string('region', 100)->nullable();
                $table->string('zip', 10)->nullable()->comment('ZIP/PLZ');
                $table->unsignedBigInteger('auto_region_id')->nullable()->index()->comment('Always use this if given');
                $table->foreign('auto_region_id')->references('id')->on('regions'); // no cascade here!
                $table->string('user_description', 255)->nullable();
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
        Schema::dropIfExists('addresses');
    }

};
