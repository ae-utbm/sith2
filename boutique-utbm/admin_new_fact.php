<?php
/**
 * @brief Admin de la boutique utbm
 *
 */

/* Copyright 2008
 *
 * - Simon Lopez <simon POINT lopez CHEZ ayolo POINT org>
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

$topdir="../";

require_once("include/boutique.inc.php");

function generate_subform_stock ( $nom,$form_n, $stock_n, $stock_value_n, $stock = -1, $enabled=true )
{

 $subfrm=new form ($form_n,false,false,false,$nom);

 $subfrm1=new form ($stock_n,false,false,false,"Non limité");
 $subfrm->add($subfrm1,false,true,($stock==-1),"nlim",true);

 $subfrm2=new form ($stock_n,false,false,false,"Limité à");
 $subfrm2->add_text_field($stock_value_n,"",($stock==-1)?"":$stock,false,false,false,$enabled);
 $subfrm->add($subfrm2,false,true,($stock!=-1),"lim",true);

 return $subfrm;
}


$site = new boutique();
if(!$site->user->is_in_group("root") && !$site->user->is_in_group("adminboutiqueutbm"))
  $site->error_forbidden();

$user = new utilisateur($site->db);
$user->load_by_id($_REQUEST['id_utilisateur']);
if(!$user->is_valid() || $user->type !='srv')
  header("Location: http://boutique.utbm.fr/admin_utl.php");


$req = new requete($site->db,
    "SELECT `boutiqueut_produits`.`nom_prod`, `boutiqueut_produits`.`id_produit`," .
    "`boutiqueut_produits`.stock_global_prod, " .
    "FORMAT(`boutiqueut_produits`.prix_vente_prod/100,2) AS prix_vente_prod " .
    "FROM `boutiqueut_produits` " .
    "INNER JOIN `boutiqueut_type_produit` ON `boutiqueut_type_produit`.`id_typeprod`=`boutiqueut_produits`.`id_typeprod` " .
    "WHERE prod_archive != 1 " .
    "AND `boutiqueut_produits`.`id_produit` NOT IN (SELECT id_produit_parent FROM boutiqueut_produits WHERE id_produit_parent IS NOT NULL) ".
    "ORDER BY `boutiqueut_produits`.`nom_prod`, `boutiqueut_type_produit`.`nom_typeprod`");
if(isset($_REQUEST['action']))
{
  if(($_REQUEST['action']=="newcmd")
    || ($_REQUEST['action']=="validercmd" && $_REQUEST['save']=='Modifier')
  )
  {
    $site->start_page("services","Administration");
    $cts = new contents("<a href=\"admin.php\">Administration</a> / Enregistrer une commande");
    $cts->add_paragraph("Service concerné : ".$user->nom." ".$user->prenom);
    $frm = new form ("genfact","admin_new_fact.php",false,"POST","Enregistrer une commande");
    $frm->allow_only_one_usage();
    $frm->add_hidden("page","newcmd");
    $frm->add_hidden("checksum", gen_uid());
    $frm->add_hidden("action","validercmd");
    $frm->add_hidden("id_utilisateur",$user->id);
    $sum=0;
    while(list($nom_prod,$id_produit,$stock_global_prod,$prix)=$req->get_row())
    {
      $_prix=sprintf("%.2f Euros",$prix);
      $frm->add_hidden("max_idprod".$id_produit,$stock_global_prod);
      if(isset($_REQUEST['prod'][$id_produit]) && intval($_REQUEST['prod'][$id_produit])!=0)
      {
        $frm->add_text_field("prod[$id_produit]","<b>$nom_prod</b>",intval($_REQUEST['prod'][$id_produit]),false,false,true,true,$_prix);
        $sum=$sum+($prix*intval($_REQUEST['prod'][$id_produit]));
      }
      else
        $frm->add_text_field("prod[$id_produit]","<b>$nom_prod</b>",0,false,false,true,true,$_prix);
    }
    $frm->add_info('<b>Total : '.sprintf("%.2f Euros",$sum).'</b>');
    $frm->add_text_field('eotp','EOTP');
    $frm->add_text_field('objectif','Motif','',true);
    $req = new requete($site->db,
         'SELECT centre_cout FROM boutiqueut_centre_cout WHERE id_utilisateur='.$user->id);
    if($req->lines==1)
    {
      list($cc)=$req->get_row();
      $frm->add_hidden('centre_cout',$cc);
      $frm->add_info('Centre de coût : '.$cc);
    }
    else
    {
      $ccs = array(''=>'--');
      while(list($cc)=$req->get_row())
        $ccs[$cc]=$cc;
      $frm->add_select_field('centre_cout','Centre de coût',$ccs,false,'',true);
    }
    if($sum>0)
    {
      $frm->add_submit("save","Modifier");
      $frm->add_submit("save","Valider");
    }
    $frm->add_submit("save","Annuler");
    $cts->add($frm,true);
    $site->add_contents($cts);
    $site->end_page();
    exit();
  }
  elseif($_REQUEST['action']=="validercmd"
         && $_REQUEST['save']=='Valider'
   && (!isset($_SESSION['boutiquechecksum'])
       ||!isset($_SESSION['boutiquechecksum'][$_REQUEST['checksum']])
      )
  )
  {
    if(!isset($_SESSION['boutiquechecksum']))
      $_SESSION['boutiquechecksum']=array();
    $debfact = new debitfacture ($site->db, $site->dbrw);
    foreach ($_REQUEST['prod'] as $id=>$nb)
    {
      $vp = new venteproduit ($site->db, $site->dbrw);
      if($nb>0)
        if($vp->load_by_id ($id))
          $cpt_cart[] = array($nb, $vp);
    }
    $debfact->debit ($user,$cpt_cart,0,1,'UT',null,null,null,$_REQUEST['objectif'],$_REQUEST['eotp'],$_REQUEST['centre_cout']);
    if($debfact->is_valid())
    {
      $info='<script language="javascript" type="text/javascript">newwindow=window.open(\'admin_gen_fact.php?id_facture='.$debfact->id.'\',\'facture\',\'height=500,width=300\');</script>';
      $_SESSION['boutiquechecksum'][$_REQUEST['checksum']]=$debfact->id;
    }
  }
  else
  {
    $info='<script language="javascript" type="text/javascript">newwindow=window.open(\'admin_gen_fact.php?id_facture='.$_SESSION['boutiquechecksum'][$_REQUEST['checksum']].'\',\'facture\',\'height=500,width=300\');</script>';
  }
}
$site->start_page("services","Administration");
$cts = new contents("<a href=\"admin.php\">Administration</a> / Enregistrer une commande");
$cts->add_paragraph("Service concerné : ".$user->nom." ".$user->prenom);
if(isset($info))
  $cts->add_paragraph($info);
$frm = new form ("addtype","admin_new_fact.php",false,"POST","Enregistrer une commande");
$frm->allow_only_one_usage();
$frm->add_hidden("page","newcmd");
$frm->add_hidden("action","newcmd");
$frm->add_hidden("id_utilisateur",$user->id);
while(list($nom_prod,$id_produit,$stock_global_prod,$prix)=$req->get_row())
{
  $prix=sprintf("%.2f Euros",$prix);
  $frm->add_hidden("max_idprod".$id_produit,$stock_global_prod);
  $frm->add_text_field("prod[$id_produit]","<b>$nom_prod</b>","",false,false,true,true,$prix);
}
$frm->add_submit("valid","Valider");
$cts->add($frm,true);
$site->add_contents($cts);
$site->end_page();

?>
