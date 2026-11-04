<?php

namespace Modules\WebsiteBase\tests\Feature;

use Modules\SystemBase\tests\TestCase;

class PwaTest extends TestCase
{
    /**
     * @return void
     */
    public function test_manifest_is_served()
    {
        config(['website-base.pwa.name' => 'Test App', 'website-base.pwa.short_name' => 'Test']);

        $response = $this->get('/manifest.webmanifest');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/manifest+json');
        $response->assertJson([
            'id'         => '/',
            'name'       => 'Test App',
            'short_name' => 'Test',
            'scope'      => '/',
            'display'    => 'standalone',
        ]);
        // The session must not be started for PWA files
        $response->assertCookieMissing(config('session.cookie'));
    }

    /**
     * @return void
     */
    public function test_service_worker_is_served_without_placeholders()
    {
        $response = $this->get('/sw.js');

        $response->assertStatus(200);
        $this->assertStringStartsWith('application/javascript', $response->headers->get('Content-Type'));
        $this->assertStringNotContainsString('__VERSION__', $response->getContent());
        $this->assertStringNotContainsString('__OFFLINE_URL__', $response->getContent());
        $this->assertStringNotContainsString('__TIMEOUT_MS__', $response->getContent());
    }

    /**
     * Same content, same version: otherwise every request would install a new service worker.
     *
     * @return void
     */
    public function test_service_worker_version_is_stable()
    {
        $this->assertSame($this->get('/sw.js')->getContent(), $this->get('/sw.js')->getContent());
    }

    /**
     * @return void
     */
    public function test_offline_page_is_self_contained()
    {
        $response = $this->get('/offline');

        $response->assertStatus(200);
        $html = $response->getContent();
        // Shown from the cache when the server is gone: nothing may be loaded from outside
        $this->assertDoesNotMatchRegularExpression('/<link[^>]+rel="stylesheet"/i', $html);
        $this->assertDoesNotMatchRegularExpression('/<script[^>]+src=/i', $html);
        $this->assertDoesNotMatchRegularExpression('/<img[^>]+src=/i', $html);
    }
}
