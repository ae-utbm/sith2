<?php

/* Copyright 2009
 * - Mathieu Briand <briandmathieu CHEZ hyprua POINT org>
 *
 * Ce fichier fait partie du site de l'Association des étudiants de
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

/**
 * @file
 * Interface permettant de réaliser le compte de l'argent présent dans les
 * caisses des bars
 *
 */

$topdir="../";
require_once("include/comptoirs.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/cts/user.inc.php");
require_once($topdir. "include/localisation.inc.php");

$site = new sitecomptoirs(true );


if ((get_localisation() != $site->comptoir->id_salle) && (! $site->user->is_in_group("gestion_syscarteae")))
  $site->error_forbidden("services","wrongplace");


$caisse = new CaisseComptoir($site->db,$site->dbrw);


if (($_REQUEST['action'] == "view") && ($site->user->is_in_group("gestion_syscarteae")))
{
  $caisse->load_by_id($_REQUEST["id_cpt_caisse"]);
}
elseif (($_REQUEST['action'] == "newreleve") && ($GLOBALS["svalid_call"]))
{
  $site->comptoir->ouvrir($_REQUEST["id_comptoir"]);

  if (!$site->comptoir->is_valid())
    $site->error_not_found("services");

  if ($site->comptoir->type != 0)
    $site->error_forbidden("services","invalid");

  if (! $site->comptoir->rechargement)
    $site->error_forbidden("services","invalid");

  $especes = array();
  foreach ($_REQUEST["espece"] as $val=>$nb)
    if (intval($nb) > 0)
      $especes[intval($val)] = intval($nb);

  $cheques = array();
  foreach ($_REQUEST["cheque_val"] as $i=>$val)
    if (intval($nb) > 0)
      $especes[intval($val)] = intval($_REQUEST["cheque_nb"][$i]);

  $caisse->ajout(first($site->operateurs), $site->comptoir->id, $especes, $cheques);
}

if ((($_REQUEST['action'] == "view") || ($_REQUEST['action'] == "newreleve")) && ($caisse->is_valid()))
{
  $req = new requete($site->db, "SELECT nom_cpt FROM cpt_comptoir WHERE id_comptoir = ".$caisse->id_comptoir);
  if ( $req->lines == 1 )
    $row = $req->get_row();

  $user = new utilisateur($site->db);
  $user->load_by_id($caisse->id_utilisateur);

  $cts = new contents("Releve effectué le ".date("d/m/Y H:i:s", $caisse->date_releve).", ".$row['nom_cpt']." par ".$user->get_html_link());

  $tbl = new table("Relevé", "sqltable");
  foreach($caisse->especes as $valeur=>$nombre)
    $tbl->add_row(array("Espèce, ".$valeur." €", $nombre), "ln1");
  foreach($caisse->cheques as $valeur=>$nombre)
    $tbl->add_row(array("Cheque, ".$valeur." €", $nombre), "ln1");

  $cts->add($tbl,true);
}

