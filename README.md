## WebsiteBase Module

A module for [Mercy Scaffold Application](https://github.com/aklebe-laravel/mercy-scaffold.git)
(or any based on it like [Jumble Sale](https://github.com/aklebe-laravel/jumble-sale.git)).

Website module with a bunch of functionalities for a multi store website.
Includes some dependencies like modules like ```Acl```, ```Form```, ```DataTable```
Provides database structure, models, navigations, CMS, management for eloquent models and notification.

### Setup
- Core Configuration
- To enable notifications you need to config the core config. For example the email channel:
  - set ```email.enabled``` to 1
  - set ```notification.channels.email.enabled``` to 1

#### Env

env ```MAIL_FROM_ADDRESS``` respective config ```mail.from.address``` will not be used to send notifications.
Instead, all identities are users.
There is a user ```NoReplyNotificationUser``` holds all relevant data from sender like sending emails. He has AclGroup ```Puppets``` which is important that specials.

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

Removes all attribute assignments where model no longer exists. It is scheduled every 2 hours by default.

#### Core-Config

Config for app and modules stored in database. See table ```core_config```.

Values for ```options``` json in ```core_config```:


| Json key    | type   | Description                                         |
|-------------|--------|-----------------------------------------------------|
| form        | object | Form specific object values                         |
| - new_group | bool   | For forms: start a new group for this element field |
| - full_row  | bool   | For forms: use a full row for this element field    |


### Manage Eloquent Models

To manage eloquent models in datatables and forms you need to create the following classes
```
Forms/xxx.php
Http/Livewire/Form/xxx.php
Http/Livewire/DataTable/xxx.php
```

By default ```xxx``` should have the same name as eloquent model.

### NotificationEvent

#### Channels

To decide the channel to received messages there are a some conditions checked by the sending process.

1) Check user preferred channel
2) If user preferred channel is not set, check the preferred channel configured on your site
3) If there is also not set, an email will send.
4) If user has no email or a fake email (```^.+?@(fake\..*|example\..*)$```), the email channel will not be used and another valid channel will be used.

#### Data Fields
- **event_data**: depends on the event_code
  - **event_code**: AclGroups_attached_to_User
    - ``` {"acl_group":"Traders"} ``` will be triggert if group "Traders" was assigned to user
    - ``` {"acl_group":"*"} ``` (no specific group like "Traders" above was found) will be triggert if any group was assigned to user


#### Troubleshooting

If no email was sent to user:
- core config is misconfigured, check config paths like ```channel```, ```notification```, ```email```
- user has no email
- user has a fake email like User::hasFakeEmail() ... ```^.+?@(fake\..*|example\..*)$```
- Check [Setup](./README.md:10)
- Check log
