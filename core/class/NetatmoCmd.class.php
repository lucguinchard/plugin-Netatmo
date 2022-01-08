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

require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once 'Netatmo.class.php';

class NetatmoCmd extends cmd {
	/*     * *************************Attributs****************************** */


	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */

	/*
	 * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
	  public function dontRemoveCmd() {
	  return true;
	  }
	 */

	public function execute($_options = array()) {
		if ($this->getType() == 'info') {
			return;
		}

		$eqLogic = $this->getEqLogic();
		$module_id = $eqLogic->getLogicalId();
		$bridge = $eqLogic->getConfiguration('bridge');
		try {
			switch ($this->getLogicalId()) {
				case 'power_off' :
						Netatmo::getClientTherm()->turnOff($bridge, $module_id);
					break;
				case 'power_to_away_mode' :
						$endtime = null;
						Netatmo::getClientTherm()->setToAwayMode($bridge, $module_id, $endtime);
					break;
				case 'power_to_frost_guard_mode' :
						$endtime = null;
						Netatmo::getClientTherm()->setToFrostGuardMode($bridge, $module_id, $endtime);
					break;
				case 'power_to_program_mode' :
						Netatmo::getClientTherm()->setToProgramMode($bridge, $module_id);
					break;
				case 'power_to_manual_mode' :
						$endtime = null;
						$setpoint = 15;
						Netatmo::getClientTherm()->setToManualMode($bridge, $module_id, $setpoint, $endtime);
					break;
				case 'refresh' :
					Netatmo::syncWeather();
					Netatmo::syncEnergy();
					break;
				case 'therm_setpoint_temperature_up' :
					$cmd = $eqLogic->getCmd(null, "therm_setpoint_temperature");
					if(is_object($cmd)) {
						$endtime = null;
						$setpoint = $cmd->execCmd();
						$setpoint += 0.5;
						Netatmo::getClientTherm()->setToManualMode($bridge, $module_id, $setpoint, $endtime);
					}
					break;
				case 'therm_setpoint_temperature_down' :
					$cmd = $eqLogic->getCmd(null, "therm_setpoint_temperature");
					if(is_object($cmd)) {
						$endtime = null;
						$setpoint = $cmd->execCmd();
						$setpoint -= 0.5;
						Netatmo::getClientTherm()->setToManualMode($bridge, $module_id, $setpoint, $endtime);
					}
					break;
				default : 
					log::add("Netatmo", 'warning', 'TODO:Créer la commande ' . $this->getLogicalId());
			}
		} catch (Exception $ex) {
			log::add("Netatmo", 'error', __('Erreur sur la commande ', __FILE__) . $this->getLogicalId() .' : ' . $ex->getMessage());
		}
	}

	/*     * **********************Getteur Setteur*************************** */
}
