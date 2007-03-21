<?php
/** @file
 * @addtogroup inventaire
 * @{
 */

/** 
 * Class gérant les salles
 */
class salle
{
	var $id;
	/** @see batiment */ 
	var $id_batiment;
	var $nom;
	var $etage;
	var $fumeur;
	var $convention;
	var $reservable;
	var $surface;
	var $tel;
	var $notes;
	
	var $db;
	var $dbrw;
	
	function salle ( $db, $dbrw = false )
	{
		$this->db = $db;
		$this->dbrw = $dbrw;
		$this->id = -1;
	}
	
	/** Charge une salle en fonction de son id
	 * $this->id est égal à -1 en cas d'erreur
	 * @param $id id de la fonction
	 */
	function load_by_id ( $id )
	{
		$req = new requete($this->db, "SELECT * FROM `sl_salle`
				WHERE `id_salle` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1");	
				
		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;	
	}
	
	function _load ( $row )
	{
		$this->id			= $row['id_salle'];
		$this->id_batiment	= $row['id_batiment'];
		$this->nom			= $row['nom_salle'];
		$this->etage			= $row['etage'];
		$this->fumeur		= $row['salle_fumeur'];
		$this->convention	= $row['convention_salle'];
		$this->reservable	= $row['reservable'];
		$this->surface		= $row['surface_salle'];
		$this->tel			= $row['tel_salle'];
		$this->notes			= $row['notes_salle'];
	}
	
	/** Ajoute une salle et le charge dans l'instance
	 * @param $id_site Id du batiment dans le quel se trouve la salle
	 * @param $nom Nom de la salle
	 * @param $etage Etage de la salle
	 * @param $fumeur (Booléen) Fumeur ou non
	 * @param $convention (Booléen) Convention de locaux requise
	 * @param $reservable (Booléen) Reservavble
	 * @param $surface Surface de la salle (en m2)
	 * @param $tel N° de telephone (si présent) de la salle
	 * @param $notes Notes (libres)
	 */
	function add ( $id_batiment, $nom, $etage, $fumeur, $convention, $reservable, $surface, $tel, $notes )
	{
		$this->id_batiment	= $id_batiment;
		$this->nom			= $nom;
		$this->etage			= $etage;
		$this->fumeur		= is_null($fumeur)?false:$fumeur;
		$this->convention	= is_null($convention)?false:$convention;
		$this->reservable	= is_null($reservable)?false:$reservable;
		$this->surface		= $surface;
		$this->tel			= $tel;
		$this->notes			= $notes;	
		
		$sql = new insert ($this->dbrw,
			"sl_salle",
			array(
				"id_batiment" => $this->id_batiment,
				"nom_salle" => $this->nom,
				"etage" => $this->etage,
				"salle_fumeur" => $this->fumeur,
				"convention_salle" => $this->convention,
				"reservable" => $this->reservable,
				"surface_salle" => $this->surface,
				"tel_salle" => $this->tel,
				"notes_salle" => $this->notes
				)
			);
				
		if ( $sql )
			$this->id = $sql->get_id();
		else
			$this->id = -1;
			
	}	
	
	/** Ajouter une association dans la salle
	 * @param $id_asso Id de l'association
	 */
	function add_asso ( $id_asso )
	{
		
		$sql = new insert ($this->dbrw,
			"sl_association",
			array(
				"id_salle" => $this->id,
				"id_asso" => $id_asso
				)
			);
		
	}
	
	/** Enlève une association de la salle
	 * @param $id_asso Id de l'association
	 */
	function remove_asso ( $id_asso )
	{
		
		$sql = new delete ($this->dbrw,
			"sl_association",
			array(
				"id_salle" => $this->id,
				"id_asso" => $id_asso
				)
			);
		
	}	
	
	/** Met à jour une salle
	 * @param $nom Nom de la salle
	 * @param $etage Etage de la salle
	 * @param $fumeur (Booléen) Fumeur ou non
	 * @param $convention (Booléen) Convention de locaux requise
	 * @param $reservable (Booléen) Reservavble
	 * @param $surface Surface de la salle (en m2)
	 * @param $tel N° de telephone (si présent) de la salle
	 * @param $notes Notes (libres)
	 */
	function update ( $nom, $etage, $fumeur, $convention, $reservable, $surface, $tel, $notes )
	{
		$this->nom			= $nom;
		$this->etage			= $etage;
		$this->fumeur		= is_null($fumeur)?false:$fumeur;
		$this->convention	= is_null($convention)?false:$convention;
		$this->reservable	= is_null($reservable)?false:$reservable;
		$this->surface		= $surface;
		$this->tel			= $tel;
		$this->notes			= $notes;	
		
		$sql = new update ($this->dbrw,
			"sl_salle",
			array(
				"id_batiment" => $this->id_batiment,
				"nom_salle" => $this->nom,
				"etage" => $this->etage,
				"salle_fumeur" => $this->fumeur,
				"convention_salle" => $this->convention,
				"reservable" => $this->reservable,
				"surface_salle" => $this->surface,
				"tel_salle" => $this->tel,
				"notes_salle" => $this->notes
				),
			array(
				"id_salle"=>$this->id
				)
			);

			
	}		
	
	
}

/**
 * Reservation de salle
 */
class reservation
{
	var $id;
	var $id_utilisateur;
	var $id_utilisateur_op;
	var $id_salle;
	var $id_asso;
	var $date_demande;
	var $date_debut;
	var $date_fin;
	var $date_accord;
	var $description;
	var $convention;
	var $etat;
	var $notes;
	
