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

if (!isConnect('admin')) {
	throw new Exception('401 Unauthorized');
}
$eqLogicList = eqLogic::byType('Netatmo');
?>

<table class="table table-condensed tablesorter" id="table_healthNetatmo">
	<thead>
		<tr>
			<th>{{Image}}</th>
			<th>{{Module}}</th>
			<th>{{ID}}</th>
			<th>{{Batterie}}</th>
			<th>{{Serial}}</th>
			<th>{{Firmware}}</th>
			<th>{{Wifi}}</th>
			<th>{{RF}}</th>
			<th>{{Date création}}</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($eqLogicList as $eqLogic) {
		?>
		<tr><td>
			<img src="<?= $eqLogic->getImage() ?>" height="65" width="55" />
			</td>
				<td><a href="<?= $eqLogic->getLinkToConfiguration() ?>" style="text-decoration: none;"><?= $eqLogic->getHumanName(true, true) ?></a></td>
				<td><span class="label label-info" style="font-size : 1em;"><?= $eqLogic->getId() ?></span></td>
			<?php
			$battery = $eqLogic->getStatus('battery');
			if(trim($battery) == ''){
				$battery_status = '<span class="label label-primary" style="font-size : 1em;" title="{{Secteur}}"><i class="fa fa-plug"></i></span>';
			}else if ($battery < 20) {
				$battery_status = '<span class="label label-danger" style="font-size : 1em;">' . $battery . '%</span>';
			} elseif ($battery < 60) {
				$battery_status = '<span class="label label-warning" style="font-size : 1em;">' . $battery . '%</span>';
			} elseif ($battery >= 60) {
				$battery_status = '<span class="label label-success" style="font-size : 1em;">' . $battery . '%</span>';
			}
			$firmware_revision_cmd = $eqLogic->getCmd(null, 'firmware_revision');
			if(is_object($firmware_revision_cmd)) {
				$firmware_revision = $firmware_revision_cmd->execCmd();
			} else {
				$firmware_revision = "";
			}
			?>
			<td><?= $battery_status ?></td>
			<td><span class="label label-info" style="font-size : 1em;"><?= $eqLogic->getLogicalId() ?></span></td>
			<td><span class="label label-info" style="font-size : 1em;"><?= $firmware_revision ?></span></td>
			<td><span class="label label-info" style="font-size : 1em;"><?= $eqLogic->getConfiguration('wifi_status') ?></span></td>
			<td><span class="label label-info" style="font-size : 1em;"><?= $eqLogic->getConfiguration('rf_status') ?></span></td>
			<td><span class="label label-info" style="font-size : 1em;"><?= $eqLogic->getConfiguration('createtime') ?></span></td></tr>
		<?php } ?>
	</tbody>
</table>
