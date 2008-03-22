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
$topdir = "../";

require_once($topdir. "include/site.inc.php");

$site = new site ();

if ( !$site->user->is_in_group("gestion_ae") )
	error_403();

if ( $_REQUEST["action"] == "process")
{
  function regaccent ($mot)
  {
    $pattern2 = str_replace("e","(e|é|è|ê|ë|É|È|Ê|Ë)",$mot);
    $pattern2 = str_replace("a","(a|à|â|ä|À|Â|Ä)",$pattern2);
    $pattern2 = str_replace("i","(i|ï|î|Ï|Î)",$pattern2);
    $pattern2 = str_replace("c","(c|ç|Ç)",$pattern2);
    $pattern2 = str_replace("u","(u|ù|ü|û|Ü|Û|Ù)",$pattern2);
    $pattern2 = str_replace("n","(n|ñ|Ñ)",$pattern2);
    return $pattern2;
  }
  
  
  
  $traduc = array(
    "GP"=>"imap",
    "GM"=>"mc",
    "GI"=>"gi",
    "GC"=>"gesc");
  
  $user = new utilisateur($site->db,$site->dbrw);
  
	$lines = explode("\n",$_REQUEST["data"]);
	
	echo "<ul>";

	foreach ( $lines as $line)
	{
	 
    $data = explode(";",$line);
    $user->id = NULL;
    
    $nom = strtolower(trim($data[0]));  
    $prenom = strtolower(trim($data[1]));  
    
    if ( trim($data[2]) )
      $naissance = datetime_to_timestamp(trim($data[2]));  
    else
      $naissance = null;
    
    echo "<li>$nom $prenom : ";

    if ( !is_null($naissance) )
    {
      $req = new requete($site->db, 
        "SELECT * FROM `utilisateurs`
        WHERE `nom_utl` REGEXP '^" . mysql_real_escape_string(regaccent($nom)) . "$'
        AND `prenom_utl` REGEXP '^" . mysql_real_escape_string(regaccent($prenom)) . "$'
        AND `date_naissance_utl` = '".date("Y-m-d",$naissance)."'");
      echo $req->lines." line(s) ";
    }
    
    if ( is_null($naissance) || $req->lines == 0 )
    {
      $req = new requete($site->db, 
        "SELECT * FROM `utilisateurs`
        WHERE `nom_utl` REGEXP '^" . mysql_real_escape_string(regaccent($nom)) . "$'
        AND `prenom_utl` REGEXP '^" . mysql_real_escape_string(regaccent($prenom)) . "$'");
      echo ", ".$req->lines." line(s) ";
    }
    
    if ( $req->lines == 1 )
    {
      $user->_load($req->get_row());
      $user->load_all_extra();
      if ( !is_null($naissance) && $naissance != $user->date_naissance )
        $user->date_naissance = $naissance;
      $user->role="etu";
      $user->departement = $traduc[trim($data[3])];  
      $user->filiere = strtolower(trim($data[4]));  
      $user->date_diplome_utbm = datetime_to_timestamp(trim($data[5]));  
      $user->became_etudiant ( "UTBM", true, true );
      $user->saveinfos();
      $user->add_to_group(42);//Nouveaux diplomé
      echo " : Updated";
      $updated++;
    }
    else
    {
      echo " : Not found";
      $notfound++;
    }
    echo "</li>";

	}
	
	echo "<li>$updated Updated, $notfound Not found</li>";
	echo "</ul>";
	
	exit();
}

$site->start_page("services","Diplomés");

$cts = new contents("Import CSV(;)");


$frm = new form("process","maj_diplomes.php");
$frm->add_hidden("action","process");
$frm->add_text_area("data","Données");
$frm->add_submit("valide","Ajouter");
$cts->add($frm);


$site->add_contents($cts);
$site->end_page();



?>
