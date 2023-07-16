<?php

/*
 * This file is part of the NextDom software (https://github.com/NextDom or http://nextdom.github.io).
 * Copyright (c) 2018 NextDom.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 2.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__ . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/../../3rdparty/Netatmo-API-PHP/Netatmo/autoload.php';
require_once 'NetatmoCmd.class.php';
require_once 'NetatmoType.class.php';

use Netatmo\Clients\NAApiClient;
use Netatmo\Clients\NAThermApiClient;
use Netatmo\Common\NAScopes;

class Netatmo extends eqLogic {
	/*     * *************************Attributs****************************** */
	private static $_client = null;
	private static $_clientTherm = null;
	private static $_globalConfig = null;
	/*     * ***********************Methode static*************************** */
	public static function getAuth() {
		$client_id = config::byKey('client_id', __CLASS__);
		$client_secret = config::byKey('client_secret', __CLASS__);
		$username = config::byKey('username', __CLASS__);
		$password = config::byKey('password', __CLASS__);
		$accessToken = config::byKey('access_token', __CLASS__);
		$refreshToken = config::byKey('refresh_token', __CLASS__);
		if(empty($client_id) || empty($client_secret) || empty($username) || empty($password)) return null;
		return array(
				'client_id' => $client_id,
				'client_secret' => $client_secret,
				'username' => $username,
				'access_token' => $accessToken,
				'password' => $password,
				'scope' => implode(' ', NAScopes::$validScopes)
		);
		
	}

	public static function getClient() {
		$auth = Netatmo::getAuth();
		if($auth != null) {
			if (Netatmo::$_client == null) {
				Netatmo::$_client = new NAApiClient($auth);
			}
		}
		return Netatmo::$_client;
	}

	public static function getClientTherm() {
		$auth = Netatmo::getAuth();
		if($auth != null) {
			if (Netatmo::$_client == null) {
				Netatmo::$_client = new NAThermApiClient($auth);
			}
		}
		return Netatmo::$_client;
	}

	public static function getGConfig($_key){
		$keys = explode('::',$_key);
		if(Netatmo::$_globalConfig == null){
			Netatmo::$_globalConfig = json_decode(file_get_contents(__DIR__.'/../config/config.json'),true);
		}
		$return = Netatmo::$_globalConfig;
		foreach ($keys as $key) {
			if(!isset($return[$key])){
				return '';
			}
			$return = $return[$key];
		}
		return $return;
	}

	public static function cronDaily() {
		Netatmo::syncDevice('info');
	}

	public static function cron15() {
		Netatmo::syncWeather('info');
	}

	public static function cron5() {
		Netatmo::syncEnergy('info');
	}

	public static function syncDevice($logTry = 'error') {
		log::add(__CLASS__, 'debug', "syncDevice");
		$client = Netatmo::getClient();
		if($client != null) {
			try {
				$homesdata = $client->api("homesdata", "GET", array());
				//log::add(__CLASS__, 'debug', "syncDevice" . json_encode($homesdata));
				$home = $homesdata['homes'][0];
				config::save('home_id', $home['id'], __CLASS__);
				$roomList = [];
				foreach($home['rooms'] as $room) {
					//log::add(__CLASS__, 'debug', "room" . print_r($room, true));
					$roomList[$room['id']] = $room['name'];
				}
				foreach($home['modules'] as $module) {
					$type = $module['type'];
					log::add(__CLASS__, 'info', "Type : " . $type);
					//log::add(__CLASS__, 'debug', "room " . print_r($module, true));
					$eqLogic = eqLogic::byLogicalId($module['id'], __CLASS__);
					if(!isset($module['name']) || $module['name'] == ''){
						$module['name'] = $module['id'];
					}
					if (!is_object($eqLogic)) {
						$eqLogic = new Netatmo();
						$eqLogic->setIsVisible(1);
						$eqLogic->setIsEnable(1);
					}
					if(!empty($module['room_id'])){
						$roomName = $roomList[$module['room_id']];
						$eqLogic->setConfiguration('room_id', $module['room_id']);
					} else {
						$roomName = null;
					}
					if(!empty($module['bridge'])){
						$eqLogic->setConfiguration('bridge', $module['bridge']);
					}
					$jeeObect = jeeObject::byName($roomName);
					if(!empty($jeeObect)) {
						$eqLogic->setObject_id($jeeObect->getId());
					}
					if(!empty($module['name'])) {
						$eqLogic->setName($module['name']);
					} else {
						$eqLogic->setName($type);
					}
					$netatmoType = constant('NetatmoType::'.$type);
					if(!empty($netatmoType)) {
						$eqLogic->setCategory($netatmoType['category'], 1);
					}
					$eqLogic->setEqType_name(__CLASS__);
					$eqLogic->setLogicalId($module['id']);
					$eqLogic->setConfiguration('type', $type);
					$battery_type = Netatmo::getGConfig($module['type'].'::bat_type');
					if(!empty($battery_type)) {
						log::add(__CLASS__, 'debug', "bat_type " . print_r($battery_type, true));
						$eqLogic->setConfiguration('battery_type', $battery_type);
					}
					$eqLogic->save();
				}
				return true;
			} catch (Exception $ex) {
				Netatmo::$_client = null;
				log::add(__CLASS__, $logTry, __('Erreur sur syncDevice Netatmo ', __FILE__) . ' : ' . $ex->getMessage());
				return false;
			}
		}
	}

	public static function syncWeather($logTry = 'error') {
		log::add(__CLASS__, 'debug', "syncWeather");
		$client = Netatmo::getClient();
		if($client != null) {
			try {
				$APIWeather = $client->api("devicelist", "POST", array("app_type" => 'app_station'));
				//log::add(__CLASS__, 'debug', "syncWeather" . json_encode($APIWeather));
				Netatmo::syncModuleList($APIWeather['devices']);
				Netatmo::syncModuleList($APIWeather['modules']);
				return true;
			} catch (Exception $ex) {
				Netatmo::$_client = null;
				log::add(__CLASS__, $logTry, __('Erreur sur syncWeather Netatmo ', __FILE__) . ' : ' . $ex->getMessage());
				return false;
			}
		}
	}

	public static function syncEnergy($logTry = 'error') {
		log::add(__CLASS__, 'debug', "syncEnergy");
		$client = Netatmo::getClientTherm();
		if($client != null) {
			try {
				$home_id = config::byKey('home_id', __CLASS__);
				log::add(__CLASS__, 'debug', "home_id: " . $home_id);
				$APIHomeStatus = $client->api("homestatus", "GET", array("home_id" => $home_id));
				Netatmo::syncModuleList($APIHomeStatus['home']['modules'], "id" , false);

				foreach($APIHomeStatus['home']['rooms'] as $room) {
					log::add(__CLASS__, 'info', 'rooms '.json_encode($room));
					foreach (eqLogic::byType(__CLASS__) as $eqLogic) {
						$type = $eqLogic->getConfiguration('type');
						if($type === "NAPlug") continue;
						if(substr($type, 0, 3) === "NAT") {
							if($room['id'] === $eqLogic->getConfiguration('room_id')) {
								log::add(__CLASS__, 'info', 'room_id '.$eqLogic->getConfiguration('room_id'));
								log::add(__CLASS__, 'info', 'eqLogic '.$eqLogic->getName());
								foreach ($room as $key => $value) {
									//log::add(__CLASS__, 'debug', "dashboard_data" . print_r($dashboard_data, true));
									if ($key == 'therm_measured_temperature') {
										$collectDateInt = $room['therm_setpoint_start_time'];
										$collectDate = date('Y-m-d H:i:s', $collectDateInt);
										$key = "temperature";
									} else {
										$collectDate = null;
									}
									$eqLogic->checkAndUpdateCmd(strtolower($key),$value,$collectDate);
									$cmd = $eqLogic->getCmd(null, $key);
									if(is_object($cmd)) {
										log::add(__CLASS__, 'info', "  Mise à jour de :" . $key . " → " . print_r($value,true));
										//$eqLogic->checkAndUpdateCmd($key, $value); //, dateCollect
									} else {
										log::add(__CLASS__, 'info', "  La méthode suivante n’existe pas :" . $key . " → " .print_r($value,true));
									}
								}
							}
						}

					}
				}
				log::add(__CLASS__, 'info', '$homestatus '.json_encode($APIHomeStatus));
				$devicelist = $client->getData();
				log::add(__CLASS__, 'info', '$devicelist '.json_encode($devicelist));
				Netatmo::syncModuleList($devicelist['devices'][0]['modules'], "_id", false);
				return true;
			} catch (Exception $ex) {
				Netatmo::$_clientTherm = null;
				log::add(__CLASS__, $logTry, __('Erreur sur syncEnergy Netatmo ', __FILE__) . ' : ' . $ex->getMessage());
				return false;
			}
		}
	}

	public static function syncModuleList($moduleList, $id = "_id", $dashboardData = "dashboard_data") {
		if(!empty($moduleList)){
			foreach ($moduleList as $module) {
				log::add(__CLASS__, 'debug', "Traitement : " . print_r($module,true));
				$eqLogic = eqLogic::byLogicalId($module[$id], __CLASS__);
				if (!is_object($eqLogic)) {
					continue;
				}
				log::add(__CLASS__, 'debug', "Traitement : " . $eqLogic->getName());
				if(!empty($module['rf_status'])) {
					$eqLogic->setConfiguration('rf_status', $module['rf_status']);
				}
				if(!empty($module['firmware'])) {
					$eqLogic->setConfiguration('firmware', $module['firmware']);
				}
				if(!empty($module['battery_percent'])) {
					$eqLogic->batteryStatus($module['battery_percent']);
				}
				$eqLogic->save(true);
				if($dashboardData) {
					if(!empty($module[$dashboardData])){
						$dashboard_data = $module[$dashboardData];
					} else {
						$dashboard_data = array();
					}
				} else {
					$dashboard_data = $module;
				}
				foreach ($dashboard_data as $key => $value) {
					if(empty($value)) continue;
					//log::add(__CLASS__, 'debug', "dashboard_data" . print_r($dashboard_data, true));
					$collectDateInt = null;
					if(!empty($dashboard_data['time_utc'])){
						if ($key == 'max_temp') {
							$collectDateInt = $dashboard_data['date_max_temp'];
						} else if ($key == 'min_temp') {
							$collectDateInt = $dashboard_data['date_min_temp'];
						} else if ($key == 'max_wind_str') {
							$collectDateInt = $dashboard_data['date_max_wind_str'];
						} else {
							$collectDateInt = $dashboard_data['time_utc'];
						}
					}
					$collectDate = date('Y-m-d H:i:s', $collectDateInt);
					$eqLogic->checkAndUpdateCmd(strtolower($key),$value,$collectDate);
					$cmd = $eqLogic->getCmd(null, $key);
					if(is_object($cmd)) {
						log::add(__CLASS__, 'info', "  Mise à jour de :" . $key . " → " . print_r($value,true));
						//$eqLogic->checkAndUpdateCmd($key, $value); //, dateCollect
					} else {
						log::add(__CLASS__, 'info', "  La méthode suivante n’existe pas :" . $key . " → " .print_r($value,true));
					}
				}
			}
		}
	}
	
	/*     * *********************Methode d'instance************************* */
	
	public function postSave() {
		if ($this->getConfiguration('applyType') != $this->getConfiguration('type')) {
			$this->applyType();
		}
		$cmd = $this->getCmd(null, 'refresh');
		if (!is_object($cmd)) {
			$cmd = new NetatmoCmd();
			$cmd->setName(__('Rafraichir', __FILE__));
		}
		$cmd->setEqLogic_id($this->getId());
		$cmd->setLogicalId('refresh');
		$cmd->setType('action');
		$cmd->setSubType('other');
		$cmd->save();
	}

	public function applyType(){
		$this->setConfiguration('applyType', $this->getConfiguration('type'));
		$supported_commands = Netatmo::getGConfig($this->getConfiguration('type').'::cmd');
		$commands = array('commands');
		foreach ($supported_commands as $supported_command) {
			$commands['commands'][] = Netatmo::getGConfig('commands::'.$supported_command);
		}
		$this->import($commands);
	}

	public function toHtml($_version = 'dashboard') {
		$replace = $this->preToHtml($_version);
		if (!is_array($replace)) {
			return $replace;
		}
		$version = jeedom::versionAlias($_version);
		if ($this->getDisplay('hideOn' . $version) == 1) {
			return '';
		}
		/* ------------ Ajouter votre code ici ------------ */
		$template = __DIR__ . '/../template/' . $version . '/' . $this->getConfiguration('type') . '.html';
		if (!file_exists($template)) {
			return parent::toHtml($version);
		}
		foreach ($this->getCmd('info') as $cmd) {
			$replace['#' . $cmd->getLogicalId() . '_html#'] = $cmd->toHtml();
			$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
			$replace['#' . $cmd->getLogicalId() . '_name#'] = $cmd->getName();
			$replace['#' . $cmd->getLogicalId() . '#'] = str_replace(array("\'", "'"), array("'", "\'"), $cmd->execCmd());
			$replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
			if ($cmd->getIsHistorized() == 1) {
				$replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
			} else {
				$replace['#' . $cmd->getLogicalId() . '_history#'] = '';
			}
		}

		foreach ($this->getCmd('action') as $cmd) {
			$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
			$replace['#' . $cmd->getLogicalId() . '_html#'] = $cmd->toHtml();
			$replace['#' . $cmd->getLogicalId() . '_name#'] = $cmd->getName();
			if (!empty($cmd->getDisplay('icon'))) {
				$replace['#' . $cmd->getLogicalId() . '_icon#'] = $cmd->getDisplay('icon');
			} else {
				$replace['#' . $cmd->getLogicalId() . '_icon#'] = "<i class='icon divers-vlc1' title='Veuillez mettre une icon à l’action : " . $cmd->getLogicalId() . "'></i>";
			}
		}
		return $this->postToHtml($version, template_replace($replace, getTemplate('core', $version, $this->getConfiguration('type'), __CLASS__)));
	}

	public function getImage() {
		$type = $this->getConfiguration('type');
		if(isset($type)) {
			$url = "plugins/" . __CLASS__ . "/core/img/" . $type .".png";
			if (file_exists($url)) {
				return $url;
			}
		}
		return parent::getImage();
	}
}