<?php

$GLOBALS['types_machines'] = array('laver' => "Machine à laver", 'secher' => "Seche linge");

/**
 * Class gérant un jeton
 */
class machine extends stdentity
{

  var $lettre;
  var $type;
  var $id_salle;
  var $hs;
  
	/** Charge un jeton en fonction de son id
	 * $this->id est égal à -1 en cas d'erreur
	 * @param $id id du jeton
	 */
	function load_by_id ( $id )
	{
		$req = new requete($this->db, "SELECT * FROM `mc_machines`
				WHERE `id` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1");	
				
		if ( $req->lines == 1 )
		{
			$this->_load($req->get_row());
			return true;
		}
		
		$this->id = -1;	
		return false;
	}
	
	function load_by_id_creneau ( $id, &$debut )
	{
		$req = new requete($this->db, "SELECT mc_machines.*, mc_creneaux.debut_creneau  FROM mc_creneaux
		    INNER JOIN `mc_machines` ON (mc_machines.id=mc_creneaux.id_machine)
				WHERE `id_creneau` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1");	
				
		if ( $req->lines == 1 )
		{
		  $row = $req->get_row();
		  $debut = strtotime($row['debut_creneau']);
			$this->_load($row);
			return true;
		}
		
		$this->id = -1;	
		return false;
	}
	
	function _load ( $row )
	{
	  $this->id = $row['id']; // devrai être id_machine
	  $this->lettre = $row['lettre']; // devrai être lettre_machine
	  $this->type = $row['type'];  // devrai être type_machine
	  $this->id_salle = $row['loc']; // devrai être id_salle 
	  $this->hs = $row['hs']; // devrai être hs_machine 
	}
	
	function create_machine ( $lettre, $type, $id_lieu, $hs=false )
	{
	 
	 
	 
	}
	
	function update_machine ( $lettre, $type, $id_lieu, $hs=false )
	{
	 
	 
	 
	}
	
	function create_creaneau ( $debut, $fin )
	{
	  new insert ( $this->dbrw, "mc_creneaux",
	   array(
	     "id_machine"=>$this->id,
	     "debut_creneau"=>date("Y-m-d H:i:s",$debut),
	     "fin_creneau"=>date("Y-m-d H:i:s",$fin)));
	}
	
	function create_all_creneaux_between ( $start, $end, $step )
	{
    $current = $start;
    while ( $current < $end )
    {
      $this->create_creaneau($current,$current+$step);
      $current += $step;
    }
	}
	
	function take_creneau ( $id_creneau, $id_utilisateur, $force=false )
	{
	  if ( $force )
	  {
  	  new update ( $this->dbrw, "mc_creneaux", 
  	    array("id_utilisateur"=>$id_utilisateur), 
  	    array("id_machine"=>$this->id,"id_creneau"=>$id_creneau)); 
	    return; 
	  } 
	 
	  new update ( $this->dbrw, "mc_creneaux", 
	    array("id_utilisateur"=>$id_utilisateur), 
	    array("id_machine"=>$this->id,"id_creneau"=>$id_creneau,"id_utilisateur"=>null)); 
	}
	
	function affect_jeton_creneau ( $id_creneau, $id_utilisateur, $id_jeton )
	{
	  new update ( $this->dbrw, "mc_creneaux", 
	    array("id_utilisateur"=>$id_utilisateur, "id_jeton"=>$id_jeton), 
	    array("id_machine"=>$this->id,"id_creneau"=>$id_creneau)); 
	}
	
}




?>