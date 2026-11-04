<?php

declare(strict_types = 1);

namespace Modules\WebsiteBase\app\Services;

use Illuminate\Support\Facades\Route;

/**
 * Progressive web app: manifest, service worker and offline page.
 *
 * Everything project specific comes from the config (website-base.pwa) or the active theme:
 * - icons: {theme}/resources/assets/images/pwa/*.png, searched in the active theme and then in its parents
 * - colors: theme.json "tokens" pwa.theme_color / pwa.background_color
 *
 * See README "Progressive web app".
 */
class PwaService
{
    /**
     * Used when neither the config nor the theme sets a color (dark surface of the Mercy themes).
     */
    const string DEFAULT_COLOR = '#020617';

    /**
     * Folder of the icons inside the public theme assets.
     */
    const string ICON_DIR = 'images/pwa';

    /**
     * Icons of the manifest: file => [sizes, purpose]. Missing files are skipped.
     */
    const array MANIFEST_ICONS = [
        'icon-192.png'          => ['192x192', 'any'],
        'icon-512.png'          => ['512x512', 'any'],
        'icon-512-maskable.png' => ['512x512', 'maskable'],
    ];

    /**
     * Icon for the iOS home screen (180x180, no transparency).
     */
    const string APPLE_TOUCH_ICON = 'apple-touch-icon.png';

    /**
     * Health route of Laravel (bootstrap/app.php "health"), polled by the offline page.
     */
    const string HEALTH_URL = '/up';

    /**
     * Whether the layout links the manifest and registers the service worker.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool) config('website-base.pwa.enabled', false);
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return (string) (config('website-base.pwa.name') ?: config('app.name', 'Laravel'));
    }

    /**
     * Name below the icon on the home screen, should be short (about 12 chars).
     *
     * @return string
     */
    public function shortName(): string
    {
        return (string) (config('website-base.pwa.short_name') ?: $this->name());
    }

    /**
     * Color of the browser/system bar.
     *
     * @return string
     */
    public function themeColor(): string
    {
        return $this->color('theme_color');
    }

    /**
     * Color of the splash screen while the app starts.
     *
     * @return string
     */
    public function backgroundColor(): string
    {
        return $this->color('background_color');
    }

    /**
     * Page the installed app opens with. Same as the start page after a login (website-base.login_redirect).
     *
     * @return string
     */
    public function startUrl(): string
    {
        $target = (string) config('website-base.login_redirect', '/');

        if ($target !== '' && Route::has($target)) {
            return route($target, [], false);
        }

        return '/'.ltrim($target, '/');
    }

    /**
     * Relative url of an icon of the active theme or one of its parents, null if no theme has it.
     *
     * @param  string  $file  like 'icon-192.png'
     *
     * @return string|null
     */
    public function iconUrl(string $file): ?string
    {
        foreach ($this->themeAssetBases() as $base) {
            $relative = $base.'/'.self::ICON_DIR.'/'.$file;
            if (is_file(public_path($relative))) {
                // Relative on purpose: an absolute url from APP_URL breaks when the app runs under another domain
                return '/'.$relative;
            }
        }

        return null;
    }

    /**
     * Content of manifest.webmanifest.
     *
     * @return array
     */
    public function manifest(): array
    {
        $icons = [];
        foreach (self::MANIFEST_ICONS as $file => [$sizes, $purpose]) {
            if ($url = $this->iconUrl($file)) {
                $icons[] = [
                    'src'     => $url,
                    'sizes'   => $sizes,
                    'type'    => 'image/png',
                    'purpose' => $purpose,
                ];
            }
        }

        return [
            // Fixed id: changing start_url later must not turn the installed app into a "new" app
            'id'               => '/',
            'name'             => $this->name(),
            'short_name'       => $this->shortName(),
            'start_url'        => $this->startUrl(),
            'scope'            => '/',
            'display'          => 'standalone',
            'background_color' => $this->backgroundColor(),
            'theme_color'      => $this->themeColor(),
            'icons'            => $icons,
        ];
    }

    /**
     * The service worker script, built from resources/pwa/sw.js.
     *
     * The cache version is a hash of the script and the offline page: every change of one of them
     * makes the browser install the new service worker and drop the old cache. Nothing to count by hand.
     *
     * @return string
     */
    public function serviceWorkerScript(): string
    {
        $template = (string) file_get_contents(module_path('WebsiteBase', 'resources/pwa/sw.js'));

        $script = strtr($template, [
            '__OFFLINE_URL__' => json_encode(route('website-base.pwa.offline', [], false), JSON_UNESCAPED_SLASHES),
            '__TIMEOUT_MS__'  => (string) (max(1, (int) config('website-base.pwa.navigate_timeout', 15)) * 1000),
        ]);

        $version = substr(hash('sha256', $script.$this->offlineHtml()), 0, 12);

        return strtr($script, ['__VERSION__' => json_encode($version)]);
    }

    /**
     * The rendered offline page. Must be self-contained (inline styles and script only),
     * because it is shown from the cache when the server is gone.
     *
     * @return string
     */
    public function offlineHtml(): string
    {
        return view('website-base::pwa.offline', [
            'name'            => $this->name(),
            'themeColor'      => $this->themeColor(),
            'backgroundColor' => $this->backgroundColor(),
            'healthUrl'       => self::HEALTH_URL,
        ])->render();
    }

    /**
     * @param  string  $key  'theme_color' or 'background_color'
     *
     * @return string
     */
    protected function color(string $key): string
    {
        if ($value = config('website-base.pwa.'.$key)) {
            return (string) $value;
        }

        if (function_exists('theme_token')) {
            return (string) theme_token('pwa.'.$key, self::DEFAULT_COLOR);
        }

        return self::DEFAULT_COLOR;
    }

    /**
     * Public asset folders of the active theme and its parents (like 'themes/central-ui', 'themes/mercy-blank').
     * theme_asset() only knows the active theme, so the parents are added here.
     *
     * @return string[]
     */
    protected function themeAssetBases(): array
    {
        if (!app()->bound('themer')) {
            return [];
        }

        $manager = app('themer');
        if (!($theme = $manager->getActiveTheme())) {
            return [];
        }

        $bases = [];
        foreach (collect([$theme])->merge($manager->getInheritanceChain($theme)) as $item) {
            $bases[] = trim($item->assetPath ?: 'themes/'.$item->name, '/');
        }

        return $bases;
    }
}
