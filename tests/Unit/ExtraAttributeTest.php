<?php
declare(strict_types = 1);

namespace Modules\WebsiteBase\tests\Unit;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\SystemBase\tests\Unit\Isolated\IsolatedTestCase;
use Modules\WebsiteBase\app\Events\ModelWithAttributesDeleted;
use Modules\WebsiteBase\app\Events\ModelWithAttributesLoaded;
use Modules\WebsiteBase\app\Events\ModelWithAttributesSaved;
use Modules\WebsiteBase\app\Listeners\ModelWithAttributesDeleted as ModelWithAttributesDeletedListener;
use Modules\WebsiteBase\app\Listeners\ModelWithAttributesLoaded as ModelWithAttributesLoadedListener;
use Modules\WebsiteBase\app\Listeners\ModelWithAttributesSaved as ModelWithAttributesSavedListener;
use Modules\WebsiteBase\app\Models\Base\TraitAttributeAssignment;
use Modules\WebsiteBase\app\Models\ModelAttributeAssignment;
use Modules\WebsiteBase\app\Services\WebsiteService;

/**
 * Extra attributes (TraitAttributeAssignment) with an in-memory sqlite database and the real listeners,
 * without booting the application.
 *
 * The cache is an array store, which supports tags like redis: tagged values are stored under a
 * tag-namespaced key, so forgetting them without the tags does nothing (the bug behind stale user settings).
 */
class ExtraAttributeTest extends IsolatedTestCase
{
    /**
     * @var Capsule
     */
    protected Capsule $capsule;

    /**
     * attribute code => [type, default value]
     */
    protected const array ATTRIBUTES = [
        'nickname' => [ModelAttributeAssignment::ATTRIBUTE_TYPE_STRING, 'nobody'],
        'bio'      => [ModelAttributeAssignment::ATTRIBUTE_TYPE_TEXT, null],
        'age'      => [ModelAttributeAssignment::ATTRIBUTE_TYPE_INTEGER, '18'],
        'rating'   => [ModelAttributeAssignment::ATTRIBUTE_TYPE_DOUBLE, '2.5'],
        'tags'     => [ModelAttributeAssignment::ATTRIBUTE_TYPE_ARRAY, null],
        'settings' => [ModelAttributeAssignment::ATTRIBUTE_TYPE_JSON, null],
        'address'  => [ModelAttributeAssignment::ATTRIBUTE_TYPE_OBJECT, null],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->setConfig('auth.providers.users.database.model', ExtraAttributeTestUser::class);
        $this->setConfig('system-base.cache.db.signature.ttl', 600);
        $this->setConfig('website-base.cache', [
            'extra_attributes'       => ['prefix' => 'ModelExtraAttributes::', 'ttl' => 600],
            'extra_attribute_entity' => ['prefix' => 'ModelExtraAttributesEntity::', 'ttl' => 600],
        ]);

        $this->capsule = new Capsule($this->container);
        $this->capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $this->capsule->setEventDispatcher(new Dispatcher($this->container));
        $this->capsule->bootEloquent();
        $this->container->instance('db', $this->capsule->getDatabaseManager());

        $events = $this->capsule->getEventDispatcher();
        $events->listen(ModelWithAttributesLoaded::class, ModelWithAttributesLoadedListener::class);
        $events->listen(ModelWithAttributesSaved::class, ModelWithAttributesSavedListener::class);
        $events->listen(ModelWithAttributesDeleted::class, ModelWithAttributesDeletedListener::class);

        $this->createSchema();
        $this->createAttributes(ExtraAttributeTestUser::class, self::ATTRIBUTES);
        $this->createAttributes(ExtraAttributeTestProduct::class, ['nickname' => [ModelAttributeAssignment::ATTRIBUTE_TYPE_STRING, 'product']]);
    }

    protected function tearDown(): void
    {
        Model::unsetEventDispatcher();
        Model::unsetConnectionResolver();
        Model::clearBootedModels();

        parent::tearDown();
    }

    /**
     * Tables like in the WebsiteBase migrations (without foreign keys).
     */
    protected function createSchema(): void
    {
        $schema = $this->capsule->getConnection()
            ->getSchemaBuilder();

        $schema->create('test_users', function (Blueprint $table) {
            $table->id();
            $table->string('name')
                ->nullable();
            $table->timestamps();
        });
        $schema->create('test_products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
        $schema->create('model_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('module')
                ->nullable();
            $table->string('code')
                ->nullable();
            $table->string('description')
                ->nullable();
            $table->timestamps();
        });
        $schema->create('model_attribute_assignments', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_read_only')
                ->default(false);
            $table->string('model')
                ->nullable();
            $table->unsignedBigInteger('model_attribute_id');
            $table->string('attribute_type', 50)
                ->nullable();
            $table->string('attribute_input', 100)
                ->nullable();
            $table->integer('form_position')
                ->default(1000);
            $table->string('form_css')
                ->nullable();
            $table->string('description')
                ->nullable();
            $table->string('default_value')
                ->nullable();
            $table->timestamps();
        });

