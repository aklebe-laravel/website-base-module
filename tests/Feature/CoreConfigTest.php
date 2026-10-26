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
                'params' => ['site.auth.enabled', 0],
                'result' => 1,
            ],
            [
                'params' => ['notification.channels.email.enabled', null],
                'result' => 0,
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
