<?
/* Copyright 2005
 * - Pierre Mauduit <pierre POINT mauduit CHEZ utbm POINT fr>
 * - Julien Etelain <julien POINT etelain CHEZ utbm POINT fr>
 *
 * Ce fichier fait partie du site de l'Association des Ãtudiants de
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
require_once("../include/carteae.inc.php"); // Requis pour les constantes

/*
 * Note : Ce script se suffit a lui-meme. Aucune interaction avec les classes
 * d'une quelconque version ne doit etre faite.
 * On ne livre pas les mots de passe etant donne que ae2 n'est pas packagï¿½.
 */

$c_h = mysql_connect("localhost", "ae_read_write", "TazPkEr");



/*
 * @brief vidange des tables de la v2
 *
 *
 */
function purge()
{
  mysql_select_db("ae2");
  $sql = "TRUNCATE TABLE `utilisateurs`";
  mysql_query($sql);
  $sql = "TRUNCATE TABLE `utl_etu`";
  mysql_query($sql);
  $sql = "TRUNCATE TABLE `utl_etu_utbm`";
  mysql_query($sql);
  $sql = "TRUNCATE TABLE `ae_cotisations`";
  mysql_query($sql);
  $sql = "TRUNCATE TABLE `ae_carte`";
  mysql_query($sql);
}
function purge_groups()
{
  mysql_select_db("ae2");
  $sql = "TRUNCATE TABLE `groupe`";
  mysql_query($sql);
  $sql = "TRUNCATE TABLE `utl_groupe`";
  mysql_query($sql);
}



/* @brief fonction d'import tablesv1 -> tablesv2 des infos
 * utilisateurs / infos persos / cotisations / cartes ae
 *
 */
