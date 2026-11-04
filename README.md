# WebsiteBase Module

A module for the [Mercy Scaffold](https://github.com/aklebe-laravel/mercy-scaffold).

Base of a multi store website: database structure, models, navigation, CMS pages, management of
Eloquent models (data tables and forms) and notifications. Uses the modules `Acl`, `Form` and `DataTable`.

## Installation

Part of the Mercy base: every project gets it automatically in a released version (`base_requires` of the
[Mercy defaults](https://github.com/aklebe-laravel/mercy-php-deployment/blob/master/resources/defaults/setup.yaml)).
List it in the project's `requires` only to use another version or branch, see
[Require modules and themes](https://github.com/aklebe-laravel/deploy-env-module#require-modules-and-themes).

## Configuration

### Core config

App and module settings are stored in the database, table `core_config` (per store).

Values of the `options` json in `core_config`:

| Json key    | Type   | Description                               |
|-------------|--------|-------------------------------------------|
| form        | object | form specific values                      |
| - new_group | bool   | forms: start a new group for this element |
| - full_row  | bool   | forms: use a full row for this element    |

To enable notifications, configure the channels in the core config. Example for the email channel:

- set `email.enabled` to 1
- set `notification.channels.email.enabled` to 1

### Env

| Key                                                   | Default | Meaning                                                    |
|-------------------------------------------------------|---------|------------------------------------------------------------|
| `MODULE_SYSTEM_BASE_CACHE_CORE_CONFIG_TTL`            | 1       | cache lifetime of core config values in seconds            |
| `MODULE_WEBSITEBASE_CACHE_EXTRA_ATTRIBUTE_TTL`        | 10      | cache lifetime of extra attributes in seconds, 0 = forever |
| `MODULE_WEBSITEBASE_CACHE_EXTRA_ATTRIBUTE_ENTITY_TTL` | 10      | cache lifetime of extra attribute entities, 0 = forever    |

Use 0 (forever) for the extra attribute caches in production.

`MAIL_FROM_ADDRESS` (config `mail.from.address`) is **not** used for notifications. Every sender is a user:
the user `NoReplyNotificationUser` holds the sender data (like the email address). It belongs to the
AclGroup `Puppets`.

## Console

| Command                                                     | Meaning                                                                                                 |
|-------------------------------------------------------------|---------------------------------------------------------------------------------------------------------|
| `website-base:attr-clean`                                   | removes attribute assignments of models that no longer exist (scheduled every 2 hours)                  |
| `website-base:changelog`                                    | generates the changelog once (clear the cache to force it again)                                        |
| `website-base:core-config {path} {--store_id=} {--module=}` | reads a core config value                                                                               |
| `website-base:user {--user_ids=} {--repair}`                | shows users with broken settings (like a missing preferred notification channel), `--repair` fixes them |

## Manage Eloquent models

To manage a model in data tables and forms, create these classes (`xxx` = name of the model by default):

```
Forms/xxx.php
Http/Livewire/Form/xxx.php
Http/Livewire/DataTable/xxx.php
```

`php artisan form:make` (Form module) or `php artisan deploy-env:former MyModule --classes=xxx` (DeployEnv) creates them.

## Media images

`MediaService::createMediaFile()` creates the files of an image media item (`media_type` `IMAGE`) from a source file:
the original (`storage/app/public/media`) and the thumbs `thumbs_medium` and `thumbs_small`.
Sizes and quality come from the core config (`catalog.product.image*`, default 1000, 200 and 50 px).

- All sizes are scaled down only, keeping the aspect ratio: never upscaled and without a background.
  Framing is done by the frontend, like a square with the theme color in light and dark mode:
  `<img class="aspect-square w-full object-contain bg-surface-100" src="...">`
- Images are saved as WebP (keeps transparency). Animated GIFs stay animated GIFs, because the GD driver
  can't write animated WebP.
- Animated GIFs bigger than `MediaService::MAX_ANIMATED_GIF_PIXELS` (frames × width × height) are saved as WebP
  of the first frame, because GD keeps all frames in memory.

## Notifications

### Channel

The channel of a message is chosen in this order:

1. the preferred channel of the user
2. if not set: the preferred channel configured for the site
3. if not set: email
4. if the user has no email or a fake email (`^.+?@(fake\..*|example\..*)$`), another valid channel is used

### Event data

`event_data` depends on the `event_code`. Example `AclGroups_attached_to_User`:

- `{"acl_group":"Traders"}`: triggered if the group "Traders" was assigned to a user
- `{"acl_group":"*"}`: triggered if any other group (without its own event) was assigned to a user

### Troubleshooting

If a user got no email:

- the core config is incomplete, check the paths `channel`, `notification`, `email`
- the user has no email, or a fake email (`User::hasFakeEmail()`)
- check the log

## Progressive web app

The website can be installed as an app (Android, iOS, desktop), without a store. When the server can't be
reached (VPN off, server down), the installed app shows an offline page instead of a browser error.

### How it works

| Url                     | Served by                        | Content                                                 |
|-------------------------|----------------------------------|---------------------------------------------------------|
| `/manifest.webmanifest` | `PwaController::manifest()`      | name, colors, icons, start url                          |
| `/sw.js`                | `PwaController::serviceWorker()` | `resources/pwa/sw.js` with the values of the config     |
| `/offline`              | `PwaController::offline()`       | `resources/views/pwa/offline.blade.php`, self-contained |

The routes (`routes/pwa.php`) run **without** the `web` middleware (no session, no CSRF). The service worker
must be served from the root, otherwise it would not control the whole site.

The service worker only handles page loads (`navigate`): network error, timeout (`navigate_timeout`) or a
proxy error 502/503/504 → the cached offline page. Livewire requests, assets, API calls and websockets (Reverb) are not touched, nothing dynamic is cached. The offline page polls `/up` every 10
seconds and
reloads when the app is back.

The cache version is a hash of the service worker and the offline page: change one of them and the browsers
install the new version on their next visit. There is no version to count by hand.

### Setup

1. Include the tags in the `<head>` of the layout (done in `mercy-blank` and `central-ui`):
   `@include('website-base::pwa.head')`
2. Icons in the theme: `resources/assets/images/pwa/` with `icon-192.png`, `icon-512.png`,
   `icon-512-maskable.png` (logo inside the inner 80 %, full background) and `apple-touch-icon.png`
   (180×180, no transparency). Missing files are taken from the parent theme (`mercy-blank` has neutral ones).
3. Optional colors in the `theme.json` of the theme: `"tokens": {"pwa.theme_color": "#020617",
   "pwa.background_color": "#020617"}`
4. HTTPS is required (except `localhost`).

The start url of the app is the start page after the login (`website-base.login_redirect`).

### Env

| Key                                        | Default                    | Meaning                                               |
|--------------------------------------------|----------------------------|-------------------------------------------------------|
| `MODULE_WEBSITE_BASE_PWA_ENABLED`          | on if `APP_ENV=production` | link manifest and register the service worker         |
| `MODULE_WEBSITE_BASE_PWA_NAME`             | `APP_NAME`                 | name of the app                                       |
| `MODULE_WEBSITE_BASE_PWA_SHORT_NAME`       | name                       | name below the icon (about 12 chars)                  |
| `MODULE_WEBSITE_BASE_PWA_THEME_COLOR`      | theme token, `#020617`     | color of the system bar                               |
| `MODULE_WEBSITE_BASE_PWA_BACKGROUND_COLOR` | theme token, `#020617`     | splash screen and dark background of the offline page |
| `MODULE_WEBSITE_BASE_PWA_NAVIGATE_TIMEOUT` | 15                         | seconds until a page load shows the offline page      |

Disabled, the layout **removes** a service worker registered earlier, so switching it off really switches it off.

### Test and troubleshooting

- Desktop Chrome: DevTools → Application → Manifest / Service workers. Offline test: Network → Offline,
  then reload. Install: icon in the address bar.
- Android: Chrome → menu → "Install app". Debugging via USB and `chrome://inspect`.
- iOS: Safari → Share → "Add to Home Screen". The app has its own storage (separate from Safari): log in once,
  the remember cookie keeps the login.
- Old service worker hangs around: DevTools → Application → Storage → "Clear site data".

## License

MIT, see [LICENSE.md](LICENSE.md).
