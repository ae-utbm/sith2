<?php
/* Copyright 2008
 * - Simon Lopez < simon dot lopez at ayolo dot org >
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License a
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
$topdir = "./";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
$site = new site ();

$site->start_page ("none", "Weekmail");


if(isset($_REQUEST['id']))
{
  $sql='SELECT * FROM weekmail WHERE id='.intval($_REQUEST['id'].' AND statut=1');
  $req = new requete($site->db,$sql);
  if($req->lines==0)
  {
    $cts=new error('Weekmail not found!','Weekmail inconnu au bataillon moussaillon');
    $site->add_contents($cts);
  }
  else
  {
    list($id,$date,$title,$content,$statut)=$req->get_row();
    $cts = new contents('<a href="weekmail.php">Weekmails</a> / [Weekmail] '.$title);
    list($annee, $mois, $jour) = explode("-", $date);
    $date=$jour."/".$mois."/".$annee;
    $cts->add_paragraph('Envoyé le '. $date);
    $cts->puts(doku2xhtml($content));
    $site->add_contents($cts);
    $site->end_page();
    exit();
  }
}

$sql = 'SELECT id'.
       ',CONCAT(\'[Weekmail] \',title) as title'.
       ',CONCAT(RIGHT(CONCAT(\'00\',DAY(date)),2),\'/\',RIGHT(CONCAT(\'00\',MONTH(date)),2),\'/\',YEAR(date)) as date '.
       'FROM weekmail '.
       'WHERE statut=1 '.
       'ORDER BY date,id DESC';
$req = new requete($site->db,$sql);
$cts = new sqltable('weekmails',
                    'Liste des weekmails',
                    $req,
                    'weekmail.php',
                    'id',
                    array('id'=>'N°','title'=>'Titre','date'=>'Date'),
                    array('view'=>'Consulter'),
                    array());
$site->add_contents($cts);
$site->end_page ();

?>
