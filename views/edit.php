<?php

if (!User::can('edit'))
    $app->redirect('/');

if (empty($battleReportID) || $battleReportID == 0) {
    $app->render("brNotFound.html");
    $app->stop();
}

$output = array(
	"BR_PAGE_EDIT" => true
);

// Try to fetch the specified battle report
$battleReport = new Battle();
if ($battleReport->load($battleReportID, false, true) === false) {
    $app->render("brNotFound.html");
    $app->stop();
}

// Admins may edit every battle report,
// normal users may only edit their own battle reports.
$twigEnv = $app->view()->getEnvironment();
$userIsAdminOrDirector = User::isAdmin() || User::is("Director");
if ($userIsAdminOrDirector || $battleReport->creatorUserID == User::getUserID()) {
	
	$twigEnv->addGlobal("BR_USER_CAN_EDIT", true);
	$twigEnv->addGlobal("BR_USER_CAN_UNPUBLISH", true);
	$twigEnv->addGlobal("BR_USER_CAN_DELETE", $userIsAdminOrDirector);
	
	if (strtolower($battleReportEditAction) == "unpublish") {
		$battleReport->unpublish();
		$app->redirect("/");
	}
	
	if ($userIsAdminOrDirector && strtolower($battleReportEditAction) == "delete") {
		$battleReport->delete();
		$app->redirect("/");
	}
	
} else {
	$twigEnv->addGlobal("BR_USER_CAN_EDIT", false);
	$twigEnv->addGlobal("BR_USER_CAN_UNPUBLISH", false);
	$twigEnv->addGlobal("BR_USER_CAN_DELETE", false);
}

// User posted changes to the current battle report
if ($app->request->isPost()) {
    
    $success = true;
    $parameters = $app->request->post();
    
    $brChanges = json_decode($parameters["battleReportChanges"]);
    if ($brChanges !== null)
        $success = $battleReport->applyChanges($brChanges);
    
    $battleReport->title = $parameters["battleTitle"];
	$battleReport->summary = $parameters["battleSummary"];
	
	$videoUrls = $parameters["battleFootageUrl"];
	$povCmbtID = $parameters["battleFootageCombatantID"];
	
	// currently, allow only one video
	$battleReport->removeFootage();
	foreach ($videoUrls as $videoUrl) {
		if (empty($videoUrl))
			continue;
		
		$footage = array(
			"url" => $videoUrl
		);
		
		$idx = array_search($videoUrl, $videoUrls);
		if ($idx !== FALSE && isset($povCmbtID[$idx]) && !empty($povCmbtID[$idx]))
			$footage["combatantID"] = $povCmbtID[$idx];
		
		$battleReport->addFootage($footage);
	}
	
	if ($success) {
		
		$previouslyUnpublished = !$battleReport->published;
		
		$battleReport->publish();
		
		// Check, whether to broadcast the newly published battle report
		if ($previouslyUnpublished === true) {
			
			// Post to Slack?
			if (BR_API_SLACK_ENABLED === true) {
				
				require_once ("$basePath/classes/API/Slack.php");
				
				$slack = new Slack(BR_API_SLACK_CHANNEL);
				if ($slack->postBattleWithID($battleReport->battleReportID) === null) {
					$app->log->warn("Something went wrong when trying to post the successfully published BattleReport #$battleReportID to Slack.");
				}
				
			}
			
		}
		
		// No need to reload the battle report records
		// as here comes the redirect, right away ...
		$app->redirect("/show/$battleReportID");
		
	} else {
		
		$output["battleReportSavingError"] = true;
		
	}
    
}

$output["battleReport"] = $battleReport;

$app->render("edit.html", $output);
