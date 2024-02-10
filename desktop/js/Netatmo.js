
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

$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

$('#bt_healthNetatmo').on('click', function () {
	$('#md_modal').dialog({title: "{{Santé Netatmo}}"});
	$('#md_modal').load('index.php?v=d&plugin=Netatmo&modal=health').dialog('open');
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=type]').on('change', function () {
	$('#img_netatmoModel').attr('src', 'plugins/Netatmo/core/img/' + $(this).value() + '.png');
	if ($(this).value() === 'station') {
		$('#battery_net_weather').hide();
	} else {
		$('#battery_net_weather').show();
	}
});

$('.eqLogicAction[data-action=syncDevice]').on('click', function () {
	$.ajax({
		type: "POST",
		url: "plugins/Netatmo/core/ajax/Netatmo.ajax.php",
		data: {
			action: "sync-device"
		},
		dataType: 'json',
		error: function (request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function (data) {
			if (data.state !== 'ok') {
				$('#div_alert').showAlert({message: data.result, level: 'danger'});
				return;
			} else {
				$('#div_alert').showAlert({message: data.result, level: 'success'});
			}
			modifyWithoutSave = false;
			var vars = getUrlVars();
			var url = 'index.php?';
			for (var i in vars) {
				if (i !== 'id' && i !== 'saveSuccessFull' && i !== 'removeSuccessFull') {
					url += i + '=' + vars[i].replace('#', '') + '&';
				}
			}
			url += 'id=' + data.id + '&saveSuccessFull=1';
			if (document.location.toString().match('#')) {
				url += '#' + document.location.toString().split('#')[1];
			}
			jeedomUtils.loadPage(url);
		}
	});
});

$('.eqLogicAction[data-action=syncWeather]').on('click', function () {
	$.ajax({
		type: "POST",
		url: "plugins/Netatmo/core/ajax/Netatmo.ajax.php",
		data: {
			action: "sync-weather"
		},
		dataType: 'json',
		error: function (request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function (data) {
			if (data.state !== 'ok') {
				$('#div_alert').showAlert({message: data.result, level: 'danger'});
				return;
			} else {
				$('#div_alert').showAlert({message: data.result, level: 'success'});
			}
			modifyWithoutSave = false;
			var vars = getUrlVars();
			var url = 'index.php?';
			for (var i in vars) {
				if (i !== 'id' && i !== 'saveSuccessFull' && i !== 'removeSuccessFull') {
					url += i + '=' + vars[i].replace('#', '') + '&';
				}
			}
			url += 'id=' + data.id + '&saveSuccessFull=1';
			if (document.location.toString().match('#')) {
				url += '#' + document.location.toString().split('#')[1];
			}
			jeedomUtils.loadPage(url);
		}
	});
});

$('.eqLogicAction[data-action=syncEnergy]').on('click', function () {
	$.ajax({
		type: "POST",
		url: "plugins/Netatmo/core/ajax/Netatmo.ajax.php",
		data: {
			action: "sync-energy"
		},
		dataType: 'json',
		error: function (request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function (data) {
			if (data.state !== 'ok') {
				$('#div_alert').showAlert({message: data.result, level: 'danger'});
				return;
			} else {
				$('#div_alert').showAlert({message: data.result, level: 'success'});
			}
			modifyWithoutSave = false;
			var vars = getUrlVars();
			var url = 'index.php?';
			for (var i in vars) {
				if (i !== 'id' && i !== 'saveSuccessFull' && i !== 'removeSuccessFull') {
					url += i + '=' + vars[i].replace('#', '') + '&';
				}
			}
			url += 'id=' + data.id + '&saveSuccessFull=1';
			if (document.location.toString().match('#')) {
				url += '#' + document.location.toString().split('#')[1];
			}
			jeedomUtils.loadPage(url);
		}
	});
});

function addCmdToTable(_cmd) {
	if (!isset(_cmd)) {
		var _cmd = {configuration: {}};
	}
	if (!isset(_cmd.configuration)) {
		_cmd.configuration = {};
	}
	var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
	tr += '<td>';
	tr += '<input class="cmdAttr form-control input-sm" data-l1key="logicalId">';
	tr += '<input class="cmdAttr form-control input-sm" data-l1key="type" style="display : none;">';
	tr += '<input class="cmdAttr form-control input-sm" data-l1key="subType" style="display : none;">';
	tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}"></td>';
	tr += '<input class="cmdAttr form-control type input-sm" data-l1key="type" value="info" disabled style="display : none;" />';
	tr += '<td>';
	tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;display:inline-block;">';
	tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;display:inline-block;">';
	tr += '<input class="cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;display:inline-block;margin-left:2px;">';
	tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
	tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
	tr += '</td>';
	tr += '<td>';
	if (is_numeric(_cmd.id)) {
		tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fa fa-cogs"></i></a> ';
		tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
	}
	tr += '</tr>';
	$('#table_cmd tbody').append(tr);
	$('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
	if (isset(_cmd.type)) {
		$('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
	}
	jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}
