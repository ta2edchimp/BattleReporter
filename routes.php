<?php

/*
 *  Routings, on Error
 */
// 404 - File Not Found
$app->notFound(function () use ($app) {
    $app->render("404.html");
});

// Any Error ...
$app->error(function (\Exception $e) use ($app) {
    include("views/error.php");
});


/*
 *  Default routings
 */
// Homepage
$app->get('/', function () use ($app, $db) {
    
    include("views/index.php");
    
});

// Show certain battle report
$app->get('/show/:battleReportID(/:battleReportDetail)', function($battleReportID, $battleReportDetail = "") use ($app) {
    
    include("views/show.php");
    
});

// Creating new battle reports
$app->map('/create', function () use ($app) {
    
    include("views/create.php");
    
})->via('GET', 'POST');

// Editing existing (and newly created) battle reports
$app->map('/edit/:battleReportID(/:editAction)', function ($battleReportID, $battleReportEditAction = "") use ($app, $basePath) {
    
    include("views/edit.php");
    
})->via('GET', 'POST');

// Log in
$app->map('/login', function () use ($app) {
    
    include("views/login.php");
    
})->via('GET', 'POST');
if (BR_LOGINMETHOD_EVE_SSO === true) {
	$app->get('/login/eve-sso', function () use ($app) {
		include("views/login-methods/eve-sso.php");
	});
	$app->get('/login/eve-sso-auth', function () use ($app) {
		include("views/login-methods/eve-sso-auth.php");
	});
}

// Log out
$app->get('/logout', function () use ($app, $basePath, $theme, $BR_DEBUGMODE) {
    
    include("views/logout.php");
    
});

// Info pages
$app->get('/info(/:page)', function ($page = "about") use ($app, $basePath, $theme) {
	
	include("views/info.php");
	
});

// Autocomplete Suggestions:
$app->group('/autocomplete', function () use ($app, $db) {
    
    // Fetching solar systems for input suggestions
    $app->post('/solarSystems', function () use ($app, $db) {
        include("views/autocomplete/solarSystems.php");
    });
    
    // Fetching ship names for input suggestions
    $app->post('/shipNames', function () use ($app, $db) {
        include("views/autocomplete/shipNames.php");
    });
    
    // Fetching known corporation and alliance names
    $app->post('/corpNames', function () use ($app, $db) {
        include("views/autocomplete/corpNames.php");
    });
    $app->post('/alliNames', function () use ($app, $db) {
        include("views/autocomplete/alliNames.php");
    });
	
	// For associating a battle report's combatant
	// to anything, e.g. pov-footage
	$app->post('/combatants/:battleReportID', function ($battleReportID) use ($app, $db) {
		include("views/autocomplete/combatants.php");
	});
    
});

// Pages only for logged in users
if (User::isLoggedIn() && BR_COMMENTS_ENABLED === true) {
	$app->post('/comment/:battleReportID', function ($battleReportID) use ($app, $db) {
		
		include("views/comment.php");
		
	});
	if (User::isAdmin()) {
		$app->get('/comment/delete/:commentID', function ($commentID) use ($app, $db) {
			
			include("views/admin/deleteComment.php");
			
		});
	}
}

// Admin only pages
if (User::isAdmin()) {
	$app->get('/admin(/:adminAction)', function ($adminAction = "") use ($app, $db, $basePath) {
		
		include("views/admin/admin.php");
		
	});
}

// Slack.com Integration
if (BR_API_SLACK_ENABLED === true && BR_API_SLACK_TOKEN !== '') {
	$app->map('/api/slack', function () use ($app, $basePath) {
		
		include("api/slack.php");
		
	})->via('GET', 'POST');
}
