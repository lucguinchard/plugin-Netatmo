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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function Netatmo_install()
{
	Netatmo_update();
}

function Netatmo_update() 
{
	$clientId = config::byKey('client_id', 'Netatmo');
	if (empty($clientId)) {
		config::save('client_id', $eqLogic->getConfiguration('client_id'), '');
		config::save('client_secret', $eqLogic->getConfiguration('client_secret'), '');
		config::save('username', $eqLogic->getConfiguration('username'), '');
		config::save('password', $eqLogic->getConfiguration('password'), '');
	}
	$accessToken = config::byKey('access_token', 'Netatmo');
	if (empty($accessToken)) {
		config::save('access_token', $eqLogic->getConfiguration('access_token'), '');
		config::save('refresh_token', $eqLogic->getConfiguration('refresh_token'), '');
	}
	foreach (Netatmo::byType('Netatmo') as $eqLogic) {
		$eqLogic->save();
	}
	Netatmo::syncDevice();
}
