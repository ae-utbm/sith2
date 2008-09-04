<?php

/* Copyright 2008
 * - Simon Lopez < simon dot lopez at ayolo dot org >
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

$topdir="../";

require_once($topdir. "include/site.inc.php");
require_once($topdir . "include/cts/user.inc.php");

$site = new site ();

if ( !$site->user->is_in_group("root") )
  $site->error_forbidden("none","group",7);

$site->start_page("none","Administration");

if(isset($_POST['action'])
   && $_POST['action']=='bloubiboulga'
   && is_dir("/var/www/ae/www/ae2/var/img")
   && is_uploaded_file($_FILES['zipeuh']['tmp_name']) )
{
  mkdir("/tmp/blehmatmat");
  if(is_dir("/tmp/blehmatmat"))
  {
    $user = new utilisateur($site->db);
    exec('unzip "'.$_FILES['zipeuh']['tmp_name'].'" -j -d "/tmp/blehmatmat/"');
    $h = opendir('/tmp/blehmatmat/');
    while ($f=readdir($h))
    {
      if ($file == "." && $file == "..")
        continue;
      if(substr($f,-3)=='JPG' || substr($f,-3)=='jpg')
      {
        $id=substr($f,0,-4);
        $user->load_by_id($_REQUEST["id_utilisateur"]);
        if ( !$user->is_valid() )
          exec("/usr/share/php5/exec/convert /tmp/blehmatmat/".$f." -thumbnail 225x300 /var/www/ae/www/ae2/var/img/matmatronch/".$id.".identity.jpg");
      }
    }
    exec("rm -Rf /tmp/blehmatmat/");
  }
}

$cts = new contents("Administration/Import massif de photos matmatronch");
$frm = new form("photos","?",true,"POST","Et paf les photos");
$frm->add_hidden("action","bloubiboulga");
$frm->add_file_field ( "zipeuh", "Zipeuh !!!" );
$frm->add_submit("paff","Et paf!");
$cts->add($frm,true);

$site->add_contents($cts);

$site->end_page();

?>
