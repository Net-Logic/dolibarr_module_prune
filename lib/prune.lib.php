<?php
/*
 * Copyright (C) 2015-2021  Frédéric France      <frederic.france@free.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *      \file       htdocs/prune/lib/prune.lib.php
 *      \ingroup    prune
 *      \brief      Dolibarr prune functions
 */

dol_include_once('/prune/vendor/autoload.php');

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;

if (!function_exists('getDolGlobalString')) {
	/**
	 * Return dolibarr global constant string value
	 * @param string $key key to return value, return '' if not set
	 * @param string $default value to return
	 * @return string
	 */
	function getDolGlobalString($key, $default = '')
	{
		global $conf;
		// return $conf->global->$key ?? $default;
		return (string) ($conf->global->$key ?? $default);
	}
}

if (!function_exists('getDolGlobalInt')) {
	/**
	 * Return dolibarr global constant int value
	 * @param string $key key to return value, return 0 if not set
	 * @param int $default value to return
	 * @return int
	 */
	function getDolGlobalInt($key, $default = 0)
	{
		global $conf;
		// return $conf->global->$key ?? $default;
		return (int) ($conf->global->$key ?? $default);
	}
}

/**
 * Prepare admin pages header
 *
 * @return array
 */
function pruneAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("prune@prune");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/prune/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("PruneSettings");
	$head[$h][2] = 'settings';
	$h++;
	$head[$h][0] = dol_buildpath("/prune/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	complete_head_from_modules($conf, $langs, null, $head, $h, 'pruneadmin');

	return $head;
}

/**
 * getPruneCache
 *
 * @param string $namespace     a string prefixed to the keys of the items stored in this cache
 * @param int $defaultLifetime  the default lifetime (in seconds) for cache items that do not define their
 *                              own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
 *                              until the files are deleted)
 * @return Symfony\Component\Cache\Adapter\AbstractAdapter
 */
function getPruneCache($namespace = '', $defaultLifetime = 0)
{
	$typeCache = getDolGlobalInt('PRUNE_CACHE_TYPE');
	if ($typeCache == 2) {
		$client = MemcachedAdapter::createConnection([
			'memcached://127.0.0.1:11211',
			// 'memcached://10.0.0.101',
			// 'memcached://10.0.0.102',
			// etc...
		]);
		$cache = new MemcachedAdapter(
			// the client object that sets options and adds the server instance(s)
			$client,
			// a string prefixed to the keys of the items stored in this cache
			$namespace,
			// the default lifetime (in seconds) for cache items that do not define their
			// own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
			// until MemcachedAdapter::clear() is invoked or the server(s) are restarted)
			$defaultLifetime
		);
	} else {
		$cache = new FilesystemAdapter(
			// a string used as the subdirectory of the root cache directory, where cache
			// items will be stored
			$namespace,
			// the default lifetime (in seconds) for cache items that do not define their
			// own lifetime, with a value 0 causing items to be stored indefinitely (i.e.
			// until the files are deleted)
			$defaultLifetime,
			// the main cache directory (the application needs read-write permissions on it)
			// if none is specified, a directory is created inside the system temporary directory
			DOL_DATA_ROOT . '/prune/temp'
		);
	}
	return $cache;
}

/**
 * Function retrieveAccessToken
 *
 * @param string $service service
 * @param string $userid user id
 * @param string $email email
 * @return Token
 */
function retrieveAccessToken($service, $userid, $email = null)
{
	global $conf, $db;
	// get from db
	dol_syslog("retrieveAccessToken service=" . $service);
	$sql = "SELECT token, refreshtoken, email FROM " . MAIN_DB_PREFIX . "prune_oauth_token";
	$sql .= " WHERE service='" . $db->escape($service) . "'";
	$sql .= " AND entity=" . (int) $conf->entity;
	$sql .= " AND fk_user=" . (int) $userid;
	// if we don't have a userid, we use the email field (if not null)
	if (!empty($email)) {
		$sql .= " AND email='" . $db->escape($email) . "'";
	}

	$resql = $db->query($sql);
	if (!$resql) {
		dol_syslog("lib prune retrieveAccessToken error = " . $db->lasterror, LOG_ERR);
	}
	$result = $db->fetch_array($resql);
	$token = unserialize($result['token']);

	return $token;
}

