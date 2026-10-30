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

## License

MIT, see [LICENSE.md](LICENSE.md).
