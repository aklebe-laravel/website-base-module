<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\WebsiteBase\app\Models\ModelAttributeAssignment;
use Modules\WebsiteBase\app\Models\ViewTemplate;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('countries')) {
            Schema::create('countries', function (Blueprint $table) {
                $table->id();
                $table->string('iso', 2)->nullable()->comment('default iso');
                $table->string('iso3', 3)->nullable()->comment('iso 3 letters');
                $table->string('name', 100)->nullable()->comment('country name');
                $table->string('nice_name', 100)->nullable()->comment('human readable name');
                $table->string('num_code', 10)->nullable()->comment('public number');
                $table->string('phone_code', 10)->nullable()->comment('county phone prefix');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('regions')) {
            Schema::create('regions', function (Blueprint $table) {
                $table->id();
                $table->string('zipcode', 5)->nullable()->comment('ZIP/PLZ');
                $table->string('city', 49)->nullable()->comment('City');
                $table->string('state', 22)->nullable()->comment('State');
                $table->string('community', 45)->nullable()->comment('Community');
                $table->float('latitude')->nullable()->comment('Latitude');
                $table->float('longitude')->nullable()->comment('Longitude');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->id();
                $table->string('name', 20)->nullable()->comment('currency name');
                $table->string('code', 3)->nullable()->comment('currency code');
                $table->string('symbol', 5)->nullable()->comment('currency symbol');
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('stores')) {
            Schema::create('stores', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_enabled')->default(true)->comment('True when enabled and listed');
                $table->boolean('is_public')->default(true)->comment('Only public will be listed');
                $table->unsignedBigInteger('parent_id')->nullable()->index();
                $table->foreign('parent_id')
                    ->references('id')
                    ->on($table->getTable())
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
                $table->string('code', 255)->unique()->nullable();
                $table->string('url', 255)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('core_configs')) {
            Schema::create('core_configs', function (Blueprint $table) {
                $table->id();
                //            $table->unsignedBigInteger('parent_id')->nullable()->index();
                //            $table->foreign('parent_id')->references('id')->on($table->getTable())->cascadeOnUpdate()->cascadeOnDelete();
                $table->unsignedBigInteger('store_id')->nullable()->index();
                $table->foreign('store_id')->references('id')->on('stores')->cascadeOnUpdate()->cascadeOnDelete();
                $table->string('path', 255)
                    ->nullable()
                    ->comment('Dor or slash based key path line catalog.product.image.width');
                $table->mediumText('value')->nullable();
                $table->string('form_input', 100)->nullable()->comment('form element');
                $table->string('description', 255)->nullable()->comment('Short description for this setting');
                $table->timestamps();
            });
        }

        /**
         * All attributes exist for all models like
         * name, email, description, price, shipping_method, size, ...
         */
        if (!Schema::hasTable('model_attributes')) {
            Schema::create('model_attributes', function (Blueprint $table) {
                $table->id();
                $table->string('code', 255)->nullable()->comment('Not unique. Use description for same names.');
                $table->string('description', 255)->nullable();
                $table->timestamps();
            });
        }

        /**
         * Attributes assigned to specific Model namespace.
         */
        if (!Schema::hasTable('model_attribute_assignments')) {
            Schema::create('model_attribute_assignments', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_read_only')->default(false)->comment('True when disabled element in forms');
                $table->string('model', 255)->nullable()->comment('Model incl namespace');
                $table->unsignedBigInteger('model_attribute_id')->unsigned();
                $table->foreign('model_attribute_id')
                    ->references('id')
                    ->on('model_attributes')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->string('attribute_type', 50)
                    ->nullable()
                    ->comment('Needs to write. If present also take for read.');
                $table->string('attribute_input', 100)
                    ->nullable()
                    ->comment('The form element. Optional with module (module_x::my_element)');
                $table->string('description', 255)
                    ->nullable()
                    ->comment('If given overwrite model_attributes.description');
                $table->string('default_value', 255)->nullable()->comment('Default value if model is created');
                $table->timestamps();
            });
        }

        /**
         * Type assignments to specific model instances.
         */
        if (!Schema::hasTable(ModelAttributeAssignment::ATTRIBUTE_ASSIGNMENT_TYPE_TABLE_PREFIX.ModelAttributeAssignment::ATTRIBUTE_TYPE_MAP['string']['table_suffix'])) {
            $this->createAssignmentTable('string', function (Blueprint $table) {
                $table->string('value', 255)->nullable();
            });
            $this->createAssignmentTable('text', function (Blueprint $table) {
                $table->mediumText('value')->nullable();
            });
            $this->createAssignmentTable('integer', function (Blueprint $table) {
                $table->integer('value')->default(0);
            });
            $this->createAssignmentTable('double', function (Blueprint $table) {
                $table->double('value')->default(0);
            });
        }

        if (!Schema::hasTable('media_items')) {
            Schema::create('media_items', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_enabled')->default(true)->comment('True when enabled and listed');
                $table->boolean('is_public')->default(true)->comment('Only public will be listed');
                $table->unsignedBigInteger('parent_id')->nullable()->index();
                $table->foreign('parent_id')
                    ->references('id')
                    ->on($table->getTable())
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->unsignedBigInteger('store_id')->nullable()->index();
                $table->foreign('store_id')->references('id')->on('stores')->cascadeOnUpdate()->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
                $table->string('media_type', 255)
                    ->nullable()
                    ->comment('Type like IMAGE,VIDEO,SOUND,DOCUMENT,ARCHIVE,FILE');
                $table->string('object_type', 255)->nullable()->comment('Object Type like PRODUCT_IMAGE');
                //            $table->string('content_code', 255)->nullable()->comment('like MAKER for first product images');
                $table->unsignedInteger('position')->default(100)->comment('Position in object');
                $table->string('name', 255)->nullable()->comment('Label to describe this image in image selections');
                $table->string('file_name', 255)
                    ->nullable()
                    ->comment('The filename from original, not the intern one.');
                $table->string('relative_path', 255)->nullable();
                $table->unsignedBigInteger('file_size')->default(0);
                $table->string('extern_url', 255)->nullable()->comment('Used if relative_path is empty only');
                $table->string('description', 255)->nullable();
                $table->string('meta_description', 255)->nullable();
                $table->timestamps();
            });
        }

        // relation table: alphabetical order, singular, underline seperated
        if (!Schema::hasTable('media_item_user')) {
            Schema::create('media_item_user', function (Blueprint $table) {
                $table->unsignedBigInteger('media_item_id')->unsigned();
                $table->unsignedBigInteger('user_id')->unsigned();
                $table->string('content_code', 255)->nullable()->comment('like MAKER for first user images');

                $table->unique(['media_item_id', 'user_id']);
                $table->foreign('media_item_id')
                    ->references('id')
                    ->on('media_items')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }

        if (!Schema::hasTable('tokens')) {
            Schema::create('tokens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
                $table->string('purpose', 255)->nullable();
                $table->string('token', 255)->unique()->nullable();
                $table->text('values')->nullable()->comment('json data value depends on purpose');
                $table->string('description', 255)->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cms_contents')) {
            Schema::create('cms_contents', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_enabled')->default(true)->comment('True when enabled and routed');
                $table->unsignedBigInteger('parent_id')->nullable()->index();
                $table->foreign('parent_id')
                    ->references('id')
                    ->on($table->getTable())
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->unsignedBigInteger('store_id')->nullable()->index();
                $table->foreign('store_id')->references('id')->on('stores')->cascadeOnUpdate()->cascadeOnDelete();
                $table->string('code', 255)->nullable();
                $table->string('format', 50)->nullable()->comment('like plain, html, markdown');
                $table->string('locale', 6)->nullable()->default('en')->comment('ISO-639-... code');
                $table->string('description', 255)->nullable();
                $table->string('acl_resources', 255)->nullable()->comment('Comma seperated acl resources');
                $table->mediumText('content')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cms_pages')) {
            Schema::create('cms_pages', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_enabled')->default(true)->comment('True when enabled and routed');
                $table->unsignedBigInteger('parent_id')->nullable()->index();
                $table->foreign('parent_id')
                    ->references('id')
                    ->on($table->getTable())
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->unsignedBigInteger('store_id')->nullable()->index();
                $table->foreign('store_id')->references('id')->on('stores')->cascadeOnUpdate()->cascadeOnDelete();
                $table->string('code', 255)->nullable()->comment('Should be unique, but same for different locales');
                $table->string('format', 50)->nullable()->comment('like plain, html, markdown');
                $table->string('name', 255)->nullable()->comment('Navigation Label');
                $table->string('title', 255)->nullable()->comment('Page title');
                $table->string('locale', 6)->nullable()->default('en')->comment('ISO-639-... code');
                $table->string('description', 255)->nullable()->comment('Page description');
                $table->mediumText('acl_resources')->nullable()->comment('json list of acl resources');
                $table->mediumText('meta_data')->nullable()->comment('json of meta data');
                $table->string('web_uri', 255)->nullable();
                $table->mediumText('content')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('view_templates')) {
            Schema::create('view_templates', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_enabled')
                    ->default(true)
                    ->comment('disabled templates will fallback to file started in views. (e.g. notifications.emails.welcome)');
                $table->string('code', 255)
                    ->nullable()
                    ->unique()
                    ->comment('unique dotted schema to ident this template ... also used to file fallback started in views. (e.g. notifications.emails.welcome)');
                $table->mediumText('content')->nullable()->comment('template content');
                $table->string('view_file', 255)->nullable()->comment('dot seperated view path to file');
                $table->string('parameter_variant', 255)
                    ->nullable()
                    ->comment('code of parameter variant like "'.ViewTemplate::PARAMETER_VARIANT_DEFAULT.'"');
                $table->string('description', 255)->nullable()->comment('short info');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notification_templates')) {
            Schema::create('notification_templates', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_enabled')->default(true)->comment('');
                $table->string('code', 255)
                    ->nullable()
                    ->unique()
                    ->comment('unique dotted schema to ident this template');
                $table->unsignedBigInteger('view_template_id')->nullable()->comment('the template');
                $table->foreign('view_template_id')
                    ->references('id')
                    ->on('view_templates')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->string('subject', 255)->nullable()->comment('email subject');
                $table->string('description', 255)->nullable()->comment('short info');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notification_concerns')) {
            Schema::create('notification_concerns', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_enabled')->default(true)->comment('');
                $table->unsignedBigInteger('store_id')->nullable()->comment('Store');
                $table->foreign('store_id')->references('id')->on('stores')->cascadeOnUpdate()->cascadeOnDelete();
                $table->unsignedBigInteger('notification_template_id')->nullable()->comment('the template');
                $table->foreign('notification_template_id')
                    ->references('id')
                    ->on('notification_templates')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->string('reason_code', 255)->nullable()->comment('Reason for this email content');
                $table->string('sender', 255)->nullable()->comment('email address of sender');
                $table->unsignedInteger('priority')->default(1000)->comment('priority (default 1000)');
                $table->string('description', 255)->nullable()->comment('short info');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notification_events')) {
            Schema::create('notification_events', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_enabled')
                    ->default(true)
                    ->comment('disabled templates will fallback to file started in views. (e.g. notifications.emails.welcome)');
                $table->string('event_trigger', 30)->nullable()->comment('auto,manually,...');
                $table->string('name', 255)->comment('Event Name');
                $table->string('event_code', 255)->nullable()->comment('dotted code to decide what should happens');
                $table->string('preferred_channel', 255)
                    ->nullable()
                    ->comment('Preferred channel to sending notifications (if user has no preferences)');
                $table->string('force_channel', 255)
                    ->nullable()
                    ->comment('Force this channel to sending notifications');
                $table->unsignedBigInteger('notification_concern_id')
                    ->nullable()
                    ->comment('notification concern or null to use content');
                $table->foreign('notification_concern_id')
                    ->references('id')
                    ->on('notification_templates')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
                $table->mediumText('content')->nullable()->comment('content if notification concern is no set');
                $table->mediumText('content_data')->nullable()->comment('extra data json');
                $table->string('description', 255)->nullable()->comment('short info about this notification');
                $table->timestamp('schedule_at')->nullable()->comment('null = immediately');
                $table->integer('repeat_count')->default(0);
                $table->string('repeat_code', 100)->nullable()->comment('like days, hours, ...');
                $table->string('repeat_value', 100)->nullable()->comment('depends on repeat_code');
                $table->timestamp('expires_at')->nullable()->comment('becomes relevance when repeat was set');
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('notification_event_user')) {
            Schema::create('notification_event_user', function (Blueprint $table) {
                $table->unsignedBigInteger('notification_event_id')->unsigned();
                $table->unsignedBigInteger('user_id')->unsigned();

                $table->unique(['notification_event_id', 'user_id'], 'userNotify_uniIndex');
                $table->foreign('notification_event_id', 'userNotify_notifyId')
                    ->references('id')
                    ->on('notification_events')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->foreign('user_id', 'userNotify_userId')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('acl_resource_notification_event')) {
            Schema::create('acl_resource_notification_event', function (Blueprint $table) {
                $table->unsignedBigInteger('acl_resource_id')->unsigned();
                $table->unsignedBigInteger('notification_event_id')->unsigned();

                $table->unique(['acl_resource_id', 'notification_event_id'], 'aclNotify_uniIndex');
                $table->foreign('acl_resource_id', 'aclNotify_aclId')
                    ->references('id')
                    ->on('acl_resources')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $table->foreign('notification_event_id', 'aclNotify_notifyId')
                    ->references('id')
                    ->on('notification_events')
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
    public function down(): void
    {
        Schema::dropIfExists('acl_resource_notification_event');
        Schema::dropIfExists('notification_event_user');
        Schema::dropIfExists('notification_events');
        Schema::dropIfExists('notification_concerns');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('view_templates');
        Schema::dropIfExists('cms_pages');
        Schema::dropIfExists('cms_contents');
        Schema::dropIfExists('tokens');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('media_item_user');
        Schema::dropIfExists('media_items');
        foreach (ModelAttributeAssignment::ATTRIBUTE_TYPE_MAP as $v) {
            Schema::dropIfExists(ModelAttributeAssignment::ATTRIBUTE_ASSIGNMENT_TYPE_TABLE_PREFIX.$v['table_suffix']);
        }
        Schema::dropIfExists('model_attribute_assignments');
        Schema::dropIfExists('model_attributes');
        Schema::dropIfExists('core_configs');
        Schema::dropIfExists('stores');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('countries');
    }

    protected function createAssignmentTable($typeName, callable $tableDeclaration): void
    {
        Schema::create(ModelAttributeAssignment::ATTRIBUTE_ASSIGNMENT_TYPE_TABLE_PREFIX.ModelAttributeAssignment::ATTRIBUTE_TYPE_MAP[$typeName]['table_suffix'],
            function (Blueprint $table) use ($typeName, $tableDeclaration) {
                $table->id();
                $table->unsignedBigInteger('model_id')->comment('product id, user id, ...');
                $table->unsignedBigInteger('model_attribute_assignment_id')->unsigned();
                $table->foreign('model_attribute_assignment_id',
                    'modelAttrA'.ModelAttributeAssignment::ATTRIBUTE_TYPE_MAP[$typeName]['table_suffix'].'ModelAttrAIdForeign')
                    ->references('id')
                    ->on('model_attribute_assignments')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
                $tableDeclaration($table);
                $table->timestamps();
            });

    }

};
