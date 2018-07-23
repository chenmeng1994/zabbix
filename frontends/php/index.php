<?php
/*
** Zabbix
** Copyright (C) 2001-2018 Zabbix SIA
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


require_once dirname(__FILE__).'/include/classes/user/CWebUser.php';
CWebUser::disableSessionCookie();

require_once dirname(__FILE__).'/include/config.inc.php';
require_once dirname(__FILE__).'/include/forms.inc.php';

$page['title'] = _('ZABBIX');
$page['file'] = 'index.php';

// VAR	TYPE	OPTIONAL	FLAGS	VALIDATION	EXCEPTION
$fields = [
	'name' =>		[T_ZBX_STR, O_NO,	null,	null,		'isset({enter})', _('Username')],
	'password' =>	[T_ZBX_STR, O_OPT, null,	null,			'isset({enter})'],
	'sessionid' =>	[T_ZBX_STR, O_OPT, null,	null,			null],
	'reconnect' =>	[T_ZBX_INT, O_OPT, P_SYS|P_ACT,	BETWEEN(0, 65535), null],
	'enter' =>		[T_ZBX_STR, O_OPT, P_SYS,	null,			null],
	'autologin' =>	[T_ZBX_INT, O_OPT, null,	null,			null],
	'request' =>	[T_ZBX_STR, O_OPT, null,	null,			null]
];
check_fields($fields);

// logout
if (isset($_REQUEST['reconnect'])) {
	CWebUser::logout();
	redirect('index.php');
}

$config = select_config();

// login via form
if (isset($_REQUEST['enter']) && $_REQUEST['enter'] == _('Sign in')) {
	// try to login
	$autoLogin = getRequest('autologin', 0);

	DBstart();
	$loginSuccess = CWebUser::login(getRequest('name', ''), getRequest('password', ''));
	DBend(true);

	if ($loginSuccess) {
		// save remember login preference
		if (CWebUser::$data['autologin'] != $autoLogin) {
			API::User()->update([
				'userid' => CWebUser::$data['userid'],
				'autologin' => $autoLogin
			]);
		}

		$request = getRequest('request', '');

		if ($request) {
			preg_match('/^\/?(?<filename>[a-z0-9\_\.]+\.php)(?<request>\?.*)?$/i', $request, $test_request);

			$request = (array_key_exists('filename', $test_request) && file_exists('./'.$test_request['filename']))
				? $test_request['filename'].(array_key_exists('request', $test_request) ? $test_request['request'] : '')
				: '';
		}

		if (!zbx_empty($request)) {
			$url = $request;
		}
		elseif (!zbx_empty(CWebUser::$data['url'])) {
			$url = CWebUser::$data['url'];
		}
		else {
			$url = ZBX_DEFAULT_URL;
		}
		redirect($url);
		exit;
	}
	// login failed, fall back to a guest account
	else {
		CWebUser::checkAuthentication(null);
	}
}
else {
	// login the user from the session, if the session id is empty - login as a guest
	CWebUser::checkAuthentication(CWebUser::getSessionCookie());
}

// the user is not logged in, display the login form
if (!CWebUser::$data['alias'] || CWebUser::$data['alias'] == ZBX_GUEST_USER) {
	switch ($config['authentication_type']) {
		case ZBX_AUTH_LDAP:
		case ZBX_AUTH_INTERNAL:
			if (isset($_REQUEST['enter'])) {
				$_REQUEST['autologin'] = getRequest('autologin', 0);
			}

			if ($messages = clear_messages()) {
				$messages = array_pop($messages);
				$_REQUEST['message'] = $messages['message'];
			}

			$data = [
				'http_auth_enabled' => $config['http_auth_enabled'] == ZBX_AUTH_HTTP_ENABLED,
				'http_login_url' => (new CUrl('index_http.php'))->removeArgument('sid')
			];

			$loginForm = new CView('general.login', $data);
			$loginForm->render();
	}
}
else {
	redirect(zbx_empty(CWebUser::$data['url']) ? ZBX_DEFAULT_URL : CWebUser::$data['url']);
}