function import_infos_utl()
{
  /* ancienne base */
  mysql_select_db("ae");
  /* LA requete */
  
  $missing="2894,2889,2887,2886,2895,2897";
  
  $sql_query = mysql_query("SELECT etudiants.*,etudiants_infos.*,cpt_comptes.*,ae_inscrits.*,`etudiant_groupe`.`id_groupe` FROM `etudiants` " .
  						"LEFT JOIN `etudiants_infos` ON `etudiants_infos`.`id` = `etudiants`.`id` " .
						"LEFT JOIN `cpt_comptes` ON `cpt_comptes`.`id_client` = `etudiants`.`id` " .
                          "LEFT JOIN `ae_inscrits` ON `ae_inscrits`.`id` = `etudiants`.`id`" .
                          "LEFT JOIN `etudiant_groupe` ON (`etudiant_groupe`.`id_etudiant` = `etudiants`.`id` AND `etudiant_groupe`.`id_groupe` = '19' ) " .
                          "WHERE `etudiants`.`id` IN ($missing)");
  echo mysql_error();

  /* tableV2 utilisateurs */
  mysql_select_db("ae2");



  /* analyse des rï¿½sultats et dispatchage dans les diffï¿½rentes tablesv2 */
  for ($i = 0; $i < mysql_num_rows($sql_query); $i++)
    {
      /* stockage temporaire du resultat */
      $result = mysql_fetch_array($sql_query);

      $flag = array();

      $flag['ae_utl'] = 0;
      $flag['utbm_utl'] = 1;
      $flag['etudiant_utl'] = 0;
      $flag['ancien_etudiant_utl'] = 0;

      if ( $result['date_val'] )
      	$flag['ae_utl'] = 1;

      if ( $result['id_groupe'] == 19 )
      	$flag['ancien_etudiant_utl'] = 1;
      else
      	$flag['etudiant_utl'] = 1;

      $sql_utilisateurs = "INSERT INTO `utilisateurs` VALUES ('".$result[0]."', '"
	.mysql_escape_string(utf8_encode($result['nom']))."', '"
	.mysql_escape_string(utf8_encode($result['prenom']))."', '"
	.mysql_escape_string($result['email'])."', '"
	.$result['pass']."', '"
	.$result['hash']."', '"
	.$result['sexe']."', '"
	.$result['naissance']."', '"
	.mysql_escape_string(utf8_encode($result['adresse']))."', '"
	.mysql_escape_string(utf8_encode($result['ville']))."', '"
	.mysql_escape_string(utf8_encode($result['CP']))."', '"
	.mysql_escape_string(utf8_encode($result['pays']))."', '"
	.mysql_escape_string(utf8_encode($result['tel']))."', '"
	.mysql_escape_string(utf8_encode($result['tel_portable']))."', '"
	.mysql_escape_string(utf8_encode($result['surnom']))."', '"
	.$flag['utbm_utl']."', '"
	.$flag['etudiant_utl']."', '"
	.$flag['ancien_etudiant_utl']."', '"
	.$flag['ae_utl']."', '"
	."1', '"
	.$result['droit_img']."', '"
	.$result['montant']."', "
	."'http://')";
      echo $sql_utilisateurs . "\n";
	mysql_query($sql_utilisateurs); echo mysql_error();
      /* table V2 utl_etu */
      $sql_etu = "INSERT INTO `utl_etu` VALUES ('"
	.$result[0] . "', '"
	.mysql_escape_string(utf8_encode($result['citation'])) . "', '"
	.mysql_escape_string(utf8_encode($result['adresse_parents'])) . "', '"
	.mysql_escape_string(utf8_encode($result['ville_parents'])) . "', '"
	.mysql_escape_string(utf8_encode($result['CP_parents'])) . "', '"
	.mysql_escape_string(utf8_encode("")) . "', '"
	.mysql_escape_string(utf8_encode($result['Tel_parents'])) . "', "
	."'UTBM'" .")";

      echo $sql_etu . "\n";
      mysql_query($sql_etu); echo mysql_error();
      /* table V2 utl_etu_utbm */
      $sql_utl_etu_utbm = "INSERT INTO `utl_etu_utbm` VALUES ('"
	. $result[0] ."', '"
	. $result['semestre'] ."', '"
	. $result['branche'] ."', '"
	. mysql_escape_string($result['filiere']) ."', '"
	. mysql_escape_string(utf8_encode($result['surnom']))  ."', '"
	. $result['email'] . "', '"
	. $result['promo'] . "')";
      echo $sql_utl_etu_utbm . "\n";
      mysql_query($sql_utl_etu_utbm); echo mysql_error();

     if ( $result['date_val'] )
     {

	      $sql_cotisation = "INSERT INTO `ae_cotisations` VALUES ('"
		. $result[0] ."', '"
		. $result[0] ."', NOW(), '"
		. $result['date_val'] ."', '"
		. $result['tshirt']  ."', '"
		. $result['carte']  ."', '"
		. $result['paiement']  ."', '"
		. 0 . "')";
     	echo $sql_cotisation . "\n";
     	mysql_query($sql_cotisation); echo mysql_error();

     	$etat_vie_carte_ae = CETAT_ATTENTE;
     	if ( $result['carte'] )
     		$etat_vie_carte_ae = CETAT_CIRCULATION;
     	else if ( $result['impr'] )
     		$etat_vie_carte_ae = CETAT_AU_BUREAU_AE;
     	/* Etat déprécié
     	 * else if ( file_exists("../../siteae/var/img/matmatronch/".$result['id'].".jpg") )
     		$etat_vie_carte_ae = CETAT_A_PRODUIRE;
			*/
			
	      $sql_carteae = "INSERT INTO `ae_carte` VALUES ('"
		. $result[0] ."', '"
		. $result[0] ."', '"
		. $etat_vie_carte_ae ."', '"
		. $result['date_val'] ."')";


     	echo $sql_carteae . "\n";

     	mysql_query($sql_carteae); echo mysql_error();
     }

    }
}

/*
 * Import des groupes
 *
 *
 */
function import_groups ()
{
  /* base v1 */
  mysql_select_db("ae");
  $sql = "SELECT * FROM `etudiant_groupe`";
  $rs = mysql_query($sql);

  mysql_select_db("ae2");

  for ($i = 0; $i < mysql_num_rows($rs); $i++)
    {
      $row = mysql_fetch_array($rs);
      $sql_ae2 = "INSERT INTO `utl_groupe` VALUES (".
	$row['id_groupe'].", ".$row['id_etudiant'].")";
      mysql_query($sql_ae2);
      echo mysql_error();
    }

  /* descriptions et definitions des groupes */
  mysql_select_db("ae");
  $sql = "SELECT * FROM `groupes`";
  $rs = mysql_query($sql);
 mysql_select_db("ae2");

  for ($i = 0; $i < mysql_num_rows($rs); $i++)
    {
      $row = mysql_fetch_array($rs);
      $sql_ae2 = "INSERT INTO `groupe` VALUES (".
	$row['id'].", '".
	mysql_real_escape_string($row['nom'])."', '".
	mysql_real_escape_string($row['description']) ."')";
      mysql_query($sql_ae2);
      echo mysql_error();
    }
}


echo "<pre>";
//purge();
//purge_groups();

import_infos_utl();
//import_groups();

echo "</pre>";

?>