	function reservation ( $db, $dbrw = false )
	{
		$this->db = $db;
		$this->dbrw = $dbrw;
		$this->id = -1;
	}
	
	function load_by_id ( $id )
	{
		$req = new requete($this->db, "SELECT * FROM `sl_reservation`
				WHERE `id_salres` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1");	
				
		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;	
	}
		
	
	function _load ( $row )
	{
		$this->id = $row['id_salres'];
		$this->id_utilisateur = $row['id_utilisateur'];
		$this->id_utilisateur_op = $row['id_utilisateur_op'];
		$this->id_salle = $row['id_salle'];
		$this->id_asso = $row['id_asso'];
		$this->date_demande = strtotime($row['date_demande_res']);
		$this->date_debut = strtotime($row['date_debut_salres']);
		$this->date_fin = strtotime($row['date_fin_salres']);
		if( $row['date_accord_res'])
			$this->date_accord = strtotime($row['date_accord_res']);
		else
			$this->date_accord = NULL;
		$this->description = $row['description_salres'];
		$this->convention = $row['convention_salres'];
		$this->etat = $row['etat_salres'];
		$this->notes = $row['notes_salres'];
		
		
		
	}
	
	
	
	function est_disponible ( $id_salle, $debut, $fin )
	{
		$req = new requete($this->db,
				"SELECT COUNT(*) " .
				"FROM `sl_reservation` WHERE " .
				"!(`date_debut_salres` > '".date("Y-m-d H:i:s",$fin-1)."' OR " .
				"`date_fin_salres` < '".date("Y-m-d H:i:s",$debut)."') ".
				"AND id_salle='".$id_salle."'");
				
		list($count) = $req->get_row();
		
		return ($count == 0);
	}
	
	function est_disponible_hors_non_accord ( $id_salle, $debut, $fin )
	{
		$req = new requete($this->db,
				"SELECT COUNT(*) " .
				"FROM `sl_reservation` WHERE " .
				"!(`date_debut_salres` > '".date("Y-m-d H:i:s",$fin-1)."' OR " .
				"`date_fin_salres` < '".date("Y-m-d H:i:s",$debut)."') ".
				"AND id_salle='".$id_salle."' AND `date_accord_res` IS NOT NULL");
				
		list($count) = $req->get_row();
		
		return ($count == 0);
	}
	
	function add ( $id_salle, $id_utilisateur, $id_asso, $debut, $fin, $description )
	{
		
		if ( !$this->est_disponible($id_salle,$debut,$fin) )	
			return false;
		
		$this->id_utilisateur = $id_utilisateur;
		$this->id_utilisateur_op = NULL;
		$this->id_salle = $id_salle;
		$this->id_asso = $id_asso;
		$this->date_demande =time();
		$this->date_debut = $debut;
		$this->date_fin = $fin;
		$this->date_accord = NULL;
		$this->description = $description;
		$this->convention = false;
		$this->etat = 0;
		$this->notes = "";
		
		$sql = new insert ($this->dbrw,
			"sl_reservation",
			array(
				"id_utilisateur" => $this->id_utilisateur,
				"id_utilisateur_op" => $this->id_utilisateur_op,
				"id_salle" => $this->id_salle,
				"id_asso" => $this->id_asso,
				"date_demande_res" => date("Y-m-d H:i:s",$this->date_demande),
				"date_debut_salres" => date("Y-m-d H:i:s",$this->date_debut),
				"date_fin_salres" => date("Y-m-d H:i:s",$this->date_fin),
				"date_accord_res" => NULL,
				"description_salres" => $this->description,
				"convention_salres" => $this->convention,
				"etat_salres" => $this->etat,
				"notes_salres" => $this->notes
				)
			);
				
		if ( $sql )
			$this->id = $sql->get_id();
		else
			$this->id = -1;
			
		return true;
	}
	
	function convention_done ()
	{
		$this->convention = true;
		
		$sql = new update ($this->dbrw,
			"sl_reservation",
			array(
				"convention_salres" => $this->convention
				),
			array(
				"id_salres" => $this->id
				)
			);
		
	}
	
	function accord ( $id_utilisateur )
	{
		$this->date_accord = time();
		$this->id_utilisateur_op = $id_utilisateur;
		
		$sql = new update ($this->dbrw,
			"sl_reservation",
			array(
				"id_utilisateur_op" => $this->id_utilisateur_op,
				"date_accord_res" => date("Y-m-d H:i:s",$this->date_accord)
				),
			array(
				"id_salres" => $this->id
				)
			);
	}
	
	function set_notes($notes)
	{
		$this->notes = $notes;
		
		$sql = new update ($this->dbrw,
			"sl_reservation",
			array(
				"notes_salres" => $notes
				),
			array(
				"id_salres" => $this->id
				)
			);	
	}
	
	
	
	function delete ()
	{
		
		$sql = new delete ($this->dbrw,
			"sl_reservation",
			array(
				"id_salres" => $this->id
				)
			);
		
	}
	
	
}
 
 
?>
