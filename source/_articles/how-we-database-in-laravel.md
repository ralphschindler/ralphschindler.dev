---
extends: _layouts.article
section: content
title: How We Database In Laravel
date: 2019-07-11
description: How we manage our production, qa, and developer databases for our Laravel Projects 
cover_image: /assets/images/how-we-database-in-laravel-cover.png
featured: true
categories: [laravel]
---

I'll first set the stage without going deep into our whole stack, but enough so there is a clear picture why and how we do what we do.  Nearly all of our Laravel based applications at [Ziff Media Group](https://www.ziffdavis.com) are MySQL (AWS Aurora) backed.  Our application are deployed into a K8s cluster, our containers are Docker, the filesystem accessible to the application is typically AWS S3.

These projects are generally monorepo, and the development environment is brought to life by Docker Compose. A developer merely needs run `docker-compose up -d`, which typically brings up the application web instance (in which nginx and php-fpm are entwined), a mysql instance that matches the Aurora version, and sometimes a touch of Redis too, depending on the project's needs.

## Which Model To Choose

We've chosen the *"give developers a snapshot of production data"* model. This means both the current state of the database's schema as well as the data. Before exploring what that model looks like, here are the models we chose not to use:

#### The "run all the migrations and use seeders" Model:

We chose not to use this model for two reasons.  The first is that it is typical that our projects would reach upwards of hundreds of migrations within a year, especially for the ones that have a large number of developers and/or are very active.  The second is that with seeding, you are generally working with contrived data.  Perhaps it is data created by Faker, or a set of real data. Either way, the Fake data offers less value, and the real data in a seeder will quickly become stale.  Both developers and QA Testers benefit from seeing real and current data in their development and QA instances of the project.

#### The "connect to a shared non-local database" Model:

We chose not to use this model primarily for concurrency reasons.  At any one time, many developers could be working on the same project.  Each might need to apply their own migration for their ticket/PR.  While we could keep cloning/resetting the shared instance back to a copy of production, there would still be scheduling conflicts when developers have migrations that have destructive data changes or significant changes to the schema.

## The Individual Production Snapshot Model

This is the best model that fits our workflow. Every developer can start with a snapshot of the state of the production database. They are free to make whatever changes they need locally, destroy or modify the data as they see fit, and when they need, easily reset back to a fresh snapshot of production.

It is also worth noting that the design of your system should be such that your production database is a manageable size.  For us, that generally means our production databases are a few to several hundred megs. We have one that is just under a terabyte, and this method still works well.

> Note: to keep smaller databases, it's best to keep only application specific / domain specific data in the database. Data records like logging should go to an appropriate log location, user audit trails should go to an audit specific transfer and storage medium.  If that is not possible, keeping them in your production database but selecting which tables to NOT include in a snapshot is also possible.

Originally, we achieved this through using a Spatie package [spatie/laravel-db-snapshots](https://github.com/spatie/laravel-db-snapshots). For most people's needs, this will work perfectly well. This tool shines when your production database is only a few megs.  It starts to break down when your production data set is beyond several megs. (The reason for this is that all the SQL in the mysqldump dump file is executed inside PHP's PDO query method when loading the data back in. This means you're bound by PHP's memory_limit and MySQL's client limitations: packet sizing, etc. I'm sure a pull request for this feature would be welcome).

We settled on writing a highly specific Laravel Command to make nightly snapshots of production, we schedule this command to run **nightly**.  The gist of it's workflow is this:

- With the `create` argument it:
    - executes the command line `mysqldump` command to create a snapshot-data.sql file
    - executes the command line gzip on the file to compress it
    - moves the file to a place in the S3 bucket
- With the `load` argument it:
    - copies the snapshot-data.sql file from S3
    - executes a gunzip and pipes the SQL into the mysql client command line tool

Some other interesting things it does:

- prevents mishaps by ensuring you're not creating during local development environments, and loading in production environments
- it creates a mysql credentials file temporarily to ensure the mysql client won't complain about insecure usage of passwords (kudos to [spatie/db-dumper](https://github.com/spatie/db-dumper) for that idea)

<script src="https://gist.github.com/ralphschindler/f29eba49eed76d384210a59daf900020.js"></script>

This script could likely be made more generic and packaged up for broader use, but we are not there yet. Many of the paths in here, the fact that it only supports MySQL, and some of the assumptions are specific to our environment and workflow. That said, maybe it will be packaged up one day.

### We never go down

We don't write migration down methods. We never expect to use them. By rule, we never use them in production.  For development, a developer is free to use them while they are hacking on a ticket/PR, but we don't expect that to be committed to the migration.  Getting back to a clean state is as simple as re-importing the production snapshot.

### Other Benefits To This Approach

We have a CI/CD and QA tooling setup that allows us to have every PR to the project brought up and kept current with the PR until it is merged.  This means that at any given time, there might be a handful to dozens of versions of this project running in our QA environment and developers local machines.

So for any given PR to be ready to go through a QA process, from scratch to a working feature instance, the workflow roughly looks like this:

- QA instance pulls down source repository and checks out feature branch
- `docker-compose up -d` (just as a developer would)
- run snapshot loader: `artisan app:snapshot load`
- run migrations specific to feature branch: `artisan migrate`
- (optionally run a seeder that puts data in the QA instance specific to the feature for testing)

## Help: I have a Legacy Project I Want To Achieve This On

If you find yourself coming into a Laravel project that does not have a good workflow for managing their database, you can still get there.  Perhaps the project's migration files have been used haphazardly and running what migrations exist does not match the current structure of production, or maybe there are no migration files at all.

Not a big deal. The first step would be to memorialize the state of the database as a "rollup" migration. (If migrations already exist but do not represent the current state of production, first remove the migrations, then follow along).  The general idea here is that even though we don't intend on running this rollup migration as part of a migration process, it will give a clear look backwards at the history of how the database got into a consistent state.

There are several methods to producing this rollup migration:

- If you use SequelPro, perhaps use this bundle [cviebrock/sequel-pro-laravel-export](https://github.com/cviebrock/sequel-pro-laravel-export)
- Write a new migration by hand and run it against an empty database; mysqldump the results and compare those against a mysqldump of the production schema; wash, rinse, repeat until they look exactly the same.
- Use a tool that will statically analyze the database and produce a migration, like [Xethron/migrations-generator](https://github.com/Xethron/migrations-generator)

Finally, make sure to clear out your migrations table in your production database and replace it with a single record for the rollup where the id is 1 and the migration is equal to the name of the rollup migration file (without the .php suffix) and the batch is equal to 1.

