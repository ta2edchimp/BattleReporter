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

## Update

As of release `0.3.3`, an update script will assist you in the process of updating BattleReporter's components. Run

	$ php update.php

to launch it. It will update [Composer](http://getcomposer.org), update existing and install new dependencies, as well as update the database tables.

You may use the updater to update or modify the EVE Online Data at anytime. Just replace the files within `/database` and run the update script again. (_Hint:_ the tables required for operation are `mapSolarSystems`, `invGroups` and `invTypes`).

## Further Configuration

Some of BattleReporter's functions are not yet covered by the `install` and `update` scripts. You'll have to setup and configure them manually by customizing your `config.php` file accordingly.

### Integration to Slack

I sure hope, you know what `Slack` is. If not, head over to the [`Slack` web site](https://slack.com/) and see, why it is better for your corporation's communiation than any IRC, WhatsApp group or whatever you nullsec dwellers currently use to ping the cap pilots ;P

BattleReporter supports to modes to be integrated with `Slack`:

* **Incoming Webhook** configure BattleReporter to automatically post new reports to a certain channel within your Slack team.
* **Slash Command** type `/battlereporter` in any public group or direct conversation to post a BattleReport.

#### Setup as Incoming Webhook

In order for BattleReporter to post anything to `Slack`, you'll have to set up an [Incoming Webhook integration](https://slack.com/services/new/incoming-webhook); Select the `Post to Channel` where you want the reports to be posted and copy the `Webhook URL` provided.

You may also set a label and customize the name and icon, but that's not necessary, as the name as who the BattleReporter posts will be the name of your BattleReporter installation and the icon next to each post will be your corporation's logo.

Next, head over to your `config.php` file and change or add (if yet absent) the following lines:

```php
// Enable Slack Integration
define('BR_API_SLACK_ENABLED', true);
// Set the designated destination for Posts to Slack
define('BR_API_SLACK_CHANNEL', 'insert Webjook URL here');
```

That's it. Everytime you hit `Save and Publish BattleReport` on creation of a new report, it will instantly be posted in the specified channel.

#### Setup Slash Command

You can also set up a Slash Command in `Slack` for BattleReporter. It will enable to directly post BattleReports in the channel you're currently chatting (except: private groups, due to Slack's API limitations). **You still must configure an Incoming Webhook** for the Slash Command to work as expected!

Head over to your Slack Team's settings, and [setup a new Slash Command](https://bitslix.slack.com/services/new/slash-commands):

* Enter `/battlereporter` or whatever you want, into the `Choose a Command` input.
* Enter `http://your.battlereporter.host/api/slack` into the `URL` input.
* Copy the `Token`.

The `Token` is the required information to insert into your BattleReporter installation's `config.php`, change or add:

```php
// Set the authorization Token for Slack Slash Commands
define('BR_API_SLACK_TOKEN', 'insert Token here');
```

You may proceed to configure the `Slash Command` in Slack. Recommended settings are:

* **Activate** `Show this command in the autocomplete list`.
* Set **Description** to sth. like `Post BattleReports`.
* Give **Usage hints**: `[help] [list] [latest] [id]`.

## Acknowledgements & Legal Stuff

BattleReporter uses parts of [CCPs](http://www.ccpgames.com/) EVE Online SDE, specifically [Steve Ronuken/fuzzysteve's EVE SDE MySQL-port Dump](https://www.fuzzwork.co.uk/dump/latest/).


_to be continued ..._
