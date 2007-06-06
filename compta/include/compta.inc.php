<?php

/**
 * @file
 */

require_once($topdir."include/site.inc.php");

/* LA COMPTA EST EN CENTIMES !!! */

require_once("comptes.inc.php");
require_once("defines.inc.php");
require_once("operations.inc.php");
require_once("typeop.inc.php");
require_once("budget.inc.php");
require_once("libelle.inc.php");


class sitecompta extends site
{
	var $id_asso;
	var $nom_asso;
	var $id_classeur;
	var $nom_classeur;
	var $nom_cpbc;
	
	
	function sitecompta ()
	{
		global $topdir;
		
		$this->site();
		$this->set_side_boxes("left",array("compta","connexion"));

	}

	function get_libelles($id_asso, $none=true)
	{
		$req = new requete($this->db,"SELECT `id_libelle`, `nom_libelle`  FROM `cpta_libelle` WHERE `id_asso`='".intval($id_asso)."' ORDER BY `nom_libelle`");
		
		if ( $none ) 
		  $libelles=array(0=>"-");
		else 
		  $libelles=array();
		  
		while ( $row = $req->get_row() )
		  $libelles[$row[0]] = $row[1];	
		  
		return $libelles;
	}
	
	function get_typeop_clb($id_asso, $none=false,$mvt=false)
	{
		$req = new requete($this->db,"SELECT `id_opclb`, `type_mouvement`, `libelle_opclb`  FROM `cpta_op_clb` WHERE (`id_asso`='".intval($id_asso)."' OR `id_asso` IS NULL)".($mvt?" AND type_mouvement=$mvt":"")." ORDER BY `type_mouvement`,`libelle_opclb`");
		if ( $none ) $typeopclb=array(0=>"-");
		else $typeopclb=array();
		while ( $row = $req->get_row() )
		{
			if ( $row[1] == -1 )
				$typeopclb[$row[0]] = "Debit: ".$row[2];	
			else
				$typeopclb[$row[0]] = "Credit: ".$row[2];	
		}
			
		return $typeopclb;
	}

	function get_typeop_std ( $none=false,$mvt=false)
	{
		$req = new requete($this->db,"SELECT `id_opstd`, CONCAT(`code_plan`,' ',`libelle_plan`)  FROM `cpta_op_plcptl` ".($mvt?"WHERE type_mouvement=$mvt":"WHERE type_mouvement!=0"));

		if ( $none ) $typeopstd=array(0=>"-");
		else $typeopstd=array();
		
		while ( $row = $req->get_row() )
			$typeopstd[$row[0]] = $row[1];	
			
		return $typeopstd;
	}

	function get_lst_cptasso ( )
	{
		$req = new requete($this->db,"SELECT `cpta_cpasso`.`id_cptasso`, " .
				"CONCAT(`asso`.`nom_asso`,' sur ',`cpta_cpbancaire`.`nom_cptbc`)  " .
				"FROM `cpta_cpasso` " .
				"INNER JOIN `asso` ON `asso`.`id_asso` = `cpta_cpasso`.`id_asso` " .
				"INNER JOIN `cpta_cpbancaire` ON `cpta_cpbancaire`.`id_cptbc` = `cpta_cpasso`.`id_cptbc` " .
				"ORDER BY `cpta_cpbancaire`.`nom_cptbc`, `asso`.`nom_asso`");
		$typeopstd=array(0=>"-");
		while ( $row = $req->get_row() )
			$typeopstd[$row[0]] = $row[1];	
		return $typeopstd;
	}

