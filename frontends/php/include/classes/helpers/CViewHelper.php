<?php
/*
** Zabbix
** Copyright (C) 2001-2019 Zabbix SIA
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
 * A helper class for CView related tasks.
 */
class CViewHelper {

	/**
	 * Generate </a>&nbsp;<sup>num</sup>" to be used in tables. Null is returned if equal to zero.
	 *
	 * @static
	 *
	 * @param int $num
	 *
	 * @return mixed
	 */
	public static function showNum($num) {
		if ($num == 0) {
			return null;
		}

		return [SPACE, new CSup($num)];
	}

	/**
	 * Save web layout mode into user's profile.
	 *
	 * @static
	 *
	 * @param int $layout_mode  ZBX_LAYOUT_NORMAL | ZBX_LAYOUT_FULLSCREEN | ZBX_LAYOUT_KIOSKMODE
	 */
	public static function saveLayoutMode($layout_mode) {
		CProfile::update('web.layout.mode', $layout_mode, PROFILE_TYPE_INT);
	}

	/**
	 * Load web layout mode from user's profile.
	 *
	 * @static
	 *
	 * @return int  Stored web layout mode (ZBX_LAYOUT_NORMAL by default).
	 */
	public static function loadLayoutMode() {
		return (int) CProfile::get('web.layout.mode', ZBX_LAYOUT_NORMAL);
	}
}
