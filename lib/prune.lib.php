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
 *      \ingroup    oauth
 *      \brief      Dolibarr token storage functions
 */


/**
 * Function retrieveAccessToken
 *
 * @param string $service service
 * @param string $userid user id
 * @return Token
 */
function retrieveAccessToken($service, $userid)
{
	global $conf, $db;
	// get from db
	dol_syslog("retrieveAccessToken service=".$service);
	$sql = "SELECT token, refreshtoken FROM ".MAIN_DB_PREFIX."prune_oauth_token";
	$sql .= " WHERE service='".$db->escape($service)."'";
	$sql .= " AND fk_user=".(int) $userid." AND entity=".(int) $conf->entity;

	$resql = $db->query($sql);
	if (! $resql) {
		dol_print_error($db);
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
 * @return string
 */
function retrieveRefreshTokenBackup($service, $userid)
{
	global $conf, $db;
	// get from db

	$sql = "SELECT token, refreshtoken FROM ".MAIN_DB_PREFIX."prune_oauth_token";
	$sql .= " WHERE service='".$db->escape($service)."'";
	$sql .= " AND fk_user=".(int) $userid." AND entity=".(int) $conf->entity;

	$resql = $db->query($sql);
	if (! $resql) {
		dol_print_error($db);
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
 * @return void
 */
function storeAccessToken($service, $token, $refreshtoken, $userid)
{
	global $conf, $db;

	dol_syslog("storeAccessToken");

	$serializedToken = serialize($token);

	$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."prune_oauth_token";
	$sql .= " WHERE service='".$db->escape($service)."' AND entity=".(int) $conf->entity;
	$sql .=  " AND fk_user=".(int) $userid;
	$resql = $db->query($sql);
	if (! $resql) {
		dol_print_error($db);
	}
	$obj = $db->fetch_array($resql);
	if ($obj) {
		// update
		$sql = "UPDATE ".MAIN_DB_PREFIX."prune_oauth_token";
		$sql.= " SET token='".$db->escape($serializedToken)."'";
		$sql.= ", refreshtoken='".$db->escape($refreshtoken)."'";
		$sql.= " WHERE rowid='".$obj['rowid']."'";

		$resql = $db->query($sql);
	} else {
		// save
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."prune_oauth_token (service, token, refreshtoken, fk_user, entity)";
		$sql.= " VALUES ('".$db->escape($service)."', '".$db->escape($serializedToken)."', '".$db->escape($refreshtoken)."', ".(int) $userid.", ".(int) $conf->entity.")";

		$resql = $db->query($sql);
	}
}

/**
 * Function clearToken
 * @param string $service service
 * @param string $userid user id
 * @return void
 */
function clearToken($service, $userid)
{
	global $conf, $db;

	$sql = "DELETE FROM ".MAIN_DB_PREFIX."prune_oauth_token";
	$sql.= " WHERE service='".$db->escape($service)."'";
	$sql .= " AND fk_user=".(int) $userid." AND entity=".(int) $conf->entity;
	$resql = $db->query($sql);
	return $resql;
}