<?php
/*
 * Created on 12 ao�t 2006
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
require_once($topdir. "include/entities/cotisation.inc.php");
 
class cotisationae
{
	var $enddate;
	
	var $db;
	var $dbrw;
	
	function cotisationae($db,$dbrw,$param)
	{
		$this->enddate = strtotime($param);
		
		$this->db = $db;
		$this->dbrw = $dbrw;
	}	
	
	function vendu($user,$prix_unit)
	{
		$cotisation = new cotisation($this->db,$this->dbrw);
		$cotisation->add ( $user->id, $this->enddate, 5, $prix_unit );
	}
	
	function get_once_sold_cts($user)
	{
		$cts = new contents("Vous venez de cotiser à l'AE jusqu'au ".date("d/m/Y",$this->enddate));
		$cts->add_paragraph("Pensez à venir retirer votre cadeau et votre carte AE au bureau de l'AE.");
		$cts->add_paragraph("Assurez vous d'avoir une photo d'indentité dans votre profil pour que votre carte puisse être imprimée.");
		$cts->add_paragraph("Pensez à mettre à jour votre profil dans le matmatronch.");
		$cts->add_paragraph("Merci d'avoir cotisé à l'AE.");
		return $cts;
	}
	
	function can_be_sold($user)
	{
		if ( !$user->ae )
			return true;
			
		$req = new requete($this->db,
		"SELECT *".
		"FROM `ae_cotisations` " .
		"WHERE `id_utilisateur`='".$user->id."' " .
		"ORDER BY `date_cotis` DESC LIMIT 1");
		
		if ( $req->lines != 1 )
			return true;
			
		list($curend) = $req->get_row();
		
		$curend=strtotime($curend);
		
		if ( $curend < $this->enddate )
			return true;
		
		return false;
	}
	
	function is_compatible($cl)
	{
		if ( get_class($cl) == "cotisationae" )
			return false;
			
		return true;
	}
	
} 
 
 
 
?>
