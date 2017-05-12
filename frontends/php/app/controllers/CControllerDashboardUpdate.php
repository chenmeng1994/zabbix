<?php
/*
** Zabbix
** Copyright (C) 2001-2017 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

/**
 * Controller to update dashboard
 *
 */
class CControllerDashboardUpdate extends CController
{
	protected function checkInput()
	{
		$fields = [
			'dashboardid' => 'required|db dashboard.dashboardid',
			'private' =>     'db dashboard.private| in 0,1',
			'users' =>       'array',
			'userGroups' =>  'array'

		];

		$ret = $this->validateInput($fields);

		if (!$ret) {
			$this->setResponse(
				new CControllerResponseData([
					'main_block' => CJs::encodeJson(['error' => 'Input data are invalid or don\'t exist!'])
				])
			);
		}

		return $ret;
	}

	protected function checkPermissions() {
		if ($this->getUserType() < USER_TYPE_ZABBIX_USER) {
			return false;
		}

		$dashboards = API::Dashboard()->get([
			'output' => [],
			'dashboardids' => $this->getInput('dashboardid'),
			'editable' => true
		]);
		if (!$dashboards) {
			return false;
		}

		return true;
	}

	protected function doAction()
	{
		$dashboard = ['dashboardid' => $this->getInput('dashboardid')];

		if ($this->hasInput('private')) {
			$dashboard['private'] = $this->getInput('private');
		}
		if ($this->hasInput('users')) {
			$users = $this->getInput('users');
			// indicator to help delete all users
			unset($users['no-users']);
			$dashboard['users'] = array_values($users);
		}
		if ($this->hasInput('userGroups')) {
			$groups = $this->getInput('userGroups');
			// indicator to help delete all user groups
			unset($groups['no-groups']);
			$dashboard['userGroups'] = array_values($groups);
		}

		$result = API::Dashboard()->update([$dashboard]);

		$this->setResponse(
			new CControllerResponseData(
				['main_block' => CJs::encodeJson(['result' => $result])]
			)
		);
	}
}
