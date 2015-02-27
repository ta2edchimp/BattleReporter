<?php

/*
 *  Site Config
 */
define('BR_OWNER', '%BR_OWNER%');

define('BR_OWNERCORP_NAME', '%BR_OWNERCORP_NAME%');
define('BR_OWNERCORP_ID', '%BR_OWNERCORP_ID%');


/*
 *  Source Killboard to fetch from
 */
define('BR_FETCH_SOURCE_NAME', '%BR_FETCH_SOURCE_NAME%');
define('BR_FETCH_SOURCE_URL', '%BR_FETCH_SOURCE_URL%');


/*
 *  Database Config
 */
// The database server's host, usually 'localhost' / '127.0.0.1'
define('DB_HOST', '%DB_HOST%');
// The database's name
define('DB_NAME', '%DB_NAME%');
// Specify a user with sufficient access rights, and his password
define('DB_USER', '%DB_USER%');
define('DB_PASS', '%DB_PASS%');


/*
 *  Styles Config
 */
define('BR_THEME', '%BR_THEME%');


/*
 *	Allowed Login Methods
 */
// Only members of this BattleReporter's owner corp may login
define('BR_LOGIN_ONLY_OWNERCORP', %BR_LOGIN_ONLY_OWNERCORP%);
// Allow login via EVE Online Single Sign On?
define('BR_LOGINMETHOD_EVE_SSO', %BR_LOGINMETHOD_EVE_SSO%);
define('BR_LOGINMETHOD_EVE_SSO_CLIENTID', '%BR_LOGINMETHOD_EVE_SSO_CLIENTID%');
define('BR_LOGINMETHOD_EVE_SSO_SECRET', '%BR_LOGINMETHOD_EVE_SSO_SECRET%');


/*
 *	Advanced Functions
 */
define('BR_COMMENTS_ENABLED', %BR_COMMENTS_ENABLED%);
// Slack.com Integration
//define('BR_API_SLACK_ENABLED', %BR_API_SLACK_ENABLED%);	// true/false
//define('BR_API_SLACK_TOKEN', '%BR_API_SLACK_TOKEN%');		// Slash Command Token provided by Slack
//define('BR_API_SLACK_CHANNEL', '%BR_API_SLACK_CHANNEL%');	// Webhook URL provided by Slack


/*
 *  Debug Mode
 */
$BR_DEBUGMODE = true;
