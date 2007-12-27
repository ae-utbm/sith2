<?php

/* Copyright 2007
 * - Julien Etelain < julien at pmad dot net >
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */
define("PROD_CRON", "/var/www/cron_update.sh");
$topdir="../";

require_once($topdir. "include/site.inc.php");

$site = new site ();

if ( !$site->user->is_in_group("root") )
  $site->error_forbidden("none","group",7);
	
$site->start_page("none","Administration / passage en prod");
$cts = new contents("<a href=\"./\">Administration</a> / prod_cron.php");

$cts->add_paragraph("Révision en production : ".get_rev());

if ( $_REQUEST["action"] == "passprod" && $GLOBALS["svalid_call"] )
{
  if ( $site->is_sure ( "","Passage en production",null, 2 ) )
    @exec(PROD_CRON);
  $Ok=true;
}

if ( $Success )
  $cts->add_paragraph("Passage en prod programmé dans les deux minutes à venir");


$cts->add_paragraph("Révision en production : ".get_rev());

$frm = new form("passageenprod", "prod_cron.php", false, "POST", "Passer en production");
$frm->allow_only_one_usage();
$frm->add_hidden("action","passprod");
$frm->add_submit("valid","Valider");
$cts->add($frm,true);

$site->add_contents($cts);

$site->end_page();

?>
