# BattleReporter

Advanced editable BattleReport platform for EVE Online.

## Setup

`BattleReporter` requires an environment running `PHP 5.4`. Version `5.5` is recommended, though.

### Requirements:

- [PhealNG](https://github.com/3rdpartyeve/phealng/)
- [Twig](http://twig.sensiolabs.org/)
- [Slim Framework](http://slimframework.com/)
- [Whoops](https://github.com/filp/whoops)
- [Whoops Middleware for Slim](https://github.com/zeuxisoo/php-slim-whoops)
- [phpFastCache](http://www.phpfastcache.com/)
- [Parsedown](https://github.com/erusev/parsedown)

Install and use [Composer](http://getcomposer.org/) to meet the requirements:

    $ php composer.phar install

Ensure to have url rewriting enabled and set up properly in your web server for `Slim` to function as expected (see the corresponding [documentation](https://github.com/codeguy/Slim#setup-your-web-server) for more information on how to set this up correctly).

### Setup:

As there is no setup available at the moment, one should start by setting up a new database and import the tables from the `/database` directory as well as the tables `mapSolarSystems`, `invGroups` and `invTypes` from [Steve Ronuken/fuzzysteve's EVE SDE MySQL Dump](https://www.fuzzwork.co.uk/dump/latest/).

Furthermore, you'll need a primary administrator account, therefore setup a php script with the following content:

```php
<?php
$password = "YourPasswordHere123!";
echo "<p>$password:<br>" . password_hash($password, PASSWORD_BCRYPT) . "</p>";
````

Execute it and add a new row to the table `brUsers` (set columns `userName` to your "admin" username, `password` to the script's output and `isAdmin` to `1`).

Customize the contents of `/config.blueprint.php` to your needs (hint: you may get your corporation's id via [zKillboard](https://zkillboard.com), [Eve-Kill](https://beta.eve-kill.net) or sth. like that) and save it as `/config.php`.

_to be continued ..._