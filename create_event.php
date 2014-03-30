<?php
/**
 * @author Jeffy Shih <jeffy@ninthday.info>
 * @copyright (c) 2014, Jeffy Shih - http://www.linkedin.com/pub/shiuh-feng-shih/4a/b25/31a
 * 
 * niceTwapperKeeper - Twitter Archiving Application
 * (https://github.com/ninthday/niceKeeper)
 * 
 * This program is from youTwapperKeeper on the Github.
 * (https://github.com/540co/yourTwapperKeeper)
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 */

// Load important files
session_start();
require_once('config.php');
require_once('function.php');
require_once('twitteroauth.php'); 

// validate information before creating
if (!(isset($_SESSION['access_token']['screen_name']))) {
	$_SESSION['notice'] = 'You must login to create an event.';
	header('Location: index.php');
	die;
	}

$event_title = filter_input(INPUT_POST, 'evntitle');
$event_description = filter_input(INPUT_POST, 'description');
$event_date = filter_input(INPUT_POST, 'evnTime');
// create and redirect
$result = $tk->createEvent($event_title, $event_description, $event_date, $_SESSION['access_token']['screen_name']);
$_SESSION['notice'] = $result;

header('Location: event.php');
?>