	function get_lst_assotier ( )
	{
		$req = new requete($this->db,"SELECT `id_asso`, `nom_asso`  FROM `asso` WHERE `id_asso_parent` IS NULL ORDER BY `nom_asso`");
		$typeopstd=array(0=>"-");
		while ( $row = $req->get_row() )
			$typeopstd[$row[0]] = $row[1];	
		return $typeopstd;
	}
	function get_lst_asso ( $none=false)
	{
		$req = new requete($this->db,"SELECT `id_asso`, `nom_asso`  FROM `asso` ORDER BY `nom_asso`");
		if ( $none ) $typeopstd=array(0=>"-");
		else $typeopstd=array();
		while ( $row = $req->get_row() )
			$typeopstd[$row[0]] = $row[1];	
		return $typeopstd;
	}
	function get_lst_entreprises ( )
	{
		$req = new requete($this->db,"SELECT `id_ent`, `nom_entreprise` FROM `entreprise` ORDER BY `nom_entreprise`");
		$typeopstd=array(0=>"-");
		while ( $row = $req->get_row() )
			$typeopstd[$row[0]] = $row[1];	
		return $typeopstd;
	}
	
	function get_lst_cptbc ( )
	{
		$req = new requete($this->db,"SELECT `id_cptbc`, `nom_cptbc` FROM `cpta_cpbancaire` ORDER BY `nom_cptbc`");
		while ( $row = $req->get_row() )
			$typeopstd[$row[0]] = $row[1];	
		return $typeopstd;
	}

	function set_current ( $id_asso, $nom_asso, $id_classeur, $nom_classeur, $nom_cpbc )
	{
		$this->id_asso = $id_asso;
		$this->nom_asso = $nom_asso;
		// Pour faciliter la navigation on mémorise le dernier classeur consulté
		if ( $this->id_asso == $_SESSION['cpta_old_id_asso'] && !$id_classeur )
		{
			$this->id_classeur = $_SESSION['cpta_old_id_classeur'];
			$this->nom_classeur = $_SESSION['cpta_old_nom_classeur'];
			$this->nom_cpbc = $_SESSION['cpta_old_nom_cpbc'];
		}
		else
		{
			$this->id_classeur = $id_classeur;
			$this->nom_classeur = $nom_classeur;
			$this->nom_cpbc = $nom_cpbc;
			$_SESSION['cpta_old_id_asso'] = $this->id_asso;
			$_SESSION['cpta_old_id_classeur'] = $this->id_classeur;
			$_SESSION['cpta_old_nom_classeur'] = $this->nom_classeur;	
			$_SESSION['cpta_old_nom_cpbc'] = $this->nom_cpbc; 	
		}
	}


	function start_page ( $section, $title ) 
	{	
		global $topdir;
	    $cts = new contents("Comptabilité");

	    $sublist = new itemlist(false,"boxlist");
	    $sublist->add("Comptabilité","title");
	    $sublist->add("<a href=\"".$topdir."compta/\">Comptes et classeurs</a>");
	    $sublist->add("<a href=\"".$topdir."entreprise.php\">Entreprises</a>");
	    
	    if ( $this->id_asso )
	    {
	    		$sublist->add("<a href=\"".$topdir."compta/typeop.php?id_asso=".$this->id_asso."\">Natures (types) d'opération ".$this->nom_asso."</a>");
	    		$sublist->add("<a href=\"".$topdir."compta/libelle.php?id_asso=".$this->id_asso."\">Etiquettes ".$this->nom_asso."</a>");

	    }
	    
	    if ( $this->id_classeur )
	    {
	    		if ( $this->nom_asso != $this->nom_cpbc)
	    			$sublist->add("<a href=\"".$topdir."compta/classeur.php?id_classeur=".$this->id_classeur."\">Retour classeur ".$this->nom_classeur." ".$this->nom_asso." sur ".$this->nom_cpbc."</a>");
			else
	    			$sublist->add("<a href=\"".$topdir."compta/classeur.php?id_classeur=".$this->id_classeur."\">Retour classeur ".$this->nom_classeur." ".$this->nom_asso."</a>");
	    }
	    $cts->add($sublist);
	    
	    if ( $this->user->is_in_group("compta_admin") )
	    {
		    $sublist = new itemlist(false,"boxlist");
		    $sublist->add("Administration","title");
		    $sublist->add("<a href=\"".$topdir."compta/admin.php\">Comptes</a>");
		    $sublist->add("<a href=\"".$topdir."compta/typeop.php\">Natures (types) d'opérations</a>");
		    $cts->add($sublist);
	    }
		$this->add_box("compta",$cts);
		
		
		parent::start_page("services",$title);
	}
}




?>
