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

The dependencies will be met by the use of [Composer](http://getcomposer.org/) during the automatic installation process.

Ensure to have url rewriting enabled and set up properly in your web server for `Slim` to function as expected (see the corresponding [documentation](https://github.com/codeguy/Slim#setup-your-web-server) for more information on how to set this up correctly).

### Setup:

As of `0.1.6`, a basic installer comes with BattleReporter. Run

	$ php install.php

to launch it. The installer asks for some basic information regarding your installation of BattleReporter, uses [Composer](http://getcomposer.org) to meet the requirements (it will be installed into the installation directory if not present), create the BattleReporter's own database tables and set up the primary admin's user account.

After the basic installation, you should download and import the tables `mapSolarSystems`, `invGroups` and `invTypes` from [Steve Ronuken/fuzzysteve's EVE SDE MySQL Dump](https://www.fuzzwork.co.uk/dump/latest/) as they are mandatory for BattleReporter to work.

_to be continued ..._