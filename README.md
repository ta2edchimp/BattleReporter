# BattleReporter [![SensioLabsInsight](https://insight.sensiolabs.com/projects/264d2540-4350-4ffb-920c-1967e1db1df3/mini.png)](https://insight.sensiolabs.com/projects/264d2540-4350-4ffb-920c-1967e1db1df3)

Advanced editable BattleReport platform for EVE Online.

- Current Version: `0.3`
- Download at [`Releases`](https://github.com/ta2edchimp/BattleReporter/releases)

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

### Installation:

As of release `0.1.6`, a basic installer comes with BattleReporter. Run

	$ php install.php

to launch it. The installer asks for some basic information regarding your installation of BattleReporter, uses [Composer](http://getcomposer.org) to meet the requirements (it will be installed into the installation directory if not present), create the BattleReporter's own database tables and set up the primary admin's user account.

#### Log in via EVE-O SSO

As of release `0.2`, logging into the BattleReport with your EVE Online account is available via Single Sign On. In order to enable log in via EVE-O SSO, you must register your BattleReporter installation as an application in the [EVE Developers portal](https://developers.eveonline.com/). A verified e-mail address is required to register (don't worry this takes no more than hitting a button in the EVE Online Account Management and clicking a link in the e-mail you'll receive soon afterwards).

When registering your BattleReporter installation in the EVE-Dev. portal, ensure to enter the correct `Callback URL`. Let's assume you installed BattleReporter to be available at `br.yourcorp.net` and you do not use SSL, the correct url would be

	http://br.yourcorp.net/login/eve-sso-auth

After hitting `Create Application`, you'll acquire a `Client ID` and a `Secret Key`. These are the values required during BattleReporter's installation process.

#### User Permissions by Role in Corp

As of release `0.3.5`, it is possible to provide an EVE Online API Key to let BattleReporter check its owner corporation's members and their roles. Users logging in with characters who possess the `Director` role can then get the permission to edit, (un)publish and delete other users' battle reports.

For this to work, when setting up an [API Key](https://community.eveonline.com/support/api-key/), it is required to be of type `Corporation` and having access to `MemberSecurity` (AccessMask: `512`).

## Update:

As of release `0.3.3`, an update script will assist you in the process of updating BattleReporter's components. Run

	$ php update.php

to launch it. It will update [Composer](http://getcomposer.org), update existing and install new dependencies, as well as update the database tables.

You may use the updater to update or modify the EVE Online Data at anytime. Just replace the files within `/database` and run the update script again. (_Hint:_ the tables required for operation are `mapSolarSystems`, `invGroups` and `invTypes`).

## Acknowledgements & Legal Stuff

BattleReporter uses parts of [CCPs](http://www.ccpgames.com/) EVE Online SDE, specifically [Steve Ronuken/fuzzysteve's EVE SDE MySQL-port Dump](https://www.fuzzwork.co.uk/dump/latest/).


_to be continued ..._