elseif ($_REQUEST['action'] == "new")
{
  $site->comptoir->ouvrir($_REQUEST["id_comptoir"]);

  if (!$site->comptoir->is_valid())
    $site->error_not_found("services");

  if ($site->comptoir->type != 0)
    $site->error_forbidden("services","invalid");

  if (! $site->comptoir->rechargement)
    $site->error_forbidden("services","invalid");

  $cts = new contents("Nouveau releve de caisse");
  $frm = new form ("newreleve","caisse.php",true,"POST","Nouveau releve de caisse");
  $frm->add_hidden("action","newreleve");
  $frm->add_hidden("id_comptoir",$site->comptoir->id);
  $frm->allow_only_one_usage();
  $frm->add_text_field("espece[10]","Pièces de 10 centimes","",false);
  $frm->add_text_field("espece[20]","Pièces de 20 centimes","",false);
  $frm->add_text_field("espece[50]","Pièces de 30 centimes","",false);
  $frm->add_text_field("espece[100]","Pièces de 1 €","",false);
  $frm->add_text_field("espece[200]","Pièces de 2 €","",false);
  $frm->add_text_field("espece[500]","Billets de 5 €","",false);
  $frm->add_text_field("espece[1000]","Billets de 10 €","",false);
  $frm->add_text_field("espece[2000]","Billets de 20 €","",false);
  $frm->add_text_field("espece[5000]","Billets de 50 €","",false);
  $frm->add_text_field("espece[10000]","Billets de 100 €","",false);

  for($i=0; $i<15; $i++)
  {
    $subfrm = new subform("cheque".$i);
    $subfrm->add_text_field("cheque_val[1]","Cheque de : ","",false);
    $subfrm->add_text_field("cheque_nb[1]","Nombre de cheques : ","",false);
    $frm->addsub($subfrm, false, true);
  }

  $frm->add_submit("valid","Valider");
  $cts->add($frm,true);
}
elseif ($site->user->is_in_group("gestion_syscarteae"))
{
  if (! isset($_REQUEST['id_comptoir']))
  {
    $req = new requete($site->db,"SELECT id_comptoir, nom_cpt
               FROM `cpt_comptoir`
               WHERE `rechargement`='1'");

    $comptoirs = array();
    while($row = $req->get_row())
    {
      $comptoirs[] = "<a href=\"caisse.php?id_comptoir=".$row['id_comptoir']."\">".$row['nom_cpt']."</a>";
    }
    $list = new itemlist("Comptoirs", false, $comptoirs);
    $site->add_contents($list);
  }

  $cts = new contents("Releves de caisses");

  $where = "";
  if (isset($_REQUEST['id_comptoir']))
    $where = "WHERE id_comptoir=".intval($_REQUEST['id_comptoir']);

  $req = new requete($site->db,
    "SELECT id_cpt_caisse, date_releve, id_utilisateur, id_comptoir,
      SUM(IF(cheque_caisse='0', valeur_caisse*nombre_caisse, 0)) as somme_especes,
      SUM(IF(cheque_caisse='0', 0, valeur_caisse*nombre_caisse)) as somme_cheques
    FROM `cpt_caisse` LEFT JOIN `cpt_caisse_sommes` USING(`id_cpt_caisse`) ".
    $where
    ." GROUP BY id_cpt_caisse");

  $cts->add(new sqltable(
  "",
  "Releves", $req, "caisse.php",
  "id_cpt_caisse",
  array(
    "date_releve" => "Date du relevé",
    "id_utilisateur" => "Vendeur",
    "id_comptoir" => "Lieu",
    "somme_especes" => "Total espèce",
    "somme_cheques" => "Total cheques"),
  array("view" => "Voir le relevé"),
  array()
  ));
}
else
  $site->error_forbidden("services","invalid");

$site->add_contents($cts);
unset($cts);


/*
// Boite sur le coté
$cts = new contents("Comptoir");

$cts->add_paragraph("<a href=\"index.php\">Autre comptoirs</a>");

$lst = new itemlist();
foreach( $site->comptoir->operateurs as $op )
  $lst->add(
    "<a href=\"comptoir.php?id_comptoir=".$site->comptoir->id."&amp;".
    "action=unlogoperateur&amp;id_operateur=".$op->id."\">". $op->prenom.
    " ".$op->nom."</a>");
$cts->add($lst);

$frm = new form ("logoperateur","comptoir.php?id_comptoir=".$site->comptoir->id);
if ( $opErreur )
  $frm->error($opErreur);
$frm->add_hidden("action","logoperateur");
$frm->add_text_field("adresse_mail","Adresse email","prenom.nom@utbm.fr");
$frm->add_text_field("code_bar_carte","Carte AE");
$frm->add_password_field("password","Mot de passe");
$frm->add_submit("valid","valider");
$cts->add($frm);

$site->add_box("comptoir",$cts);
unset($cts);
*/

$site->end_page();

?>
