## WebsiteBase Module

A module for [Mercy Scaffold Application](https://github.com/AKlebe/MercyScaffold.git)
(or any based on it like [Jumble Sale](https://github.com/AKlebe/JumbleSale.git)).

Website module with a bunch of functionalities for a multi store website.
Includes some dependencies like modules like ```Acl```, ```Form```, ```DataTable```
Provides database structure, models, navigations, CMS, management for eloquent models and notification.

### Setup


#### Env

```
MODULE_WEBSITEBASE_CACHE_EXTRA_ATTRIBUTE_TTL=0
```
Cached lifetime in seconds for extra attributes. Set to 0 (forever) in production.

```
MODULE_WEBSITEBASE_CACHE_EXTRA_ATTRIBUTE_ENTITY_TTL=0
```
Cached lifetime in seconds for extra attribute entities. Set to 0 (forever) in production.

#### Console

```
php artisan website-base:attr-clean
```

Removes all attribute assignments where model no longer exists.

### Manage Eloquent Models

To manage eloquent models in datatables and forms you need to create the following classes
```
Forms/xxx.php
Http/Livewire/Form/xxx.php
Http/Livewire/DataTable/xxx.php
```

xxx should have the same name like eloquent model.

#### NotificationEvent

##### Fields
- **event_data**: depends on the event_code
  - **event_code**: (any)
    - ``` {"view_path":"xxx"} ``` if send to telegram: view path (default=```telegram-api::telegram.default-message```)
    - ``` {"buttons":"website_link"} ``` if send to telegram: code of a button container declared in: ```\Modules\TelegramApi\Services\TelegramButtonService::DEFINED_BUTTON_CONTAINERS```
  - **event_code**: AclGroups_attached_to_User
    - ``` {"acl_group":"Traders"} ``` will be triggert if group "Traders" was assigned to user
    - ``` {"acl_group":"*"} ``` (no specific group like "Traders" above was found) will be triggert if any group was assigned to user


