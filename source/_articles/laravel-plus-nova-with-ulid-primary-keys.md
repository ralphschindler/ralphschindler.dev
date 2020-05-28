---
extends: _layouts.article
section: content
title: Laravel + Nova With ULID Primary Keys
date: 2020-05-25
description: Using ULIDs for Primary Keys in Laravel and Laravel Nova applications
cover_image: /assets/images/laravel-plus-nova-with-ulid-primary-keys/cover.png
featured: true
categories: [laravel]
---

In this short post, we'll explore what one has to do in order to build out a system that uses non-autoincrementing primary keys.  Why would we want to do this?

Whether an internal or public facing, let's consider URL like `https://example.com/things/12345` and what qualities it has:

- it potentially exposes that the generation of new ID's as being possibly sequential, which might encourage URL guessing
- it also, potentially exposes the size of the dataset
- it might imply that the database is in charge of assigning new IDs (thus they can't be created offline)
- the ID 12345 might exist in multiple tables

Considering this, we may consider moving away from database assigned autoincrementing IDs. And for most developers, the first non-autoinc kind of identifier that comes to mind is a UUID (sometimes a GUID).

One approach we may consider is to augment records with a UUID. The approach of augmentation means you may have both an `id` column and a `uuid` column in a record. Your foreign keys still are mapped to the unsigned integer `id` columns, but your `uuid` is what you may use when exposing the records as resources on a website or in a API.

A natural alternative is using UUID's in the database as the *primary keys* themselves. This also means you're also using them as foreign keys in related tables.  We won't go into the specifics, but there is much discussion of the pros/cons of such an approach:

- [https://www.percona.com/blog/2019/11/22/uuids-are-popular-but-bad-for-performance-lets-discuss/](https://www.percona.com/blog/2019/11/22/uuids-are-popular-but-bad-for-performance-lets-discuss/)
- [https://mysqlserverteam.com/storing-uuid-values-in-mysql-tables/](https://mysqlserverteam.com/storing-uuid-values-in-mysql-tables/)
- Also, MySQL 8 has introduced some UUID specific functions and feature: [https://mysqlserverteam.com/mysql-8-0-uuid-support/](https://mysqlserverteam.com/mysql-8-0-uuid-support/)

## ULIDs

An alternative to UUIDs that I like to use are ULIDs [https://github.com/ulid/spec](https://github.com/ulid/spec).  There are a couple of advantages to using ULIDs:

- when stored as strings, ULIDs take up 26 chars, whereas UUIDs take up 36 characters
- ULIDs, unlike most but not all UUID/GUID implementations, are sequential
- ULIDs are arguably "prettier", seemingly random and are case insensitve, eg: `01arz3ndektsv4rrffq69g5fav`

If you are building an app that has a database, and you're not housing millions and millions of rows but maybe tens or hundreds of thousands, ULIDs are a good choice as they have negligable performance issues (when inserting or used for foreign keys) and offer the right balance of features when using them for their random and sequential (for database indexing purposes) characteristics.  If you wanted more details and discussion, here are a few links I found:

- [https://news.ycombinator.com/item?id=13116308](https://news.ycombinator.com/item?id=13116308)
- [https://www.honeybadger.io/blog/uuids-and-ulids/](https://www.honeybadger.io/blog/uuids-and-ulids/)

## ULID In Laravel Eloquent Models

To use ULIDs in Laravel, php more specifically, we need to either write a generator or use a 3rd party library (I use this one [https://github.com/robinvdvleuten/php-ulid](https://github.com/robinvdvleuten/php-ulid).) While the implementation is trivial enough, I've found there exists a good library that works and is maintained, so let's install that:

    composer require robinvdvleuten/ulid

Then, let's build a migration, it will look something like this in the `up` method:

```php
    Schema::create('widgets', function (Blueprint $table) {
        $table->char('id', 26)->primary(); // instead of $table->id(), which is an unsigned big integer, auto-incrementing
        $table->string('name');
        $table->timestamps();
    });
```

Next, we need to build a Trait that will enable our models to create ULIDs.

```php
<?php

namespace App\Models\Concerns;

use Ulid\Ulid;

trait HasUlid
{
    public static function bootHasUlid()
    {
        // when creating models, we will generate a new ULID before saving
        static::creating(function ($model) {
            if (!isset($model->id)) {
                $model->id = (string) Ulid::generate(true);
            }
        });
    }

    public function initializeHasUlid()
    {
        // initialize for this trait runs for every new instance, here
        // we can change some default parameters for this model, specifically
        // we can turn off incrementing and tell Eloquent the PK is a string

        $this->incrementing = false;

        $this->keyType = 'string';
    }
}
```

Now, our Widget model can consume this concern / trait:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Widget extends Model
{
    use Concerns\HasUlid;
}
```

## ULID in Laravel Nova

Out of the box, Laravel Nova assumes primary keys are going to be created with `$table->id()` in a migration. This is an important detail only with regards to Nova's built-in change tracking table `action_events`.  So, instead of the `action_events` table making references to models that are normally *unsigned big integers*, we have to make it so referenced models are tracked with *character size 26 columns*.

![Image of Nova](/assets/images/laravel-plus-nova-with-ulid-primary-keys/action-events.png)

To achieve this change, we'll have to publish the Nova migration, so we can change it:

```
artisan vendor:publish --tag=nova-migrations
```

Next, we'll have to go into `2018_01_01_000000_create_action_events_table.php`, and make the following changes:

```diff
    Schema::create('action_events', function (Blueprint $table) {
        $table->id();
        $table->char('batch_id', 36);
        $table->unsignedBigInteger('user_id')->index();
        $table->string('name');
        $table->string('actionable_type');
-       $table->unsignedBigInteger('actionable_id');
+       $table->char('actionable_id', 26);
        $table->string('target_type');
-       $table->unsignedBigInteger('target_id');
+       $table->char('target_id', 26);
        $table->string('model_type');
-       $table->unsignedBigInteger('model_id')->nullable();
+       $table->char('model_id', 26);
        $table->text('fields');
        $table->string('status', 25)->default('running');
        $table->text('exception');
        $table->timestamps();

        $table->index(['actionable_type', 'actionable_id']);
        $table->index(['batch_id', 'model_type', 'model_id']);
    });
```

And that's it. Finally, we can create models that use a ULID and Laravel Nova will not complain:

![Image of Nova](/assets/images/laravel-plus-nova-with-ulid-primary-keys/created-widget.png)