/**
 * Function retrieveRefreshTokenBackup
 *
 * @param string $service service
 * @param string $userid user id
 * @param string $email email
 * @return string
 */
function retrieveRefreshTokenBackup($service, $userid, $email = null)
{
	global $conf, $db;
	// get from db

	$sql = "SELECT token, refreshtoken FROM " . MAIN_DB_PREFIX . "prune_oauth_token";
	$sql .= " WHERE service='" . $db->escape($service) . "'";
	$sql .= " AND fk_user=" . (int) $userid . " AND entity=" . (int) $conf->entity;

	$resql = $db->query($sql);
	if (!$resql) {
		dol_syslog("lib prune retrieveRefreshToken error = " . $db->lasterror, LOG_ERR);
	}
	$result = $db->fetch_array($resql);
	$tokenrefreshbackup = $result['refreshtoken'] ?? '';

	return $tokenrefreshbackup;
}

/**
 * Function storeAccessToken
 *
 * @param string $service service
 * @param string $token token
 * @param string $refreshtoken refreshtoken backup
 * @param string $userid user id
 * @param string $email email
 * @return boolean
 */
function storeAccessToken($service, $token, $refreshtoken, $userid, $email = null)
{
	global $conf, $db;

	dol_syslog("storeAccessToken");

	$serializedToken = serialize($token);

	$sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "prune_oauth_token";
	$sql .= " WHERE service='" . $db->escape($service) . "' AND entity=" . (int) $conf->entity;
	$sql .=  " AND fk_user=" . (int) $userid;
	if (!empty($email)) {
		$sql .=  " AND email='" . $db->escape($email) . "'";
	}
	$resql = $db->query($sql);
	if (!$resql) {
		dol_syslog("lib prune storeAccessToken error = " . $db->lasterror, LOG_ERR);
	}
	$obj = $db->fetch_array($resql);
	if ($obj) {
		// update
		$sql = "UPDATE " . MAIN_DB_PREFIX . "prune_oauth_token";
		$sql .= " SET token='" . $db->escape($serializedToken) . "'";
		$sql .= ", refreshtoken='" . $db->escape($refreshtoken) . "'";
		$sql .= " WHERE rowid='" . (int) $obj['rowid'] . "'";

		$resql = $db->query($sql);
		if (!$resql) {
			dol_syslog("lib prune storeAccessToken error = " . $db->lasterror, LOG_ERR);
		}
	} else {
		// save
		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "prune_oauth_token (service, token, refreshtoken, fk_user, email, entity)";
		$sql .= " VALUES ('" . $db->escape($service) . "', '" . $db->escape($serializedToken) . "', '" . $db->escape($refreshtoken) . "', ";
		$sql .= (int) $userid . ", " . (empty($email) ? "null" : "'" . $db->escape($email) . "'") . ", " . (int) $conf->entity . ")";

		$resql = $db->query($sql);
		if (!$resql) {
			dol_syslog("lib prune storeAccessToken error = " . $db->lasterror, LOG_ERR);
		}
	}
	return (!$resql ? false : true);
}

/**
 * Function clearToken
 *
 * @param string $service service
 * @param string $userid user id
 * @param string $email email
 * @return boolean
 */
function clearToken($service, $userid, $email = null)
{
	global $conf, $db;

	$sql = "DELETE FROM " . MAIN_DB_PREFIX . "prune_oauth_token";
	$sql .= " WHERE service='" . $db->escape($service) . "'";
	$sql .= " AND fk_user=" . (int) $userid . " AND entity=" . (int) $conf->entity;
	if (!empty($email)) {
		$sql .= " AND email='" . $db->escape($email) . "'";
	}
	$resql = $db->query($sql);
	if (!$resql) {
		dol_syslog("lib prune clearToken error = " . $db->lasterror, LOG_ERR);
	}
	return (!$resql ? false : true);
}
