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
$site->start_page("releves","Releves de caisses");

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

  if ((get_localisation() != $site->comptoir->id_salle) && (! $site->user->is_in_group("gestion_syscarteae")))
    $site->error_forbidden("services","wrongplace");

  if ( count($site->comptoir->operateurs) == 0 )
  {
    $cts->add_paragraph("En attente de la connexion d'un barman");
  }
  else
  {
    $especes = array();
    foreach ($_REQUEST["espece_nb"] as $val=>$nb)
      if (intval($nb) > 0)
        $especes[intval($val)] = intval($nb);

    $cheques = array();
    foreach ($_REQUEST["cheque_val"] as $i=>$val)
      if (intval($_REQUEST["cheque_nb"][$i]) > 0)
        $cheques[intval($val)] += intval($_REQUEST["cheque_nb"][$i]);

    $caisse_videe = false;
    if (($site->user->is_in_group("gestion_syscarteae")) && ($_REQUEST['caisse_videe']))
      $caisse_videe = true;

    $caisse->ajout(first($site->comptoir->operateurs)->id, $site->comptoir->id, $especes, $cheques, $caisse_videe);
  }
}

if ((($_REQUEST['action'] == "view") || ($_REQUEST['action'] == "newreleve")) && ($caisse->is_valid()))
{
  $req = new requete($site->db, "SELECT nom_cpt FROM cpt_comptoir WHERE id_comptoir = ".$caisse->id_comptoir);
  if ( $req->lines == 1 )
    $row = $req->get_row();

  $user = new utilisateur($site->db);
  $user->load_by_id($caisse->id_utilisateur);

  if ($site->user->is_in_group("gestion_syscarteae"))
    $cts = new contents("<a href=\"caisse.php\">Relevés</a> /
        <a href=\"caisse.php?id_comptoir=".$caisse->id_comptoir."\">".$row['nom_cpt']."</a> /
        ".date("d/m/Y H:i:s", $caisse->date_releve));
  else
    $cts = new contents();

  $tbl = new table("Releve effectué le ".date("d/m/Y H:i:s", $caisse->date_releve).", ".$row['nom_cpt']." par ".$user->get_html_link(), "sqltable");
  $tbl->add_row(array("Type", "Qté"), "head");
  foreach($caisse->especes as $valeur=>$nombre)
    $tbl->add_row(array("Espèce ".number_format($valeur/100, 2)." €", $nombre), "ln1");
  foreach($caisse->cheques as $valeur=>$nombre)
    $tbl->add_row(array("Chèques ".number_format($valeur/100, 2)." €", $nombre), "ln1");

  $cts->add($tbl,true);

  if ($caisse->caisse_videe)
    $cts->add_paragraph("La caisse a été vidée après ce relevé");
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

  if ((get_localisation() != $site->comptoir->id_salle) && (! $site->user->is_in_group("gestion_syscarteae")))
    $site->error_forbidden("services","wrongplace");

  if ( count($site->comptoir->operateurs) == 0 )
  {
    $cts = new contents($site->comptoir->nom);
    $cts->add_paragraph("En attente de la connexion d'un barman");
  }
  else
  {
    $cts = new contents("Nouveau releve de caisse");
    $frm = new form ("newreleve","caisse.php",true,"POST");
    $frm->add_hidden("action","newreleve");
    $frm->add_hidden("id_comptoir",$site->comptoir->id);
    $frm->allow_only_one_usage();

    $esp = array(
      10 => "Pièces de 10 centimes ",
      20 => "Pièces de 20 centimes ",
      50 => "Pièces de 50 centimes ",
      100 => "Pièces de 1 € ",
      200 => "Pièces de 2 € ",
      500 => "Billets de 5 € ",
      1000 => "Billets de 10 € ",
      2000 => "Billets de 20 € ",
      5000 => "Billets de 50 € ",
      10000 => "Billets de 100 € ",
    );

    foreach( $esp as $val => $txt)
    {
      /* On utilise des subform uniquement pour être en harmonie avec la suite... */
      $subfrm = new subform("espece[$val]");
      $subfrm->add_text_field("espece_nb[$val]", $txt, "",false, 5);
      $frm->addsub($subfrm, false, true);
    }

    for($i=0; $i<15; $i++)
    {
      $subfrm = new subform("cheque[".$i."]");
      $subfrm->add_price_field("cheque_val[".$i."]","Chèques de : ","",false, "€", 5);
      $subfrm->add_text_field("cheque_nb[".$i."]","Nombre de cheques : ","",false, 5);
      $frm->addsub($subfrm, false, true);
    }

    if ($site->user->is_in_group("gestion_syscarteae"))
    {
      $frm->add_checkbox("caisse_videe", "Caisse vidée");
    }

    $frm->add_submit("valid","Valider");
    $cts->add($frm,true);
  }
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

    $cts = new contents("Releves de caisses");

    if (! isset($_REQUEST['showall']))
      $cts->add_paragraph("<a href=\"caisse.php?showall\">Afficher tous les relevés</a>");
  }
  else
  {
    $req = new requete($site->db,"SELECT id_comptoir, nom_cpt
               FROM `cpt_comptoir`
               WHERE `id_comptoir`='".$_REQUEST['id_comptoir']."'");
    $row = $req->get_row();
    $cts = new contents("<a href=\"caisse.php\">Relevés</a> /
        <a href=\"caisse.php?id_comptoir=".$caisse->id_comptoir."\">".$row['nom_cpt']."</a>");
    if (! isset($_REQUEST['showall']))
      $cts->add_paragraph("<a href=\"caisse.php?id_comptoir=".$caisse->id_comptoir
          ."&amp;showall\">Afficher tous les relevés</a>");
  }

  $where = $limit = "";
  if (isset($_REQUEST['id_comptoir']))
  {
    $where = "WHERE id_comptoir=".intval($_REQUEST['id_comptoir'])." ";
    if (! isset($_REQUEST['showall']))
      $where .= "AND (cpt_caisse.id_cpt_caisse > (SELECT MAX(`cpt_caisse_ref`.`id_cpt_caisse`)
                  FROM `cpt_caisse` `cpt_caisse_ref`
                  WHERE  `cpt_caisse_ref`.`id_comptoir`='".intval($_REQUEST['id_comptoir'])."'
                  AND `cpt_caisse_ref`.`caisse_videe` = '1')) ";
  }
  elseif(! isset($_REQUEST['showall']))
    $limit = "LIMIT 100";

  $req = new requete($site->db,
    "SELECT id_cpt_caisse, date_releve, id_utilisateur, id_comptoir, nom_cpt,
      CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) as `nom_utilisateur`,
      ROUND(SUM(IF(cheque_caisse='0', valeur_caisse*nombre_caisse, 0))/100, 2) as somme_especes,
      ROUND(SUM(IF(cheque_caisse='0', 0, valeur_caisse*nombre_caisse))/100, 2) as somme_cheques,
      IF(caisse_videe='1', 'Oui', '') as caisse_videe
    FROM `cpt_caisse` LEFT JOIN `cpt_caisse_sommes` USING(`id_cpt_caisse`)
    INNER JOIN `utilisateurs` USING(id_utilisateur)
    INNER JOIN `cpt_comptoir` USING(id_comptoir) " .
    $where
    ." GROUP BY id_cpt_caisse
    ORDER BY date_releve DESC
    $limit
    ");

  $cts->add(new sqltable(
  "",
  "Releves", $req, "caisse.php",
  "id_cpt_caisse",
  array(
    "date_releve" => "Date du relevé",
    "nom_utilisateur" => "Vendeur",
    "nom_cpt" => "Lieu",
    "somme_especes" => "Total espèce",
    "somme_cheques" => "Total cheques",
    "caisse_videe" => "Caisse videe"),
  array("view" => "Voir le relevé"),
  array()
  ));
}
else
  $site->error_forbidden("services","invalid");

$site->add_contents($cts);
unset($cts);


if ($site->comptoir->is_valid())
{
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
  $site->set_side_boxes("right",array("comptoir"));
  unset($cts);
}

$site->end_page();
//TODO :  total théorique chèques et espèce séparés + total relevé
//TODO: case banque
?>
