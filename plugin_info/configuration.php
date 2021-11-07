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

include_file('core', 'authentification', 'php');

if (!isConnect()) {
	include_file('desktop', '404', 'php');
	die();
}
?>
<form class="form-horizontal">
	<fieldset>
		<div class="form-group">
			<label class="col-lg-4 control-label">{{Client ID}}</label>
			<div class="col-lg-4">
				<input type="text" class="configKey form-control" data-l1key="client_id" placeholder="Client ID"/>
			</div>
		</div>
		<div class="form-group">
			<label class="col-lg-4 control-label">{{Client secret}}</label>
			<div class="col-lg-4">
				<input type="text" class="configKey form-control" data-l1key="client_secret" placeholder="Client Secret"/>
			</div>
		</div>
		<div class="form-group">
			<label class="col-lg-4 control-label">{{Nom d’utilisateur}}</label>
			<div class="col-lg-4">
				<input type="text" class="configKey form-control" data-l1key="username" placeholder="Nom d’utilisateur"/>
			</div>
		</div>
		<div class="form-group">
			<label class="col-lg-4 control-label">{{Mot de passe}}</label>
			<div class="col-lg-4">
				<input type="password" class="configKey form-control" data-l1key="password" placeholder="Mot de passe"/>
			</div>
		</div>
	</fieldset>
</form>
