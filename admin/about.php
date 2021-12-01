<?php
/* Copyright (C) 2004-2017  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2019-2021  Frédéric France         <frederic.france@netlogic.fr>
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
 * \file    prune/admin/about.php
 * \ingroup prune
 * \brief   About page of module Prune.
 */

// Load Dolibarr environment
include '../config.php';

// Libraries
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once '../lib/prune.lib.php';

// Translations
$langs->loadLangs(array("errors", "admin", "prune@prune"));

// Access control
if (!$user->admin) {
	accessforbidden();
}

// Parameters
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');


/*
 * Actions
 */

// None


/*
 * View
 */

$form = new Form($db);

llxHeader('', $langs->trans("PruneAbout"));

// Subheader
$linkback = '<a href="' . ($backtopage ? $backtopage : DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1') . '">';
$linkback .= $langs->trans("BackToModuleList") . '</a>';

print load_fiche_titre($langs->trans("PruneAbout"), $linkback, 'object_prune-32x32@prune');

// Configuration header
$head = pruneAdminPrepareHead();
print dol_get_fiche_head($head, 'about', '', -1, 'prune@prune');

dol_include_once('/prune/core/modules/modPrune.class.php');
$tmpmodule = new modPrune($db);
print $tmpmodule->getDescLong() . '<br><br>';

$composerInfo = new ComposerLockParser\ComposerInfo('../composer.lock');
$packages = $composerInfo->getPackages();
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td class="titlefield">' . $langs->trans("Library") . '</td><td>' . $langs->trans("Version") . '</td><td>' . $langs->trans("Namespace") . '</td>';
print '<td>' . $langs->trans("SourceUrl") . '</td>';
print '<td>' . $langs->trans("Homepage") . '</td>';
print '</tr>';

foreach ($packages as $package) {
	print '<tr class="oddeven">';
	print '<td>';
	print $package->getName();
	print '</td><td>';
	print $package->getVersion();
	print '</td><td>';
	print $package->getRequire()['php'];
	print '</td><td>';
	print $package->getNamespace();
	print '</td><td>';
	print '<a href="' . $package->getSource()['url'] . '">' . $package->getSource()['url'] . '</a>';
	print '</td><td>';
	print empty($package->getHomepage()) ? '' : '<a href="' . $package->getHomepage() . '">' . $package->getHomepage() . '</a>';
	print '</td>';
	print '</tr>';
}
print '</table>';
// Page end
print dol_get_fiche_end();

llxFooter();
$db->close();
