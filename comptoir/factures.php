<?php
/* Copyright 2006
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
$topdir="../";
require_once("include/comptoirs.inc.php");
require_once($topdir."include/cts/sqltable.inc.php");
require_once ($topdir. "include/assoclub.inc.php");
require_once ($topdir . "include/pdf/facture_pdf.inc.php");
$site = new sitecomptoirs();

if ( $site->user->id < 1 )
{
	header("Location: ../403.php?reason=session");
	exit();	
}	


if ( !$site->user->is_in_group("gestion_ae") )
	error_403();	


if ( $_REQUEST["action"] == "genfact" )
{
  $month = substr($_REQUEST["mois"],3,4).substr($_REQUEST["mois"],0,2);

  $sql = new requete($site->db, 
    "SELECT ".
    "`asso`.`nom_asso`,".
    "`asso`.`id_asso`,".
    "SUM( `prix_unit` * `quantite` ) /100 as `somme` ".
    "FROM `cpt_vendu` ".
    "INNER JOIN cpt_debitfacture USING ( `id_facture` ) ".
    "INNER JOIN asso ON asso.id_asso = cpt_vendu.id_assocpt ".
    "WHERE id_produit NOT IN ( 40, 41, 42, 43 ) AND " .
    "EXTRACT( YEAR_MONTH FROM `date_facture` ) ='".mysql_real_escape_string($month)."'  ".
    "GROUP BY `asso`.`id_asso` ".
    "ORDER BY `asso`.`nom_asso`");


    
    
	$factured_infos = array ('name' => "AE - UTBM",
      'addr' => array("6 Boulevard Anatole France",
      "90000 BELFORT"),
      'logo' => "http://ae.utbm.fr/images/Ae-blanc.jpg");
      
	$date_facturation = date("d/m/Y", mktime ( 0, 0, 0, substr($month,4)+1, 1, substr($month,0,4)));
  $ref = $month;
  
  $asso = new asso($site->db);
  
  $fact_pdf = new facture_pdf (null, $factured_infos, $date_facturation,null,$ref,null);
  $fact_pdf->AliasNbPages ();

  while ( $row = $sql->get_row() )
  {

  
  
    $asso->load_by_id($row['id_asso']);
    
    
    $facturing_infos = array ('name' => $asso->nom,
			 'addr' => explode("\n",utf8_decode($asso->adresse_postale)),
			 'logo' => "/var/www/ae/www/ae2/var/img/logos/".$asso->nom_unix.".jpg");
			 
    $query = new requete ($site->db, "SELECT " .
        "CONCAT(`cpt_produits`.`id_typeprod`,'-',`cpt_vendu`.`id_produit`,'-',`cpt_vendu`.`prix_unit`) AS `groupby`, " .
        "SUM(`cpt_vendu`.`quantite`) AS `quantite`, " .
        "`cpt_vendu`.`prix_unit` AS `prix_unit`, " .
        "SUM(`cpt_vendu`.`prix_unit`*`cpt_vendu`.`quantite`) AS `total`," .
        "`cpt_produits`.`nom_prod`," .
        "`cpt_type_produit`.`nom_typeprod`"  .
        "FROM `cpt_vendu` " .
        "INNER JOIN `asso` ON `asso`.`id_asso` =`cpt_vendu`.`id_assocpt` " .
        "INNER JOIN `cpt_produits` ON `cpt_produits`.`id_produit` =`cpt_vendu`.`id_produit` " .
        "INNER JOIN `cpt_debitfacture` ON `cpt_debitfacture`.`id_facture` =`cpt_vendu`.`id_facture` " .
        "INNER JOIN `utilisateurs` ON `cpt_debitfacture`.`id_utilisateur` =`utilisateurs`.`id_utilisateur` " .
        "INNER JOIN `cpt_type_produit` ON `cpt_type_produit`.`id_typeprod`=`cpt_produits`.`id_typeprod` " .
        "WHERE `cpt_vendu`.`id_assocpt`='".mysql_real_escape_string($asso->id)."' AND `cpt_produits`.`id_typeprod`!='11' " .
        "AND EXTRACT(YEAR_MONTH FROM `date_facture`)='".mysql_real_escape_string($month)."' " .
        "GROUP BY `groupby` " .
        "ORDER BY `cpt_type_produit`.`nom_typeprod`, `cpt_produits`.`nom_prod`, `cpt_vendu`.`prix_unit`");
        
    $lines = array();
    while ($line = $query->get_row ())
    {
      $lines[] = array('nom' => utf8_decode($line['nom_prod']),
           'quantite' => intval($line['quantite']),
           'prix' => $line['prix_unit'],
           'sous_total' => intval($line['quantite']) * $line['prix_unit']);
    }
  
    $fact_pdf->set_infos($facturing_infos,
				     $factured_infos,
				     $date_facturation,
				     $titre,
				     $ref,
				     $lines);
    $fact_pdf->AddPage ();
    $fact_pdf->print_items ();
  
  }
  $fact_pdf->Output ();
  exit();
}



$site->set_admin_mode();





$site->start_page("services","Comptabilité comptoirs");

$cts = new contents("Tableau factures");


$sql = new requete($site->db, 
"SELECT ".
"`asso`.`nom_asso`,".
"`asso`.`id_asso`,".
"EXTRACT( YEAR_MONTH FROM `date_facture` ) AS `month`, ".
"CONCAT( `id_assocpt` , '-', EXTRACT( YEAR_MONTH FROM `date_facture` ) ) AS `C` , ".
"TRUNCATE(SUM( `prix_unit` * `quantite` ) /100,2) as `somme` ".
"FROM `cpt_vendu` ".
"INNER JOIN cpt_debitfacture ".
"USING ( `id_facture` ) ".
"INNER JOIN asso ON asso.id_asso = cpt_vendu.id_assocpt ".
"WHERE id_produit NOT ".
"IN ( 40, 41, 42, 43 ) ".
"GROUP BY `C` ".
"ORDER BY `month`");


$headers = array(); // Nom des colonnes
$table = array(); // Contenu du tableau

while ( $row = $sql->get_row() )
{
	
	$asso = $row["id_asso"];	
	$month = $row["month"];
	
	if ( !isset($table[$month]) )
		$table[$month] = array("mois"=>substr($month,4,2)."/".substr($month,0,4));
		
	if ( !isset($headers["a".$asso]) )
		$headers["a".$asso] = $row["nom_asso"];
		
	$table[$month]["a".$asso] = $row["somme"];
	
}

asort($headers);

$headers = array_merge(array("mois"=>"Mois"),$headers);


$cts->add(new sqltable(
	"compta", 
	"Comptabilité comptoirs", $table, "factures.php", 
	"mois", 
	$headers, 
	array("genfact"=>"Factures"), 
	array(),
	array( )
	));






$site->add_contents($cts);
$site->end_page();





?>
