<?php
$topdir = "../";
include($topdir. "include/site.inc.php");

require_once($topdir. "include/cts/sqltable.inc.php");

$site = new site ();
$site->set_side_boxes("right",array(),"nope");
$site->set_side_boxes("left",array(),"nope");
function process_asso ( &$rows, &$db, $row )
{

  $req = new requete($db,"SELECT `utilisateurs`.`id_utilisateur`, ".
    "CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) as `nom_utilisateur`, ".
    "IF(utl_etu_utbm.email_utbm IS NULL,utilisateurs.email_utl,utl_etu_utbm.email_utbm) as `email_resp`, ".
    "IF(utilisateurs.tel_portable_utl='',utilisateurs.tel_maison_utl,utilisateurs.tel_portable_utl) as `tel_resp` ".
    "FROM asso_membre ".    "INNER JOIN utilisateurs USING(id_utilisateur) ".
    "LEFT JOIN utl_etu_utbm USING(id_utilisateur) ".
    "WHERE asso_membre.id_asso = '".$row['id_asso']."' ".    "AND asso_membre.role=10 ".    "AND asso_membre.date_fin IS NULL ".    "ORDER BY asso_membre.desc_role ".    "LIMIT 1");

  if ( $req->lines == 1 )
    $row = array_merge($row,$req->get_row());
    
  $id = $row['id_asso'];
  
  if ( is_null($row['id_asso_parent'])  )
  {
    $row['nom_asso_1'] = $row['nom_asso'];
    $row['id_asso_1'] = $row['id_asso'];
    unset($row['nom_asso']);
    unset($row['id_asso']);
  }
  else if ( $row['id_asso_parent'] ==1 )
  {
    $row['nom_asso_2'] = $row['nom_asso'];
    $row['id_asso_2'] = $row['id_asso'];
    unset($row['nom_asso']);
    unset($row['id_asso']);
  }

  $rows[] = $row;

  
  if ( $id == 3 ) return;

  $req = new requete($db,"SELECT * ".    "FROM asso ".    "WHERE asso.id_asso_parent = '".$id."' ".    "ORDER BY nom_asso");

  while ( $srow = $req->get_row() )
    process_asso($rows,$db,$srow);

}

$rows = array();

$req = new requete($site->db,"SELECT * ".  "FROM asso ".  "WHERE asso.id_asso IN (1,2,3) ".  "ORDER BY nom_asso");

while ( $srow = $req->get_row() )
  process_asso($rows,$site->db,$srow);

$site->start_page("home","Associations");

$site->add_contents( new sqltable ( "", "Responsables", $rows, "", "", 
array(
"nom_asso_1"=>"",
"nom_asso_2"=>"",
"nom_asso"=>"",
"email_asso"=>"Email",
"nom_utilisateur"=>"Responsable",
"email_resp"=>"Email",
"tel_resp"=>"Telephone",
"siteweb_asso"=>"Site web"

), array(), array(), array() ));

$site->end_page();


?>
