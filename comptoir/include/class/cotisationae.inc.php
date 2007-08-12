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
	var $prevdate;
	
	var $db;
	var $dbrw;
	
	function cotisationae($db,$dbrw,$param,&$user)
	{
		$this->db = $db;
		$this->dbrw = $dbrw;	 
	 
	  $year  = date("Y");
    $month = date("m");
    
	  if ( $user->ae )
	  {
  		$req = new requete($this->db,
    		"SELECT date_fin_cotis ".
    		"FROM `ae_cotisations` " .
    		"WHERE `id_utilisateur`='".$user->id."' " .
    		"ORDER BY `date_fin_cotis` DESC LIMIT 1");	 
      if ( $req->lines == 1 )
      {  
    		list($curend) = $req->get_row();
    		$this->prevdate=strtotime($curend);	   
  		  $year  = date("Y",$this->prevdate);
        $month = date("m",$this->prevdate);
      }
	  }
	  
	  if ( $month < 2 ) // janvier => aout année -1
	  {
	    $year--; 
	    $month = 8;
	  }
	  else if ( $month < 7 ) // février, mars, avril, mai, juin => février année
	  {
	    $month = 2; 
	  }
	  else // juillet, aout, sept, octobre, novembre, décembre => aout année
	  {
	    $month = 8; 
	  }
	  
	  $month += intval($param);
	  
	  if ( $month > 12 )
	  {
	    $year++;
	    $month -= 12; 
	  }

		$this->enddate = mktime ( 2, 0, 0, $month, 15 , $year );
		

	}	
	
	function vendu($user,$prix_unit)
	{
		$cotisation = new cotisation($this->db,$this->dbrw);
		$cotisation->add ( $user->id, $this->enddate, 5, $prix_unit );
	}
	
	function get_info()
	{
	  return "Cotisation à l'AE jusqu'au ".date("d/m/Y",$this->enddate);
	}
	
	function get_once_sold_cts($user)
	{
	 // On affiche la date "précédente", vu que la cotisation a déjà été fait, $this->enddate corresponderait à une nouvelle cotisation 
		$cts = new contents("Vous venez de cotiser à l'AE jusqu'au ".date("d/m/Y",$this->prevdate));
		$cts->add_paragraph("Pensez à venir retirer votre cadeau et votre carte AE au bureau de l'AE.");
		$cts->add_paragraph("Assurez vous d'avoir une photo d'indentité dans votre profil pour que votre carte puisse être imprimée.");
		$cts->add_paragraph("Pensez à mettre à jour votre profil dans le matmatronch.");
		$cts->add_paragraph("Merci d'avoir cotisé à l'AE.");
		return $cts;
	}
	
	function can_be_sold($user)
	{
		if ( !$user->utbm )
			return false;
			
		/*if ( !$user->ae )
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
			return true;*/
		
		return true;
	}
	
	function is_compatible($cl)
	{
		if ( get_class($cl) == "cotisationae" )
			return false;
			
		return true;
	}
	
} 
 
 
 
?>
