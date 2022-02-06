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
	throw new Exception('{{401 - Accès non autorisé}}');
}

$pluginName = init('m');
$plugin = plugin::byId($pluginName);
sendVarToJS('eqType', $plugin->getId());
$eqLogicList = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fa fa-cog"></i> {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoPrimary" data-action="gotoPluginConf">
				<i class="fa fa-wrench"></i>
				<br>
				<span>{{Configuration}}</span>
			</div>
			<div class="cursor eqLogicAction logoSecondary" data-action="syncDevice">
				<i class="fa fa-sync"></i>
				<br>
				<span>{{Synchroniser devices}}</span>
			</div>
			<div class="cursor eqLogicAction logoSecondary" data-action="syncWeather">
				<i class="fa fa-sync" style="font-size: 10px !important;position: absolute; top: 10px;right: 50px;"></i>
				<img src="plugins/Netatmo/desktop/img/weather.jpeg" style="padding-top: 0; width: 50px !important;"/>
				<br>
				<span>{{Synchroniser Weather}}</span>
			</div>
			<div class="cursor eqLogicAction logoSecondary" data-action="syncEnergy">
				<i class="fa fa-sync" style="font-size: 10px !important;position: absolute; top: 10px;right: 40px;"></i>
				<img src="plugins/Netatmo/desktop/img/energy.jpeg" style="padding-top: 0; width: 50px !important;"/>
				<br>
				<span>{{Synchroniser Énergie}}</span>
			</div>
			<?php if (count($eqLogicList) != 0) { ?>
			<div class="cursor logoSecondary" id="bt_healthNetatmo">
				<i class="fa fa-medkit"></i>
				<br>
				<span>{{Santé}}</span>
			</div>
			<?php } ?>
		</div>
		<legend><img style="width:40px" src="<?= $plugin->getPathImgIcon() ?>"/> {{Mes appareils}}</legend>
		<?php if (count($eqLogicList) == 0) { ?>
			<center>
				<span style='color:#767676;font-size:1.2em;font-weight: bold;'>{{Vous n’avez pas encore d’appareil, cliquez sur configuration et cliquez sur synchroniser pour commencer}}</span>
			</center>
		<?php } else { ?>
			<div class="input-group" style="margin:5px;">
				<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
				<div class="input-group-btn">
					<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>
					<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>
				</div>
			</div>
			<div class="eqLogicThumbnailContainer">
				<?php
				foreach ($eqLogicList as $eqLogic) {
					$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard'; ?>
					<div class="eqLogicDisplayCard cursor <?= $opacity ?>" data-eqLogic_id="<?= $eqLogic->getId() ?>">
					<img src="<?= $eqLogic->getImage() ?>" style="max-width: 100px !important;width: auto !important;max-height: 100px !important;min-height: auto !important;"/>
					<br/>
					<span class="name"><?= $eqLogic->getHumanName(true, true) ?></span>
					</div>
				<?php
				}
				?>
			</div>
		<?php } ?>
	</div>

	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span></a>
				<a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
				<a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i><span class="hidden-xs"> {{Supprimer}}</span></a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation">
				<a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab"
					data-action="returnToThumbnailDisplay">
					<i class="fa fa-arrow-circle-left"></i>
				</a>
			</li>
			<li role="presentation" class="active">
				<a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab">
					<i class="fas fa-tachometer-alt"></i> {{Equipement}}
				</a>
			</li>
			<li role="presentation">
				<a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab">
					<i class="fa fa-list-alt"></i> {{Commande}}
				</a>
			</li>
		</ul>
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br/>
				<div class="row">
					<div class="col-sm-6">
						<form class="form-horizontal">
							<fieldset>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{Nom de l’équipement Netatmo}}</label>
									<div class="col-sm-6">
										<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
										<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l’équipement Netatmo}}"/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label" >{{Objet parent}}</label>
									<div class="col-sm-6">
										<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
											<option value="">{{Aucun}}</option>
											<?php foreach (jeeObject::all() as $object) { ?>
												<option value="<?= $object->getId() ?>"><?= $object->getName() ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label"></label>
									<div class="col-sm-8">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{Type}}</label>
									<div class="col-sm-6">
										<select disabled class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="type">
											<option value="NAMain">{{Station}}</option>
											<option value="NAModule1">{{Module extérieur}}</option>
											<option value="NAModule2">{{Anémomètre}}</option>
											<option value="NAModule3">{{Module pluie}}</option>
											<option value="NAModule4">{{Module intérieur}}</option>
											<option value="NATherm1">{{Thermostat intelligent}}</option>
											<option value="NATherm2">{{Thermostat modulant intelligent}}</option>
											<option value="NAPlug">{{Relais Thermostat}}</option>
											<option value="NRV">{{Tête thermostatique}}</option>
										</select>
									</div>
								</div>
							</fieldset>
						</form>
					</div>
					<div class="col-sm-6">
						<form class="form-horizontal">
							<fieldset>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{Identifiant}}</label>
									<div class="col-sm-6">
										<span class="eqLogicAttr label label-info" style="font-size:1em;" data-l1key="logicalId"></span>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{Firmware}}</label>
									<div class="col-sm-6">
										<span class="eqLogicAttr label label-info" style="font-size:1em;" data-l1key="configuration" data-l2key="firmware"></span>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{Réception réseaux}}</label>
									<div class="col-sm-6">
										<span class="label label-info" style="font-size:1em;">
											<span class="eqLogicAttr" data-l1key="configuration" data-l2key="wifi_status"></span>
											<span class="eqLogicAttr" data-l1key="configuration" data-l2key="rf_status"></span>
										</span>
									</div>
								</div>
							</fieldset>
						</form>
						<center>
							<img src="<?php echo $plugin->getPathImgIcon(); ?>" id="img_netatmoModel" style="height : 250px;margin-top : 60px" />
						</center>
					</div>
				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="commandtab">
				<div class="table-responsive">
					<table id="table_cmd" class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th style="width: 200px;">{{Nom}}</th>
								<th>{{Type}}</th>
								<th style="width: 150px;">{{Action}}</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<?php 
include_file('desktop', $pluginName, 'js', $pluginName);
include_file('core', 'plugin.template', 'js');