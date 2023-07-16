<?php

define('__ROOT__', dirname(dirname(__FILE__)));

require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
require_once '../class/Netatmo.class.php';


$plugin = plugin::byId("Netatmo");
$eqLogicList = eqLogic::byType($plugin->getId());

/**
* Webhooks Endpoint Example.
* This script has to be hosted on a webserver in order to make it work.
* This endpoint should first be registered as webhook URI in your app settings on Netatmo Developer Platform (or registered using the API).
* If you don't known how to register a webhook, or simply need ore information please refer to documentation: https://dev.netatmo.com/doc/webhooks)
*/

//Get the post JSON sent by Netatmo servers
$jsonData = file_get_contents("php://input");

//Each time a webhook notification is sent, log it into a text file
if(!is_null($jsonData) && !empty($jsonData)) {
	$json = json_decode($jsonData, TRUE);
	
	log::add("Netatmo", 'debug', print_r($json, true));
	$module = $json['home']['modules'][0];
	$room = $json['home']['rooms'][0];
	$eqLogic = eqLogic::byLogicalId($module['id'], Netatmo);
	if(!empty($room['therm_setpoint_temperature'])) {
		$eqLogic->checkAndUpdateCmd('therm_setpoint_temperature', $room['therm_setpoint_temperature']);
	}
	if(!empty($room['therm_setpoint_end_time'])) {
		$eqLogic->checkAndUpdateCmd('therm_setpoint_end_time', $room['therm_setpoint_end_time']);
	}
	if(!empty($room['therm_setpoint_mode'])) {
		$therm_setpoint_mode = $room['therm_setpoint_mode'];
	}
	if(!empty($json['therm_mode'])) {
		$therm_setpoint_mode = $json['therm_mode'];
	}
	if(!empty($json['mode'])) {
		$therm_setpoint_mode = $json['mode'];
	}
	log::add("Netatmo", 'info', "therm_setpoint_mode : " . $therm_setpoint_mode);
	if(!empty($therm_setpoint_mode)) {
		$eqLogic->checkAndUpdateCmd('therm_setpoint_mode', $therm_setpoint_mode);
		switch ($therm_setpoint_mode) {
			case 'home':
				$eqLogic->checkAndUpdateCmd('therm_setpoint_temperature', 18.5); // Change 18.5
				$eqLogic->checkAndUpdateCmd('therm_setpoint_end_time', 0);
			break;
			case 'away':
				$eqLogic->checkAndUpdateCmd('therm_setpoint_temperature', 15); // Change 18.5
				$eqLogic->checkAndUpdateCmd('therm_setpoint_end_time', 0);
			break;
			case 'schedule':
				$eqLogic->checkAndUpdateCmd('therm_setpoint_temperature', 19); // Change 18.5
				$eqLogic->checkAndUpdateCmd('therm_setpoint_end_time', 0);
			break;
			case 'hg':
				$eqLogic->checkAndUpdateCmd('therm_setpoint_temperature', 10); // Change 18.5
				$eqLogic->checkAndUpdateCmd('therm_setpoint_end_time', 0);
			break;
			case 'manual':
			break;
			default:
					log::add("Netatmo", 'info', "therm_setpoint_mode non géré " . $therm_setpoint_mode);
				break;
		}
	}
	if(!empty($room['therm_mode_endtime'])) {
		$eqLogic->checkAndUpdateCmd('therm_setpoint_end_time', $room['therm_mode_endtime']);
	}
} else {
	log::add("Netatmo", 'debug', "Netatmo  jsonData   " . $jsonData);
}