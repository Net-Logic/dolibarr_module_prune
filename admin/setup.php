<?php
/* Copyright (C) 2004-2017  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2019-2020  Frédéric France         <frederic.france@netlogic.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    htdocs/modulebuilder/template/admin/setup.php
 * \ingroup prune
 * \brief   Prune setup page.
 */

// Load Dolibarr environment
include '../config.php';

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
//require_once DOL_DOCUMENT_ROOT.'/core/lib/oauth.lib.php';
require_once '../lib/prune.lib.php';

// Translations
$langs->loadLangs(array("admin", 'oauth', "prune@prune"));

// Access control
if (!$user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

$arrayofparameters = array(
	'PRUNE_CACHE_TYPE' => array(
		'css' => 'minwidth300',
		'type' => 'select',
		'choices' => [
			1 => 'FileSystem',
			2 => 'Memcache',
		],
		'enabled' => 1,
	),
	// 'PRUNE_ID' => array(
	// 	'css' => 'minwidth500',
	// 	'type' => 'text',
	// 	'enabled' => 1,
	// ),
	// 'PRUNE_SECRET' => array(
	// 	'css' => 'minwidth500',
	// 	'type' => 'password',
	// 	'enabled' => 1,
	// ),
	// 'PRUNE_MYPARAM2' => array(
	//     'css' => 'minwidth500',
	//     'type' => 'text',
	//     'enabled' => 1,
	// )
);

// Paramètres ON/OFF PRUNE_ est rajouté au paramètre
$modules = array(
	'ENABLE_PRUNE_CACHE' => 'PruneEnableCache',
	// 'ENABLE_EXTRAFIELDS_DEBUG' => 'PruneEnableExtrafieldsDebug',
	'ENABLE_DEVELOPPER_MODE' => 'PruneEnableDevelopperMode',
);

/*
 * Actions
 */
foreach ($modules as $const => $desc) {
	if ($action == 'activate_' . strtolower($const)) {
		dolibarr_set_const($db, "PRUNE_" . $const, "1", 'chaine', 0, $langs->trans($desc), $conf->entity);
	}
	if ($action == 'disable_' . strtolower($const)) {
		dolibarr_del_const($db, "PRUNE_" . $const, $conf->entity);
		//header("Location: ".$_SERVER["PHP_SELF"]);
		//exit;
	}
}
if ($action == 'update') {
	$error = 0;
	$db->begin();
	foreach ($arrayofparameters as $key => $val) {
		$result = dolibarr_set_const($db, $key, GETPOST($key, 'alpha'), 'chaine', 0, '', $conf->entity);
		if ($result < 0) {
			$error++;
			break;
		}
	}
	if (!$error) {
		$db->commit();
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	} else {
		$db->rollback();
		setEventMessages($langs->trans("SetupNotSaved"), null, 'errors');
	}
}

/*
 * View
 */

llxHeader();

$form = new Form($db);

$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1">';
$linkback .= $langs->trans("BackToModuleList") . '</a>';
print load_fiche_titre($langs->trans('PruneConfig'), $linkback, 'object_prune-32x32@prune');

$head = pruneAdminPrepareHead();

dol_fiche_head($head, 'settings', '', -1, 'technic');

if ($action == 'edit') {
	print '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="update">';

	//print $langs->trans("ListOfSupportedOauthProviders").'<br><br>';

	print '<table class="noborder centpercent">';
	print '<tr class="liste_titre"><td class="titlefield">' . $langs->trans("Parameter") . '</td><td>' . $langs->trans("Value") . '</td></tr>';

	foreach ($arrayofparameters as $key => $val) {
		print '<tr class="oddeven">';
		print '<td>';
		$tooltiphelp = (($langs->trans($key . 'Tooltip') != $key . 'Tooltip') ? $langs->trans($key . 'Tooltip') : '');
		print $form->textwithpicto($langs->trans($key), $tooltiphelp);
		$type = $val['type'] ?? 'text';
		$value = getDolGlobalString($key, ($val['default'] ?? ''));
		print '</td>';
		if ($type == 'select') {
			print '<td><select name="' . $key . '" class="flat ' . ($val['css'] ?? 'minwidth200') . '">';
			print '<option value="">--Please choose an option--</option>';
			foreach ($val['choices'] as $keychoice => $valuechoice) {
				print '<option value="'.$keychoice.'"'.($value == $keychoice ? ' selected' : '').'>'.$valuechoice.'</option>';
			}
			print '</select></td>';
		} else {
			print '<td><input name="' . $key . '" type="' . $type . '" class="flat ' . ($val['css'] ?? 'minwidth200') . '" value="' . $value . '"></td>';
		}
		print '</tr>';
	}
	print '</table>';

	print '<br><div class="center">';
	print '<input class="button" type="submit" value="' . $langs->trans("Save") . '">';
	print '</div>';

	print '</form>';
	print '<br>';
} else {
	print '<table class="noborder centpercent">';

	print '<tr class="liste_titre">';
	print '<td class="titlefield">' . $langs->trans("Parameter") . '</td>';
	print '<td>' . $langs->trans("Value") . '</td></tr>';

	foreach ($arrayofparameters as $key => $val) {
		print '<tr class="oddeven"><td>';
		$tooltiphelp = (($langs->trans($key . 'Tooltip') != $key . 'Tooltip') ? $langs->trans($key . 'Tooltip') : '');
		print $form->textwithpicto($langs->trans($key), $tooltiphelp);
		print '</td><td>';
		$value = getDolGlobalString($key);
		if (isset($val['type']) && $val['type'] == 'password') {
			$value = preg_replace('/./i', '*', $value);
		}
		if (isset($val['type']) && $val['type'] == 'select') {
			$value = $val['choices'][$value] ?? '';
		}
		print $value;
		print '</td></tr>';
	}

	print '</table>';

	print '<div class="tabsAction">';
	print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?action=edit">' . $langs->trans("Modify") . '</a>';
	print '</div>';
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>' . $langs->trans("Paramètres Divers") . '</td>';
	print '<td align="center" width="100">' . $langs->trans("Action") . '</td>';
	print "</tr>\n";
	// Modules
	foreach ($modules as $const => $desc) {
		print '<tr class="oddeven">';
		print '<td>' . $langs->trans($desc) . '</td>';
		print '<td align="center" width="100">';
		$constante = 'PRUNE_' . $const;
		$value = (isset($conf->global->$constante) ? $conf->global->$constante : 0);
		if ($value == 0) {
			print '<a href="' . $_SERVER['PHP_SELF'] . '?action=activate_' . strtolower($const) . '&amp;token=' . $_SESSION['newtoken'] . '">';
			print img_picto($langs->trans("Disabled"), 'switch_off');
			print '</a>';
		} elseif ($value == 1) {
			print '<a href="' . $_SERVER['PHP_SELF'] . '?action=disable_' . strtolower($const) . '&amp;token=' . $_SESSION['newtoken'] . '">';
			print img_picto($langs->trans("Enabled"), 'switch_on');
			print '</a>';
		}
		print "</td>";
		print '</tr>';
	}
	print '</table>' . PHP_EOL;
	print '<br>' . PHP_EOL;
}

print dol_get_fiche_end();

// End of page
llxFooter();
$db->close();
