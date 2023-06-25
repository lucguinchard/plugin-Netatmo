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
if(!is_null($jsonData) && !empty($jsonData))
{
	$json = json_decode($jsonData, TRUE);
	
	log::add("Netatmo", 'warning', print_r($json, true));
	if (!empty($json['event_type'])) {
		switch ($json['event_type']) {
			case "set_point":
				$eqLogic = eqLogic::byLogicalId($json['home']['modules'][0]['id'], Netatmo);
				$eqLogic->checkAndUpdateCmd('therm_setpoint_temperature', $json['temperature']);
				break;
			case "therm_mode":
				foreach ($eqLogicList as $eqLogic) {
					if($eqLogic->getConfiguration('type')==='NATherm1') {
						$eqLogic->checkAndUpdateCmd('therm_setpoint_mode', $json['mode']);
					}
				}
				break;
			case "cancel_set_point":
				foreach ($eqLogicList as $eqLogic) {
					if($eqLogic->getConfiguration('type')==='NATherm1') {
						$eqLogic->checkAndUpdateCmd('therm_setpoint_mode', 'schedule');
					}
				}
				break;

			default:
				log::add("Netatmo", 'warning', "event_type non géré : " . $json['event_type']);
		}
	} else {
		log::add("Netatmo", 'info', 'Il n’y a pas de event_type');
	}
}