        $valueColumns = [
            'strings'  => fn(Blueprint $table) => $table->string('value')
                ->nullable(),
            'texts'    => fn(Blueprint $table) => $table->mediumText('value')
                ->nullable(),
            'integers' => fn(Blueprint $table) => $table->integer('value')
                ->default(0),
            'doubles'  => fn(Blueprint $table) => $table->double('value')
                ->default(0),
        ];
        foreach ($valueColumns as $suffix => $valueColumn) {
            $schema->create(ModelAttributeAssignment::ATTRIBUTE_ASSIGNMENT_TYPE_TABLE_PREFIX.$suffix,
                function (Blueprint $table) use ($valueColumn) {
                    $table->id();
                    $table->unsignedBigInteger('model_id');
                    $table->unsignedBigInteger('model_attribute_assignment_id');
                    $valueColumn($table);
                    $table->timestamps();
                });
        }
    }

    /**
     * @param  string  $modelClass
     * @param  array   $attributes  code => [type, default value]
     */
    protected function createAttributes(string $modelClass, array $attributes): void
    {
        $position = 0;
        foreach ($attributes as $code => [$type, $default]) {
            $attributeId = DB::table('model_attributes')
                ->insertGetId(['code' => $code]);
            DB::table('model_attribute_assignments')
                ->insert([
                    'model'              => $modelClass,
                    'model_attribute_id' => $attributeId,
                    'attribute_type'     => $type,
                    'default_value'      => $default,
                    'form_position'      => ++$position,
                ]);
        }
    }

    /**
     * Rows of a value table for a model id.
     */
    protected function valueRows(string $suffix, int $modelId): array
    {
        return DB::table(ModelAttributeAssignment::ATTRIBUTE_ASSIGNMENT_TYPE_TABLE_PREFIX.$suffix)
            ->where('model_id', $modelId)
            ->get()
            ->all();
    }

    protected function createUser(array $extraAttributes = []): ExtraAttributeTestUser
    {
        $user = new ExtraAttributeTestUser(['name' => 'test']);
        $user->setExtraAttributes($extraAttributes);
        $user->save();

        return $user;
    }

    public function test_new_instance_has_typed_defaults(): void
    {
        $user = new ExtraAttributeTestUser();

        $this->assertSame('nobody', $user->getExtraAttribute('nickname'));
        $this->assertNull($user->getExtraAttribute('bio'));
        $this->assertSame(18, $user->getExtraAttribute('age'));
        $this->assertSame(2.5, $user->getExtraAttribute('rating'));
        $this->assertNull($user->getExtraAttribute('tags'));
        $this->assertSame('fallback', $user->getExtraAttribute('unknown', 'fallback'));
        $this->assertSame(array_keys(self::ATTRIBUTES), array_keys($user->getExtraAttributes()));
    }

    public function test_attributes_of_other_models_are_not_mixed_in(): void
    {
        $product = new ExtraAttributeTestProduct();

        $this->assertSame(['nickname' => 'product'], $product->getExtraAttributes());
    }

    public function test_setExtraAttribute_casts_by_type(): void
    {
        $user = new ExtraAttributeTestUser();

        $user->setExtraAttribute('age', '42');
        $user->setExtraAttribute('rating', '4.75');
        $user->setExtraAttribute('tags', '["a","b"]');
        $user->setExtraAttribute('settings', '{"theme":"dark"}');
        $user->setExtraAttribute('address', '{"city":"Berlin"}');
        $user->setExtraAttribute('unknown', 'kept as is');

        $this->assertSame(42, $user->getExtraAttribute('age'));
        $this->assertSame(4.75, $user->getExtraAttribute('rating'));
        $this->assertSame(['a', 'b'], $user->getExtraAttribute('tags'));
        $this->assertSame(['theme' => 'dark'], $user->getExtraAttribute('settings'));
        $this->assertEquals((object) ['city' => 'Berlin'], $user->getExtraAttribute('address'));
        $this->assertSame('kept as is', $user->getExtraAttribute('unknown'));
    }

    public function test_save_writes_all_types_and_find_loads_them_typed(): void
    {
        $user = $this->createUser([
            'nickname' => 'andi',
            'bio'      => 'Long text',
            'age'      => 42,
            'rating'   => 4.5,
            'tags'     => ['a', 'b'],
            'settings' => ['theme' => 'dark'],
            'address'  => (object) ['city' => 'Berlin'],
        ]);

        $this->assertCount(1, $this->valueRows('strings', $user->id));
        $this->assertCount(4, $this->valueRows('texts', $user->id));
        $this->assertCount(1, $this->valueRows('integers', $user->id));
        $this->assertCount(1, $this->valueRows('doubles', $user->id));

        $loaded = ExtraAttributeTestUser::find($user->id);
        $this->assertSame('andi', $loaded->getExtraAttribute('nickname'));
        $this->assertSame('Long text', $loaded->getExtraAttribute('bio'));
        $this->assertSame(42, $loaded->getExtraAttribute('age'));
        $this->assertSame(4.5, $loaded->getExtraAttribute('rating'));
        $this->assertSame(['a', 'b'], $loaded->getExtraAttribute('tags'));
        $this->assertSame(['theme' => 'dark'], $loaded->getExtraAttribute('settings'));
        $this->assertEquals((object) ['city' => 'Berlin'], $loaded->getExtraAttribute('address'));
    }

    public function test_extra_attributes_are_part_of_toArray(): void
    {
        $user = $this->createUser(['nickname' => 'andi']);

        $array = ExtraAttributeTestUser::find($user->id)
            ->toArray();

        $this->assertSame('andi', $array['extra_attributes']['nickname']);
        $this->assertSame(18, $array['extra_attributes']['age']);
    }

    public function test_empty_array_is_saved_as_null(): void
    {
        $user = $this->createUser(['tags' => []]);

        $this->assertTrue($user->saveModelAttributeTypeValue('tags', []));
        $this->assertNull($user->loadModelAttributeTypeValue('tags', 'default'));
    }

    public function test_saveModelAttributeTypeValue_updates_instead_of_duplicating(): void
    {
        $user = $this->createUser(['nickname' => 'first']);

        $this->assertTrue($user->saveModelAttributeTypeValue('nickname', 'second'));
        $this->assertTrue($user->saveModelAttributeTypeValue('nickname', 'third'));

        $rows = $this->valueRows('strings', $user->id);
        $this->assertCount(1, $rows);
        $this->assertSame('third', $rows[0]->value);
        $this->assertSame('third', $user->loadModelAttributeTypeValue('nickname'));
    }

    public function test_unknown_attribute_is_not_saved(): void
    {
        $user = $this->createUser();

        $this->assertFalse($user->saveModelAttributeTypeValue('unknown', 'x'));
        $this->assertNull($user->loadModelAttributeTypeValues('unknown'));
        $this->assertSame('default', $user->loadModelAttributeTypeValue('unknown', 'default'));
    }

    public function test_loaded_values_are_cached_per_entity(): void
    {
        $user = $this->createUser(['nickname' => 'cached']);
        ExtraAttributeTestUser::find($user->id); // fills the entity cache

        // changed in the database only (like another process would do)
        DB::table(ModelAttributeAssignment::ATTRIBUTE_ASSIGNMENT_TYPE_TABLE_PREFIX.'strings')
            ->where('model_id', $user->id)
            ->update(['value' => 'changed']);

        $this->assertSame('cached',
            ExtraAttributeTestUser::find($user->id)
                ->getExtraAttribute('nickname'));
    }

    /**
     * Regression: the entity cache is written with the tag WebsiteService::cacheTagExtraAttributes.
     * Clearing it without the tag kept the old values until the ttl expired (desktop layout switch on PROD).
     */
    public function test_clearCache_after_single_save_returns_the_new_value(): void
    {
        $user = $this->createUser(['nickname' => 'old']);
        $this->assertSame('old',
            ExtraAttributeTestUser::find($user->id)
                ->getExtraAttribute('nickname'));

        // the way UserDesktopSettings saves a single setting
        $user->saveModelAttributeTypeValue('nickname', 'new');
        $this->assertTrue($user->clearCacheParametersExtraAttributeEntity());

        $this->assertSame('new',
            ExtraAttributeTestUser::find($user->id)
                ->getExtraAttribute('nickname'));
    }

    public function test_save_of_the_model_clears_the_entity_cache(): void
    {
        $user = $this->createUser(['nickname' => 'old']);
        $loaded = ExtraAttributeTestUser::find($user->id);

        $loaded->setExtraAttribute('nickname', 'new');
        $loaded->save();

        $this->assertSame('new',
            ExtraAttributeTestUser::find($user->id)
                ->getExtraAttribute('nickname'));
    }

    public function test_flushing_the_tag_clears_all_extra_attribute_caches(): void
    {
        $user = $this->createUser(['nickname' => 'old']);
        ExtraAttributeTestUser::find($user->id);
        DB::table(ModelAttributeAssignment::ATTRIBUTE_ASSIGNMENT_TYPE_TABLE_PREFIX.'strings')
            ->where('model_id', $user->id)
            ->update(['value' => 'new']);

        Cache::tags([WebsiteService::cacheTagExtraAttributes])
            ->flush();

        $this->assertSame('new',
            ExtraAttributeTestUser::find($user->id)
                ->getExtraAttribute('nickname'));
    }

    public function test_cache_keys_are_separated_by_model_and_id(): void
    {
        $user = new ExtraAttributeTestUser();
        $user->id = 7;
        $product = new ExtraAttributeTestProduct();
        $product->id = 7;

        $userKey = $user->getCacheParametersExtraAttributeEntity()['key'];

        $this->assertSame('ModelExtraAttributesEntity::'.ExtraAttributeTestUser::class.'_7', $userKey);
        $this->assertNotSame($userKey, $product->getCacheParametersExtraAttributeEntity()['key']);
        $this->assertSame(600, $user->getCacheParametersExtraAttributeEntity()['ttl']);
    }

    public function test_ATTRIBUTE_MODEL_IDENT_shares_the_attributes(): void
    {
        $customer = new ExtraAttributeTestCustomer();

        $this->assertSame(ExtraAttributeTestUser::class, $customer->getAttributeModelIdent());
        $this->assertSame(array_keys(self::ATTRIBUTES), array_keys($customer->getExtraAttributes()));
    }

    public function test_delete_removes_the_values(): void
    {
        $user = $this->createUser(['nickname' => 'andi', 'age' => 42, 'tags' => ['a']]);
        $id = $user->id;

        $user->delete();

        foreach (['strings', 'texts', 'integers', 'doubles'] as $suffix) {
            $this->assertSame([], $this->valueRows($suffix, $id), $suffix);
        }
    }

    public function test_deleteModelAttributeTypeValue_removes_one_value(): void
    {
        $user = $this->createUser(['nickname' => 'andi', 'age' => 42]);

        $this->assertTrue($user->deleteModelAttributeTypeValue('nickname'));
        $this->assertFalse($user->deleteModelAttributeTypeValue('nickname'));
        $this->assertFalse($user->deleteModelAttributeTypeValue('unknown'));

        $this->assertSame([], $this->valueRows('strings', $user->id));
        $this->assertCount(1, $this->valueRows('integers', $user->id));
    }

    public function test_getAttributeTypeTableName(): void
    {
        $prefix = ModelAttributeAssignment::ATTRIBUTE_ASSIGNMENT_TYPE_TABLE_PREFIX;

        $this->assertSame($prefix.'strings', ExtraAttributeTestUser::getAttributeTypeTableName(null));
        $this->assertSame($prefix.'texts', ExtraAttributeTestUser::getAttributeTypeTableName(ModelAttributeAssignment::ATTRIBUTE_TYPE_JSON));
        $this->assertSame($prefix.'integers', ExtraAttributeTestUser::getAttributeTypeTableName(ModelAttributeAssignment::ATTRIBUTE_TYPE_INTEGER));
        $this->assertSame($prefix.'doubles', ExtraAttributeTestUser::getAttributeTypeTableName(ModelAttributeAssignment::ATTRIBUTE_TYPE_DOUBLE));
    }
}

/**
 * Model with extra attributes for ExtraAttributeTest.
 */
class ExtraAttributeTestUser extends Model
{
    use TraitAttributeAssignment;

    protected $table = 'test_users';

    protected $guarded = [];
}

/**
 * Second model, its attributes must not be mixed with ExtraAttributeTestUser.
 */
class ExtraAttributeTestProduct extends Model
{
    use TraitAttributeAssignment;

    protected $table = 'test_products';

    protected $guarded = [];
}

/**
 * Uses the attributes of ExtraAttributeTestUser by ATTRIBUTE_MODEL_IDENT.
 */
class ExtraAttributeTestCustomer extends Model
{
    use TraitAttributeAssignment;

    const string ATTRIBUTE_MODEL_IDENT = ExtraAttributeTestUser::class;

    protected $table = 'test_users';

    protected $guarded = [];
}
