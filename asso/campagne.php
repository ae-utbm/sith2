<?php
/* Copyright 2007
 * - Simon Lopez <simon POINT lopez CHEZ ayolo POINT org>
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
$topdir = "../";

require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/entities/asso.inc.php");
$site = new site ();

$asso = new asso($site->db,$site->dbrw);
$asso->load_by_id($_REQUEST["id_asso"]);
if ( $asso->id < 1 )
{
  header("Location: ../404.php");
  exit();
}
if ( !$site->user->is_in_group("gestion_ae") && !$asso->is_member_role($site->user->id,ROLEASSO_MEMBREBUREAU) )
  error_403();


$site->start_page("campagne","Campagnes de recrutement");

$cts = new contents($asso->get_html_path());
$cts->add(new tabshead($asso->get_tabs($site->user),"cpg"));
$site->add_contents($cts);

if (isset($_REQUEST["addcpg"]) && isset($_REQUEST["nom"]) && !empty($_REQUEST["nom"]) && isset($_REQUEST["end_date"]) && isset($_REQUEST["description"]) && isset($_REQUEST["questions"]) )
{
  $cts = new contents("Campagne ajoutée avec succès");
  $cpg = new campagne($site->db,$site->dbrw);
  $cpg->new_campagne($_REQUEST["nom"], $_REQUEST["description"], $_REQUEST["end_date"], $_REQUEST["id_groupe"]);
  foreach ( $_REQUEST["questions"] as $rep )
  {
    if ( isset($rep['nom_question']) && !empty($rep['nom_question']) && isset($rep['type_question']))
    {
      if(empty($rep['description_question']))
        $rep['description_question']==$rep['nom_question'];

      if (($rep['type_question'] == "list" || $rep['type_question'] =="radio") && !empty($rep['reponses_question']))
      {
        $reponses=$rep['reponses_question'];
        $values=explode(";",$reponses,2);
        foreach($values as $value)
        {
          $value=explode("|", $value, 2);
          $c=count($value);
          if( $c!= 2 || empty($value[0]) || empty($value[1]))
          {
            $rep['type_question']="text";
            $reponses="";
          }
        }
        $cpg->add_question($rep['nom_question'],$rep['description_question'],$rep['type_question'],$reponses);
      }
      elseif ($rep['type_question'] == "text" || $rep['type_question'] == "checkbox" )
      {
        $cpg->add_question($rep['nom_question'],$rep['description_question'],$rep['type_question']);
      }
    }
  }
  $cts->add_paragraph("<img src=\"".$topdir."images/actions/done.png\">&nbsp;La campagne \"".$cpg->nom."\" a bien été ajouté.");
  $site->add_contents($cts,true);
  unset($_REQUEST["nom"]);
  unset($_REQUEST["description"]);
  unset($_REQUEST["questions"]);
  unset($_REQUEST["end_date"]);

}

if($_REQUEST["action"]=="add")
{
  $frm = new form ("nvcampagne","campagne.php?id_asso=".$asso->id,false,"POST","Nouvelle campagne");

  /* Duree de validite d'une campagne = 15 jours par defaut */

  $default_valid = time() + (15 * 24 * 60 * 60);

  if ($_REQUEST["end_date"])
    $frm->add_date_field("end_date", "Date de fin de validite : ",$_REQUEST["end_date"],true);
  else
    $frm->add_date_field("end_date", "Date de fin de validite : ",$default_valid,true);

  $frm->add_text_field("nom", "Nom de la campagne",$_REQUEST["nom"],true,80);    

  $frm->add_text_area("description", "Description de la campagne",$_REQUEST["description"]);
  $frm->add_entity_smartselect("id_groupe","Groupe",new group($site->db));

  $frm->add_info("Pour supprimer une question, il suffit de laisser son nom vide !<br />");
  $frm->add_info("Pour une question de type liste ou bouton radio, complétez impérativement le champ \"Réponses possibles\".");
  $frm->add_info("Formatage du champ \"Réponses possibles\" : valeur_1|La valeur 1;valeur_2|La valeur 2;...;valeur_z|La dernière valeur");

  if (isset($_REQUEST["questions"]))
  {
    $n = 1;
    foreach ( $_REQUEST["questions"] as $num=>$question )
    {
      if ( !empty($question) )
      {
        $subfrm = new form("questions".$num,null,null,null,"Question $n");
        $subfrm->add_text_field("questions[$num][nom_question]", "Nom question",$question["nom_question"],false,80);
        $subfrm->add_text_area("questions[$num][description_question]", "Description",$question["description_question"]);
        if(isset($question["type_question"]))
          $type = $question["type_question"];
        else
          $type="text";
        $subfrm->add_select_field("questions[$num][type_question]","Type de question",array("text"=>"Texte","checkbox"=>"Boite à cocher","list"=>"Liste", "radio"=>"Bouton radio"),$type);
        $subfrm->add_text_field("questions[$num][reponses_question]", "Réponses possibles",$question["reponses_question"],false,80);
        $frm->add ( $subfrm, false, false, false, false, false, false, true );
        $n++;
      }
    }
    if (isset($_REQUEST["newques"]))
    {
      $i=$n-1;
      $subfrm = new form("questions".$i,null,null,null,"Question $n");
      $subfrm->add_text_field("questions[$i][nom_question]", "Nom question","",false,80);
      $subfrm->add_text_area("questions[$i][description_question]", "Description");
      $subfrm->add_select_field("questions[$i][type_question]","Type de question",array("text"=>"Texte","checkbox"=>"Boite à cocher","list"=>"Liste", "radio"=>"Bouton radio"),"text");
      $subfrm->add_text_field("questions[$i][reponses_question]", "Réponses possibles","",false,80);
      $frm->add ( $subfrm, false, false, false, false, false, false, true );
    }
  }
  else
  {
    $n=1;
    for($i=0;$i<6;$i++)
    {
      $subfrm = new form("questions".$i,null,null,null,"Question $n");
      $subfrm->add_text_field("questions[$i][nom_question]", "Nom question","",false,80);
      $subfrm->add_text_area("questions[$i][description_question]", "Description");
      $subfrm->add_select_field("questions[$i][type_question]","Type de question",array("text"=>"Texte","checkbox"=>"Boite à cocher","list"=>"Liste", "radio"=>"Bouton radio"),"text");
      $subfrm->add_text_field("questions[$i][reponses_question]", "Réponses possibles","",false,80);
      $frm->add ( $subfrm, false, false, false, false, false, false, true );
      $n++;
    }
  }

  $frm->add_hidden("text_submit",$text_submit);
  $frm->add_hidden("date_campagne",$_REQUEST['date_campagne']);

  $frm->add_submit("newques","Question supplémentaire");

  $frm->add_submit("addcpg","Ajouter","Le campagne sera immédiatement pris en compte et affichéé le site");

  $site->add_contents($frm);
}
else
{
  $cts=new contents();
  $cts->add_paragraph("<a href=\"./campagne.php?id_asso=".$asso->id."&action=add\">Ajouter une campagne</a>");
  $req = new requete ( $site->db,
                       "SELECT `id_campagne`, `nom_campagne`, `date_debut_campagne`, `date_fin_campagne` ".
                       "FROM `cpg_campagne` WHERE `id_asso`='".$asso->id."'" );
  $tbl = new sqltable("listcampagne",
                      "Campagnes",
                      $req,
                      $topdir."campagne.php?id_asso=".$asso->id,
                      "id_campagne",
                      array("nom_campagne"=>"Intitulé","date_debut_campagne"=>"Début","date_fin_campagne"=>"Fin"),
                      array(),
                      array(),
                      array() );
  $cts->add($tbl);
  $site->add_contents($cts);
}

$site->end_page ();

?>
