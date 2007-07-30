<?php
$topdir = "./../";
require_once($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/cts/user.inc.php");
require_once($topdir. "include/cts/gallery.inc.php");
require_once($topdir. "include/cts/special.inc.php");

require_once($topdir. "include/globals.inc.php");
$site = new site ();

$site->allow_only_logged_users("matmatronch");


if ( !$site->user->utbm && !$site->user->ae )
  $site->error_forbidden("matmatronch","group",10001);
  
$is_admin = ( $site->user->is_in_group("gestion_ae") || $site->user->is_asso_role ( 27, 1 ));

if ( $_REQUEST["action"] == "simplesearch" )
{
	
	$sql = "SELECT `utilisateurs`.*, `utl_etu`.*, `utl_etu_utbm`.* " .
			"FROM `utilisateurs` " .
			"LEFT JOIN `utl_etu` ON `utl_etu`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
			"LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
			"WHERE (CONCAT(`prenom_utl`,' ',`nom_utl`) LIKE '".mysql_escape_joker_string($_REQUEST["pattern"])."%' OR " .
			"CONCAT(`nom_utl`,' ',`prenom_utl`) LIKE '".mysql_escape_joker_string($_REQUEST["pattern"])."%' OR " .
			"CONCAT(`alias_utl`,' (',`prenom_utl`,' ',`nom_utl`,')') LIKE '".mysql_escape_joker_string($_REQUEST["pattern"])."%' OR " .
			"CONCAT(`surnom_utbm`,' (',`prenom_utl`,' ',`nom_utl`,')') LIKE '".mysql_escape_joker_string($_REQUEST["pattern"])."%') ";

  if ( !$is_admin )
    $sql .= "AND `publique_utl`='1'";
    
  $sql .= "LIMIT 10";
    
	$req = new requete($site->db,$sql);
	
	if ( $req->lines == 1 )
	{
		$row = $req->get_row();
		header("Location: ../user.php?id_utilisateur=".$row['id_utilisateur']);
		exit();
	}
	
	$site->start_page("matmatronch","MatMaTronch");
	$site->add_css("css/mmt.css");
	
	if ( $req->lines == 0 )
	{
		$tbl = new error("Aucun resultat","");
	}
	else
	{
		$tbl = new sqltable(
				"listresult", 
				"Resultat de la recherche Mat'Matronch ($nb)", $req, "../user.php", 
				"id_utilisateur", 
				array("nom_utl"=>"Nom","prenom_utl"=>"Prenom","surnom_utbm"=>"Surnom",
				"date_naissance_utl"=>"Date de naissance","promo_utbm"=>"Promo"), 
				array("view"=>"Voir la fiche"), array(), array( )
				);
	}
	
	$site->add_contents($tbl);
	
	$site->end_page();
	exit();
}

if ( !$site->user->ae )
{
  $site->start_page("matmatronch","MatMaTronch");
  $cts = new contents("Accès limité");
  $cts->add_paragraph("L'accès à la recherche avancée du matmatronch est réservée aux cotisants AE. Vous pouvez tout de même utiliser le moteur de recherche rapide se trouvant en haut à gauche de la page.");
  $site->add_contents($cts);
  $site->end_page(); 
  exit();
}

if ( $_REQUEST["action"] == "search" )
{
	$elements = array();
	
	
	if ( $_REQUEST["nom"] )
		$elements[] = "`nom_utl` LIKE '%".mysql_escape_string($_REQUEST["nom"])."%'";
		
	if ( $_REQUEST["prenom"] )
		$elements[] = "`prenom_utl` LIKE '%".mysql_escape_string($_REQUEST["prenom"])."%'";
		
	if ( $_REQUEST["surnom"] )
		$elements[] = "`surnom_utbm` LIKE '%".mysql_escape_string($_REQUEST["surnom"])."%'";
		
	if ( $_REQUEST["date_naissance"]>1 )
	{
		$elements[] = "`date_naissance_utl`='".date("Y-m-d",$_REQUEST["date_naissance"])."'";
		$_POST["date_naissance"]=$_REQUEST["date_naissance"];
	}
	
	if ( $_REQUEST["sexe"] )
	  $elements[] = "`sexe_utl`='".mysql_escape_string($_REQUEST["sexe"])."'";
		
	if ( $_REQUEST["semestre"])
	$elements[] = "`semestre_utbm`='".mysql_escape_string($_REQUEST["semestre"])."'";
		
	if ( $_REQUEST["branche"])
	$elements[] = "`branche_utbm`='".mysql_escape_string($_REQUEST["branche"])."'";
	
	if ( $_REQUEST["promo"])
	  $elements[] = "`promo_utbm`='".mysql_escape_string($_REQUEST["promo"])."'";
	
	if ( $_REQUEST["tel_maison"])
	  $elements[] = "`tel_maison_utl`='".mysql_escape_string(telephone_userinput($_REQUEST["tel_maison"]))."'";
		
	if ( $_REQUEST["tel_portable"])
		$elements[] = "`tel_portable_utl`='".mysql_escape_string(telephone_userinput($_REQUEST["tel_portable"]))."'";
	
	if ( count($elements) >= 1 )
	{
		
		$sql = "SELECT `utilisateurs`.*, `utl_etu`.*, `utl_etu_utbm`.*, `utilisateurs`.`id_ville` as `id_ville`, `utl_etu`.`id_ville` as `ville_parents`, `utilisateurs`.`id_pays` as `id_pays`, `utl_etu`.`id_pays` as `pays_parents` " .
				"FROM `utilisateurs` " .
				"LEFT JOIN `utl_etu` ON `utl_etu`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
				"LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
				"WHERE "	.implode(" AND ",$elements);
		
		$sqlnb = "SELECT COUNT(`utilisateurs`.`id_utilisateur`) " .
				"FROM `utilisateurs` " .
				"LEFT JOIN `utl_etu` ON `utl_etu`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
				"LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
				"WHERE "	.implode(" AND ",$elements);

		
		if ( !$_REQUEST["inclus_ancien"] )
		{
			$sql .= " AND `ancien_etudiant_utl`='0'";
			$sqlnb .= " AND `ancien_etudiant_utl`='0'";
		}
		else
			$_POST["inclus_ancien"]=true;
				
		if ( !$_REQUEST["inclus_nutbm"])
		{
			$sql .= " AND `utbm_utl`='1'";
			$sqlnb .= " AND `utbm_utl`='1'";	
		}
		else
			$_POST["inclus_nutbm"]=true;
			
    if ( !$is_admin )
    {
      $sql .= "AND `publique_utl`='1'";		
      $sqlnb .= "AND `publique_utl`='1'";		
    }
    
		if ( $_REQUEST["order_by"] == 1 )
			$sql .= " ORDER BY `nom_utl`,`prenom_utl`";	
		else
			$sql .= " ORDER BY `surnom_utbm`";
		
		
		$reqnb = new requete($site->db,$sqlnb);
		list($nb) = $reqnb->get_row();
		

		$npp=18;
		$page = intval($_REQUEST["page"]);
		
		if ( $page)
			$st=$page*$npp;
		else
			$st=0;
				
		if ( $st > $nb )
			$st = floor($nb/$npp)*$npp;			
				
	  $sql .= " LIMIT $st,$npp";

		$req = new requete($site->db,$sql);
		
		$site->start_page("matmatronch","MatMaTronch");
		$site->add_css("css/mmt.css");
		
		if ( $nb == 0 )
		{
			$tbl = new error("Aucun resultat","");
		}
		elseif ( $nb > 350 )
		{
			$tbl = new error("Recherche trop imprécise","Votre requete a donné plus de 350 réponses.");
		}
		else
		{
			$tbl = new contents("Resultat de la recherche Mat'Matronch ($nb) <a href=\"".$topdir."matmatronch\" style=\"background-image: url(./../images/icons/16/page.png); background-repeat: no-repeat; background-position: 3px; background-color: white; float: right; font-size: 90%; border: 1px dashed black; padding: 3px; padding-left: 22px; margin-top: -7px;\">Nouvelle Recherche</a>");
			$user = new utilisateur($site->db);
			
			
			$gal = new gallery();
			
			while ( $row = $req->get_row() )
			{
				$user->_load_all($row);
				$gal->add_item(new userinfov2($user));
			}
			
			$tbl->add($gal);
			
			if ( $nb > $npp )
			{
				$tabs = array();
				$i=0;
				while ( $i < $nb )
				{
					$n = $i/$npp;
					$url = "";
					$ar = array_merge($_GET,$_POST);
					$ar["page"] = $n;
					foreach ( $ar as $key => $value )
					{
						if( $key != "magicform" && $value && $key != "mmtsubmit" )
						{
							if ( $url )
								$url .= "&";
							else
								$url = "matmatronch/index.php?";	
							if ( !is_array($value) )
							$url .= $key."=".rawurlencode($value);
						}
					}
					$tabs[]=array($n,$url,$n+1 );
					$i+=$npp;	
				}
				$tbl->add(new tabshead($tabs, $page, "_bottom"));
			}		
			
		}

		$site->add_contents($tbl);
		
		$site->end_page();
		exit();
	}
}

$info = new contents("Le Mat'Matronch");
$info->add_paragraph("Zeu` Trombino de l'UTBM","center");
$info->add_paragraph("<br>");
$info->add_paragraph("Le Mat' Mat' donne enfin un nom a un visage, le numero de portable du binome fantome, l'adresse de ce confrere que l'on recherche tant depuis cette fameuse soiree ...","justify");
$info->add_paragraph("<br>");
$info->add_paragraph(wikilink("activites-matmatronch","Article de présentation"));
$site->add_box("mmt_info",$info);

$info = new contents("Les plus");
$info->add_paragraph("Nos sponsors :");
$info->add_paragraph("<br><a href=\"http://www.societegenerale.fr\" target=\"_blank\">Societe Generale</a>","center");
$info->add_paragraph("<a href=\"http://www.schraag-imp.com\" target=\"_blank\">Imprimerie Schraag</a>","center");
$info->add_paragraph("<br>");
$info->add_paragraph("Les versions Transportables :");
$info->add_paragraph("<a href=\"".$topdir."e-boutic/?cat=13\"><br>Acheter votre version papier</a>","center");
$info->add_paragraph("<br>");
$info->add_paragraph("La version PDF :");
$info->add_paragraph("<img src=\"".$topdir."images/pdf.png\" alt=\"\"><a href=\"/mmt/mmt.pdf\"><br>Obtenir le pdf</a>","center");
$info->add_paragraph("&nbsp;");
$info->add_paragraph("La version mobile :");
$info->add_paragraph("<a href=\"http://ae.utbm.fr/i/\">http://ae.utbm.fr/i/</a><br/><a href=\"".$topdir."iinfo.php\">Plus d'informations...</a>","center");

$site->add_box("mmtplus",$info);

$site->set_side_boxes("right",array("mmt_info","mmtplus"),"mmt_right");


$site->start_page("matmatronch","MatMaTronch");
$site->add_css("css/mmt.css");

$cts = new contents("Recherche Mat'Matronch");

/*Partie gauche du formulaire commun */
$frm_global = new form("mmtsearch","index.php",false,"POST","Recherche Mat'Matronch");
$frm_global->add_hidden("action","search");

$frm_left = new form("mmtleft",null,null,null);
$frm_left->add_text_field("nom","Nom");	
$frm_left->add_text_field("prenom","Prenom");
$frm_left->add_text_field("surnom","Surnom");	
$frm_left->add_radiobox_field("sexe","Sexe",array(1=>"Homme",2=>"Femme",0=>"Indifférent"),0,-1,false,array(1=>"images/icon_homme.png",2=>"images/icon_femme.png"));
	
$frm_global->add($frm_left);	
	
$frm_level = new form("mmtright",null,null,null);
$frm_level->_render_name("essai","Par :",false);

$frm_niveau = new form("parformnvl",null,null,null,"Niveau :");	
$frm_niveau->add_select_field("branche","Branche",array(""=>"Toutes","TC"=>"TC","GI"=>"GI","GSP"=>"IMAP","GSC"=>"GESC","GMC"=>"GMC","Enseignant"=>"Enseignant","Administration"=>"Administration"));
$frm_niveau->add_select_field("semestre","Semestre",array(""=>"Tous",1=>"1",2=>"2",3=>"3",4=>"4",5=>"5",6=>"6"));	

$frm_level->add($frm_niveau,true, false, /**/true, "niveau", false, true, true);

$frm_promo = new form("parformpromo",null,null,null,"Promo :");

$frm_promo->add_radiobox_field("promo", false, array (1 => "Promo 01",
                                                      2 => "Promo 02",
                                                      3 => "Promo 03",
                                                      4 => "Promo 04",
                                                      5 => "Promo 05",
                                                      6 => "Promo 06",
                                                      7 => "Promo 07",
                                                      8 => "Promo 08",
                                                      0 => "Toutes"),
                                0, -1, false, array ( 1 => "images/promo_01.png",
                                                      2 => "images/promo_02.png",
                                                      3 => "images/promo_03.png",
                                                      4 => "images/promo_04.png",
                                                      5 => "images/promo_05.png",
                                                      6 => "images/promo_06.png",
                                                      7 => "images/promo_07.png",
                                                      8 => "images/promo_08.png"));

$frm_level->add($frm_promo,true, false, false, "promo", false, true, true);

$frm_level->buffer.="<script>parform_val=null;</script>";

$frm_global->add($frm_level);
$frm_global->add_submit("mmtsubmit","Rechercher");
$frm_advanced = new form("search",null,null,null,"Plus de criteres");
$frm_advanced->add_date_field("date_naissance","Date de naissance",-1,false,"Entrez une date au format JJ/MM/AAAA");
$frm_advanced->add_text_field("tel_maison","Telephone (fixe)",$user->tel_maison);
$frm_advanced->add_text_field("tel_portable","Telephone (portable)",$user->tel_portable);
$frm_advanced->add_checkbox("inclus_ancien","Inclure les anciens",false);
$frm_advanced->add_checkbox("inclus_nutbm","Inclure les non-utbm",false);

$frm_global->add($frm_advanced,false, false, false, false, false, true, false);

$frm_order = new form("order",null,null,null,"Tri & affichage des resultats");
$frm_order->add_radiobox_field("order_by","Trier par",array(1=>"Nom",2=>"Prenom",3=>"Surnom"),3,false,false,array());
$frm_global->add($frm_order);

$frm_global->add_submit("mmtsubmit","Rechercher");

$site->add_contents($frm_global);

$site->end_page();

?>
