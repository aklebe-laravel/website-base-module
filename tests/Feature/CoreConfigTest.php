<?php

namespace Modules\WebsiteBase\tests\Feature;

use Modules\SystemBase\tests\TestCase;

class CoreConfigTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');
        $response->assertStatus(config('website-base.module_website_public', false) ? 200 : 302);
    }

    /**
     * @return void
     */
    public function test_config_tree()
    {
        $testList = [
            [
                'params' => ['catalog.product.image.width', 0],
                'result' => 1200,
            ],
            [
                'params' => ['catalog.product.image.height', 0],
                'result' => 1200,
            ],
            [
                'params' => ['catalog.product.image.width', 0, 1],
                'result' => 1200,
            ],
            [
                'params' => ['catalog.product.image.height', 0, 1],
                'result' => 1200,
            ],
            [
                'params' => ['catalog.product.image.width', 0, 2],
                'result' => 800,
            ],
            [
                'params' => ['catalog.product.image.height', 0, 2],
                'result' => 600,
            ],
            [
                'params' => ['catalog.product.image.width', 0, null],
                'result' => 800,
            ],
            [
                'params' => ['catalog.product.image.height', 0, null],
                'result' => 600,
            ],
        ];

        $this->runList($testList, function ($name, $data) {
            $result = call_user_func_array([app('website_base_config'), 'getValue'], $data['params']);
            if ($result != $data['result']) {
                $this->fail(sprintf("Result for object %s: %s, but expected: %s. Params: %s", $name, $result,
                    $data['result'], json_encode($data['params'])));
            }
        });

        $this->assertTrue(true);
    }
}
