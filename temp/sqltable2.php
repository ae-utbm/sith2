<?php
$topdir="../";

require_once($topdir."include/site.inc.php");
require_once($topdir."include/cts/sqltable2.inc.php");

$site = new site();
$site->add_js("js/sqltable2.js");
$site->add_css("css/sqltable2.css");

$site->start_page("sas", "Test de sqltable2" );
$cts = new contents("table");
$sql = "SELECT " .
		"`cpta_operation`.`id_op`, " .
		"`cpta_operation`.`num_op`, " .
		"`cpta_operation`.`date_op`, " .
		"`cpta_operation`.`op_effctue`, " .
		"`cpta_operation`.`commentaire_op`, " .
		
		"`cpta_operation`.`num_cheque_op`, " .
		"`cpta_operation`.`mode_op`, " .		

		"(IF(`cpta_op_plcptl`.`type_mouvement` IS NULL,`cpta_op_clb`.`type_mouvement`,`cpta_op_plcptl`.`type_mouvement`)*`montant_op`) as `montant`, " .
		
		"`cpta_op_clb`.`libelle_opclb`, " .
		"`cpta_op_plcptl`.`code_plan`, " .
		
		"`entreprise`.`nom_entreprise`, " .
		"`entreprise`.`id_ent`, " .
		
		"`asso`.`id_asso`, " .
		"`asso`.`nom_asso`, " .

		"`cpta_cpasso`.`id_cptasso`, " .
		"CONCAT(`asso2`.`nom_asso`,' sur ',`cpta_cpbancaire`.`nom_cptbc` ) AS `nom_cptasso`, " .
		
		"`utilisateurs`.`id_utilisateur`, " .
		"CONCAT(`utilisateurs`.`prenom_utl`,' ',`utilisateurs`.`nom_utl`) as `nom_utilisateur`, " .
		
		"`cpta_libelle`.`nom_libelle` ".
		
		"FROM `cpta_operation` " .
		"LEFT JOIN `cpta_op_clb` ON `cpta_operation`.`id_opclb`=`cpta_op_clb`.`id_opclb` ".
		"LEFT JOIN `cpta_op_plcptl` ON `cpta_operation`.`id_opstd`=`cpta_op_plcptl`.`id_opstd` ".
		"LEFT JOIN `cpta_cpasso` ON `cpta_operation`.`id_cptasso`=`cpta_cpasso`.`id_cptasso` ".
		"LEFT JOIN `asso` ON `cpta_operation`.`id_asso`=`asso`.`id_asso` ".
		"LEFT JOIN `entreprise` ON `cpta_operation`.`id_ent`=`entreprise`.`id_ent` ".
		"LEFT JOIN `utilisateurs` ON `cpta_operation`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
    "LEFT JOIN `cpta_libelle` ON `cpta_operation`.`id_libelle`=`cpta_libelle`.`id_libelle` ".

		"LEFT JOIN `asso` AS `asso2` ON `cpta_cpasso`.`id_asso`=`asso2`.`id_asso` ".
		"LEFT JOIN `cpta_cpbancaire` ON `cpta_cpasso`.`id_cptbc`=`cpta_cpbancaire`.`id_cptbc` ".
		"WHERE `cpta_operation`.id_classeur='162' " .
		"ORDER BY `cpta_operation`.`num_op` DESC" ;
		

$tbl = new sqltable2("compte","Test","sqltable2.php");
$tbl->add_column_number("num_op","N°");
$tbl->add_column("date_op","Date");
$tbl->add_column("nom_libelle","Etiquette");
$tbl->add_column_price("montant","Montant");
$tbl->add_column("mode_op","Paiement");
$tbl->add_column_number("num_cheque_op","N°");
$tbl->add_column("acteur","Débiteur/Crediteur",array("nom_utilisateur","nom_entreprise","nom_asso","nom_cptasso"));
$tbl->add_column_number("code_plan","Code");
$tbl->add_column("libelle_opclb","Nature(type)");
$tbl->add_column("op_effctue","Eff.");
$tbl->add_column("commentaire_op","Commentaire");

$tbl->set_column_enumeration("op_effctue",array(0=>"Non",1=>"Oui"));
$tbl->set_column_enumeration("mode_op",array(2=>"Espèces",1=>"Chèque",3=>"Virement",4=>"Carte Bancaire"));

$tbl->set_column_action("op_effctue","done");
$tbl->set_column_action("num_op","edit");

$tbl->set_column_isdiverse("commentaire_op");

$tbl->add_action("delete","Supprimer");
$tbl->add_action("print","Imprimer");

$tbl->set_sql($site->db,"id_op",$sql);
$cts->add($tbl,true);
$site->add_contents($cts);
$site->end_page();

?>