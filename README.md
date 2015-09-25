# BattleReporter

[![Build](https://img.shields.io/travis/ta2edchimp/BattleReporter.svg?branch=master)](https://travis-ci.org/ta2edchimp/BattleReporter)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/264d2540-4350-4ffb-920c-1967e1db1df3.svg)](https://insight.sensiolabs.com/projects/264d2540-4350-4ffb-920c-1967e1db1df3)
[![Coveralls.io](https://img.shields.io/coveralls/ta2edchimp/BattleReporter.svg)](https://coveralls.io/github/ta2edchimp/BattleReporter)
[![Releases](https://img.shields.io/github/release/ta2edchimp/BattleReporter.svg)](https://github.com/ta2edchimp/BattleReporter/releases)
[![Issues](https://img.shields.io/github/issues-raw/ta2edchimp/BattleReporter.svg)](https://github.com/ta2edchimp/BattleReporter/issues)

Advanced editable BattleReport platform for EVE Online.

Download the current version at [`Releases`](https://github.com/ta2edchimp/BattleReporter/releases).

## Table of Contents

1. [What is BattleReporter](#what-is-battlereporter)
2. [Setup](#setup)
  1. [Requirements](#requirements)
  2. [Installation](#installation)
  3. [Enable Login via EVE-Online Single Sign On](#enable-login-via-eve-online-single-sign-on)
  4. [User Permissions by Role in Corp](#user-permissions-by-role-in-corp)
3. [Update](#update)
4. [Further Configuration](#further-configuration)
  1. [Integrate with Slack](#integrate-with-slack)
  2. [Theming BattleReporter](#theming-battlereporter)
5. [Contributing](#contributing)
6. [License, Acknowledgements & Legal Stuff](#license-acknowledgements--legal-stuff)

## What is BattleReporter

BattleReporter is a user generated killboard for [EVE-Online](http://eve-online.com), that fetches a corporation's kills (and losses) from either [zKillboard](https://zkillboard.com/) or [EVE-Kill.Net](https://beta.eve-kill.net/) and generates reports from them, that can be  
- manually amended (by adding logistics, videos and after action reports, ...),
- corrected (e.g. by removing unrelated kills or losses),
- clarified (you can specify a 3rd party, for example).

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

### Installation

To install BattleReporter, go grab its latest [release](https://github.com/ta2edchimp/BattleReporter/releases/latest) and unzip it to your server and run

	$ php install.php

The installer asks for some basic information regarding your installation of BattleReporter, uses [Composer](http://getcomposer.org) to meet the requirements (it will be installed into the installation directory if not present), create the BattleReporter's own database tables and set up the primary admin's user account.

Within BattleReporter's root directory, you'll find a `public` folder, which has to be set up as the web root.
Refer to [this documentation](https://github.com/codeguy/Slim#setup-your-web-server) to see how to configure you web server for BattleReporter to work properly (ensure to have url rewriting enabled).

#### Enable Login via EVE-Online Single Sign On

Initially, BattleReporter can only be logged into using the admin account created during its installation. To log in with your EVE Online account is available via Single Sign On. In order to enable log in via EVE-O SSO, you must register your BattleReporter installation as an application in the [EVE Developers portal](https://developers.eveonline.com/). A verified e-mail address is required to register (don't worry this takes no more than hitting a button in the EVE Online Account Management and clicking a link in the e-mail you'll receive soon afterwards).

When registering your BattleReporter installation in the EVE-Dev. portal, ensure to enter the correct `Callback URL`. Let's assume you installed BattleReporter to be available at `br.yourcorp.net` and you do not use SSL, the correct url would be

	http://br.yourcorp.net/login/eve-sso-auth

After hitting `Create Application`, you'll acquire a `Client ID` and a `Secret Key`. These are the values required during BattleReporter's installation process.

#### User Permissions by Role in Corp

During the installaton, it is possible to provide an EVE Online API Key to let BattleReporter check its owner corporation's members and their roles. Users logging in with characters who possess the `Director` role can then get the permission to edit, (un)publish and delete other users' battle reports.

For this to work, when setting up an [API Key](https://community.eveonline.com/support/api-key/), it is required to be of type `Corporation` and having access to `MemberSecurity` (AccessMask: `512`).

## Update

To update your BattleReporter installation, simply download the latest [release](https://github.com/ta2edchimp/BattleReporter/releases/latest) and unzip it to your server and run

	$ php update.php

to launch the automated update routine. It will update [Composer](http://getcomposer.org), update existing and install new dependencies, as well as update the database tables.

You may use the updater to update or modify the EVE Online Data at anytime. Just replace the files within `/database` and run the update script again. (_Hint:_ the tables required for operation are `mapSolarSystems`, `invGroups` and `invTypes`).

**Important Note when updating any pre `1.0` version:** Please ensure, all configuration variables from `/config.blueprint.php` are mentioned in your `/config.php` after running BattleReporter's update script (For example all variables with the `Slack` default values, when updating from `0.3` to `0.4`).

## Further Configuration

Some of BattleReporter's functions are not yet covered by the `install` and `update` scripts. You'll have to setup and configure them manually by customizing your `config.php` file accordingly.

### Integrate with Slack

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
define('BR_API_SLACK_CHANNEL', 'insert Webhook URL here');
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
* Give **Usage hints**: `[help, list, latest, id]`.

**Usage:**

Type `/battlereporter help` into the current channel's chat to get a list of options; Basically: enter `/battlereporter` (with or without appended `latest`) to post the latest created BattleReport, enter `/battlereporter list` to get a list of the 25 most recently created reports, or use `/battlereporter 123` to post the BattleReport with Id #123.

### Theming BattleReporter

Currently, BattleReporter comes with only a default theme. You can easily create your own or modify the default theme by duplicating the `/public/themes/default` folder and making the necessary changes to the included html templates, css files or images.  
BattleReporter uses `Twig` as its templating engine, so head over to [their excellent documentation](http://twig.sensiolabs.org/documentation) to see, what's possible.

To change the theme used by BattleReporter, just edit the following lines within `config.php` to your theme's folder name accordingly:

```PHP
/*
 *  Styles Config
 */
define('BR_THEME', 'default'); // Change 'default' to your theme's folder name
```

## Contributing

Please [file an issue](https://github.com/ta2edchimp/BattleReporter/issues/new) if you have any feature request, ideas for improvements or found any bugs.

Feel free to fork BattleReporter, commit some bug fixes, implement cool features or make a theme and make a pull request!

## License, Acknowledgements & Legal Stuff

BattleReporter is released unde the [Beerware License](https://tldrlegal.com/license/beerware-license); So, as long as you keep the included author information, feel free to do anything you want with the source codes.

BattleReporter uses parts of [CCPs](http://www.ccpgames.com/) EVE Online SDE, specifically [Steve Ronuken/fuzzysteve's EVE SDE MySQL-port Dump](https://www.fuzzwork.co.uk/dump/latest/).

**All EVE related materials are property of [CCP Games](http://ccpgames.com).**  
EVE Online and the EVE logo are the registered trademarks of CCP hf. All rights are reserved worldwide. All other trademarks are the property of their respective owners. EVE Online, the EVE logo, EVE and all associated logos and designs are the intellectual property of CCP hf. All artwork, screenshots, characters, vehicles, storylines, world facts or other recognizable features of the intellectual property relating to these trademarks are likewise the intellectual property of CCP hf. CCP hf. has granted permission to BattleReporter to use EVE Online and all associated logos and designs for promotional and information purposes on its website but does not endorse, and is not in any way affiliated with, BattleReporter. CCP is in no way responsible for the content on or functioning of this website, nor can it be liable for any damage arising from the use of this website.
