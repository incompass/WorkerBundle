[![Build Status](https://travis-ci.org/incompass/TimestampableBundle.svg?branch=master)](https://travis-ci.org/incompass/TimestampableBundle)

TimestampableBundle
===================

This bundle allows you to simply add ```use Timestampable``` 
to a doctrine entity class to have it automatically add 
created_at and updated_at fields and to have them updated on
insert and update.

Installation
------------

### Composer
```
composer require incompass-timestampablebundle
```

Usage
-----

Add the Timestampable trait to your doctrine entities.

```
use Timestampable
```

Update your database schema
```
php bin/console doctrine:schema:update --force
```

All entities will now be saved with created_at and updated_at fields populated.

Contributors
------------

Joe Mizzi (casechek/incompass)