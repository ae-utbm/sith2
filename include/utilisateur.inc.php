<?php
/** @file Gestion des utilisateurs
 *
 *
 */
require_once("carteae.inc.php");

/**
 * Classe permetant la gestion d d'un utilisateur
 */
class utilisateur
{

	var $db;
	var $dbrw;

	/** Tableau associatif regroupant les groupes dont l'utilisateur est membre.
	 * Associe les id des groupes à leur nom.
	 * @see load_groups
	 */
	var $groupes;

	/** Tableau associatif des paramètres utilisateur
	 * @see load_params
	 */
	var $params;

	/* table utilisateurs */
	var $id;
	var $nom;
	var $prenom;
	var $email;
	var $pass;
	var $hash;
	var $sexe;
	var $date_naissance;
	var $addresse;
	var $ville;
	var $cpostal;
	var $pays;
	var $tel_maison;
	var $tel_portable;
	var $alias;

	var $utbm;
	var $etudiant;
	var $ancien_etudiant;
	var $ae;
	var $modere;

	var $droit_image;
	var $montant_compte;
	var $site_web;

	var $date_maj;
  var $derniere_visite;
  
  /** Profil visible de tous, recherchable dans le mmt online */
  var $publique;
  
  /** Publication autorisée dans le matmatronch papier */
  var $publique_mmtpapier;
  
  
  
  
	/* etudiant */
	var $citation;
	var $adresse_parents;
	var $ville_parents;
	var $cpostal_parents;
	var $pays_parents;
	var $tel_parents;
	var $nom_ecole_etudiant;

	/* utbm */
	var $semestre;
	var $branche;
	var $filiere;
	var $surnom;
	var $email_utbm;
	var $promo_utbm;
	var $date_diplome_utbm; /* si etudiant */


	var $_grps;

	function utilisateur ( $db, $dbrw = false )
	{
		$this->db = $db;
		$this->dbrw = $dbrw;
		$this->id = -1;
		$this->groupes = false;
	}

	/** Charge un utilisateur en fonction de son id
	 * En cas d'erreur, l'id est défini à -1
	 * @param $id id de l'utilisateur
	 */
	function load_by_id ( $id )
	{
		$req = new requete($this->db, "SELECT * FROM `utilisateurs`
				WHERE `id_utilisateur` = '" . mysql_real_escape_string($id) . "'
				LIMIT 1");

		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;
	}

	/** Charge un utilisateur en fonction de son adresse email personnelle,
	 * ou de son adresse mail utbm.
	 * En cas d'erreur, l'id est défini à -1
	 * @param $email adresse email de l'utilisateur
	 */
	function load_by_email ( $email )
	{
		/*if ( intval($email)==$email)
		{
			$this->load_by_id($email);
			return;
		}*/

		$req = new requete($this->db, "SELECT `utilisateurs`.* FROM `utilisateurs` " .
				"LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur` = `utilisateurs`.`id_utilisateur` " .
				"WHERE `utilisateurs`.`email_utl` = '" . mysql_real_escape_string($email) . "' OR " .
				"`utl_etu_utbm`.`email_utbm` = '" . mysql_real_escape_string($email) . "' " .
				"LIMIT 1");

		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;



	}

	/** Charge un utilisateur en fonction de son alias
	 * En cas d'erreur, l'id est défini à -1
	 * @param $alias alias de l'utilisateur
	 */
	function load_by_alias ( $alias )
	{

		$req = new requete($this->db, "SELECT * FROM `utilisateurs`
				WHERE `alias_utl` = '" . mysql_real_escape_string($alias) . "'
				LIMIT 1");

		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;

	}
	/**
	 * Charge un utilisateur en fonction de son numéro de carte AE.
	 * En cas d'erreur, l'id est défini à -1
	 * @param $num numéro de carte
	 */
	function load_by_carteae ( $num )
	{
		list($id,$extra) = explode(" ",$num);

		$req = new requete($this->db, "SELECT * FROM `utilisateurs` " .
				"INNER JOIN `ae_cotisations` ON `ae_cotisations`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
				"INNER JOIN `ae_carte` ON `ae_cotisations`.`id_cotisation`=`ae_carte`.`id_cotisation` " .
				"WHERE `ae_carte`.`id_carte_ae` = '" . mysql_real_escape_string($id) . "' " .
				"AND `ae_carte`.`etat_vie_carte_ae`<=".CETAT_EXPIRE." " .
				"LIMIT 1");

		if ( $req->lines == 1 )
		{
			$this->_load($req->get_row());
			return;
		}

		$req = new requete($this->db, "SELECT * FROM `ae_carte`
				WHERE `id_carte_ae` = '" . mysql_real_escape_string($id) . "' AND " .
				"`etat_vie_carte_ae`>=".CETAT_PERDUE." ".
				"LIMIT 1");
		if ( $req->lines == 1 )
		{
			$this->id = -2;
			return;
		}

		$this->id = -1;
	}
	/**
	 * Charge un utilisateur en fonction de son id de cotisation.
	 * En cas d'erreur, l'id est défini à -1
	 * @param $id_cotisation id de la cotisation
	 */
	function load_by_cotisation ( $id_cotisation )
	{
		$req = new requete($this->db, "SELECT * FROM `utilisateurs` " .
				"INNER JOIN `ae_cotisations` ON `ae_cotisations`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
				"WHERE `ae_cotisations`.`id_cotisation` = '" . mysql_real_escape_string($id_cotisation) . "' " .
				"LIMIT 1");

		if ( $req->lines == 1 )
			$this->_load($req->get_row());
		else
			$this->id = -1;
	}

	/** Determine si un alias est disponible.
	 * @param $alias Alias à tester (sauf pour l'utilisateur en cours)
	 * @return true si disponible, false sinon
	 */
	function is_alias_avaible ( $alias )
	{
		$req = new requete($this->db, "SELECT * FROM `utilisateurs` " .
				"WHERE `alias_utl` = '" . mysql_real_escape_string($alias) . "' " .
				"AND `utilisateurs`.`id_utilisateur`!='".$this->id."'");

		if  ( $req->lines != 0 ) return false;

		$req = new requete($this->db, "SELECT * FROM `asso`
				WHERE `nom_unix_asso` = '" . mysql_real_escape_string($alias) . "'
				LIMIT 1");

		if  ( $req->lines != 0 ) return false;

		return true;
	}

	/** Determine si une adresse email est disponible
	 * @param $email Adresse email à tester (sauf pour l'utilisateur en cours)
	 * @return true si disponible, false sinon
	 */
	function is_email_avaible ( $email )
	{

		$req = new requete($this->db, "SELECT `utilisateurs`.* FROM `utilisateurs` " .
				"LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur` = `utilisateurs`.`id_utilisateur` " .
				"WHERE `utilisateurs`.`email_utl` = '" . mysql_real_escape_string($email) . "' OR " .
				"`utl_etu_utbm`.`email_utbm` = '" . mysql_real_escape_string($email) . "' " .
				"AND `utilisateurs`.`id_utilisateur`!='".$this->id."'");

		return ( $req->lines == 0 );
	}

	/**
	 * Charge un utilisateur en fonction des données renvoyés par un champ user_field.
	 * En cas d'erreur, l'id est défini à -1.
	 * @param $pattern Données envoyés par le champ.
	 * @see add_user_field
	 * @see 	add_suggested_text_field
	 */
	function load_by_pattern ( $pattern )
	{
		$sql = "SELECT `utilisateurs`.*, `utl_etu`.*, `utl_etu_utbm`.* " .
				"FROM `utilisateurs` " .
				"LEFT JOIN `utl_etu` ON `utl_etu`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
				"LEFT JOIN `utl_etu_utbm` ON `utl_etu_utbm`.`id_utilisateur`=`utilisateurs`.`id_utilisateur` " .
				"WHERE CONCAT(`prenom_utl`,' ',`nom_utl`) LIKE '".mysql_escape_joker_string($pattern)."%' OR " .
				"CONCAT(`nom_utl`,' ',`prenom_utl`) LIKE '".mysql_escape_joker_string($pattern)."%' OR " .
				"CONCAT(`alias_utl`,' (',`prenom_utl`,' ',`nom_utl`,')') LIKE '".mysql_escape_joker_string($pattern)."%' OR " .
				"CONCAT(`surnom_utbm`,' (',`prenom_utl`,' ',`nom_utl`,')') LIKE '".mysql_escape_joker_string($pattern)."%' " .
				"ORDER BY nom_utl,prenom_utl";

		$req = new requete($this->db,$sql);

		if ( $req->lines == 1 )
			$this->_load_all($req->get_row());
		else
			$this->id = -1;

		return $req;
	}


	function _load ( $row )
	{
	  $this->id = $row['id_utilisateur'];
		$this->nom = $row['nom_utl'];
		$this->prenom = $row['prenom_utl'];
		$this->email = $row['email_utl'];
		$this->pass = $row['pass_utl'];
		$this->hash = $row['hash_utl'];
		$this->sexe = $row['sexe_utl'];
		$this->date_naissance = strtotime($row['date_naissance_utl']);
		$this->addresse = $row['addresse_utl'];
		$this->ville = $row['ville_utl'];
		$this->cpostal = $row['cpostal_utl'];
		$this->pays = $row['pays_utl'];
		$this->tel_maison = $row['tel_maison_utl'];
		$this->tel_portable = $row['tel_portable_utl'];
		$this->alias = $row['alias_utl'];
		$this->utbm = $row['utbm_utl'];
		$this->etudiant = $row['etudiant_utl'];
		$this->ancien_etudiant = $row['ancien_etudiant_utl'];
		$this->ae = $row['ae_utl'];
		$this->modere = $row['modere_utl'];
		$this->droit_image = $row['droit_image_utl'];
		$this->montant_compte = $row['montant_compte'];
		$this->site_web = $row['site_web'];

		if ( $row['date_maj_utl'] )
			$this->date_maj = strtotime($row['date_maj_utl']);
		else
			$this->date_maj = null;
			
		if ( is_null($row['derniere_visite_utl']) )
		  $this->derniere_visite = null;
    else			
		  $this->derniere_visite = strtotime($row['derniere_visite_utl']);
			
    $this->publique = $row['publique_utl'];  
    $this->publique_mmtpapier = $row['publique_mmtpapier_utl'];
			
			
	}


	function _load_all ( $row )
	{
		$this->_load($row);

		if ( $this->etudiant || $this->ancien_etudiant )
		{
			$this->citation = $row["citation"];
			$this->adresse_parents = $row["adresse_parents"];
			$this->ville_parents = $row["ville_parents"];
			$this->cpostal_parents = $row["cpostal_parents"];
			$this->pays_parents = $row["pays_parents"];
			$this->tel_parents = $row["tel_parents"];
			$this->nom_ecole_etudiant = $row["nom_ecole_etudiant"];
		}
		else
		{
			unset($this->adresse_parents);
			unset($this->ville_parents);
			unset($this->cpostal_parents);
			unset($this->pays_parents);
			unset($this->tel_parents);
			unset($this->nom_ecole_etudiant);
		}

		if ( $this->utbm )
		{
			$this->semestre = $row["semestre_utbm"];
			$this->branche = $row["branche_utbm"];
			$this->filiere = $row["filiere_utbm"];
			$this->surnom = $row["surnom_utbm"];
			$this->email_utbm = $row["email_utbm"];
			$this->promo_utbm = $row["promo_utbm"];
			$this->date_diplome_utbm = !is_null($row["date_diplome_utbm"])?strtotime($row["date_diplome_utbm"]):null;
		}
		else
		{
			unset($this->semestre);
			unset($this->branche);
			unset($this->filiere);
			unset($this->surnom);
			unset($this->email_utbm);
			unset($this->promo_utbm);
			unset($this->date_diplome_utbm);
		}
	}
	
	/** 
	 */
	function visite ( )
	{
		$req = new update($this->dbrw,"utilisateurs",array("derniere_visite_utl"=>date("Y-m-d H:i:s")),array("id_utilisateur"=>$this->id));
	}
	
	/** Active un compte en attente
	 */
	function validate ( )
	{
		$this->hash = "valid";
		$req = new update($this->dbrw,"utilisateurs",array("hash_utl"=>$this->hash),array("id_utilisateur"=>$this->id));
	}

	/** Desactive un compte, pour revalidation
	 */
	function invalidate ( )
	{
		$this->hash = md5(genere_pass(20));
		$req = new update($this->dbrw,"utilisateurs",array("hash_utl"=>$this->hash),array("id_utilisateur"=>$this->id));
	}

	/** Determine si le mot de passe précisé est le bon
	 * @param $password Mot de passe à tester
	 * @return true si le mot de passe est correct, false sinon
	 */
	function is_password ( $password )
	{
		if ($this->pass == crypt($password, substr($this->pass,0,2) ))
			return true;
		return false;
	}

	/** Change le mot de passe de l'utilisateur
	 * @param $$new_password Nouveau mot de passe
	 */
	function change_password ( $new_password )
	{
		$this->pass = crypt($new_password, "ae");
		$req = new update($this->dbrw,"utilisateurs",array("pass_utl"=>$this->pass),array("id_utilisateur"=>$this->id));
	}


	/* GROUPS management */
	/** Change les groupes dont l'utilisateur fait parti
	 * @see is_in_group
	 * @see is_in_group_id
	 */
	function load_groups ()
	{
		$this->groupes = array();

		$req = new requete($this->db, "SELECT `groupe`.`id_groupe`,`groupe`.`nom_groupe` FROM `utl_groupe`
				INNER JOIN `groupe` ON `utl_groupe`.`id_groupe` = `groupe`.`id_groupe`
				WHERE `utl_groupe`.`id_utilisateur` = '" . mysql_real_escape_string($this->id) . "'");

		// 1XXXX [flag]
		// 2XXXX [asso]-bureau
		// 3XXXX [asso]-membres

		while ( list($id,$name) = $req->get_row() )
			$this->groupes[$id] = $name;

		if ( $this->ae == true ) $this->groupes[10000] = "ae-membres";
		if ( $this->utbm == true ) $this->groupes[10001] = "utbm";
		if ( $this->ancien_etudiant == true ) $this->groupes[10002] = "ancien_etudiant";
		if ( $this->etudiant == true ) $this->groupes[10003] = "etudiant";

		$req = new requete($this->db,
			"SELECT `asso`.`id_asso`, `asso`.`nom_unix_asso` " .
			"FROM `asso_membre` " .
			"INNER JOIN `asso` ON `asso`.`id_asso`=`asso_membre`.`id_asso` " .
			"WHERE `asso_membre`.`id_utilisateur`='".$this->id."' " .
			"AND `asso_membre`.`date_fin` is NULL " .
			"AND `asso_membre`.`role` > 1 " .
			"ORDER BY `asso`.`nom_asso`");

		while ( list($id,$name) = $req->get_row() )
			$this->groupes[$id+20000] = $name."-bureau";

		$req = new requete($this->db,
			"SELECT `asso`.`id_asso`, `asso`.`nom_unix_asso` " .
			"FROM `asso_membre` " .
			"INNER JOIN `asso` ON `asso`.`id_asso`=`asso_membre`.`id_asso` " .
			"WHERE `asso_membre`.`id_utilisateur`='".$this->id."' " .
			"AND `asso_membre`.`date_fin` is NULL " .
			"AND `asso`.`id_asso_parent` IS NOT NULL " .
			"ORDER BY `asso`.`nom_asso`");

		while ( list($id,$name) = $req->get_row() )
			$this->groupes[$id+30000] = $name."-membres";

		if ( !isset($this->promo_utbm) )
			$this->load_all_extra();

		if ( $this->promo_utbm > 0 )
			$this->groupes[$this->promo_utbm+40000] = "promo".sprintf("%02d",$this->promo_utbm)."-membres";

	}
	/** Determine si l'utilisateur est membre du groupe précisé.
	 * (Charge automatiquement les groupes)
	 * @param $name nom du groupe
	 * @return true si l'utilisateur est membre, false sinon
	 * @see is_in_group_id
	 */
	function is_in_group ( $name )
	{
		if ( !$this->groupes )
			$this->load_groups();

		return in_array($name,$this->groupes);
	}

	/** Determine si l'utilisateur est membre du groupe précisé
	 *  (Charge automatiquement les groupes)
	 * @param $id id du groupe
	 * @return true si l'utilisateur est membre, false sinon
	 * @see is_in_group
	 */
	function is_in_group_id ( $id )
	{
		if ( !$this->groupes )
			$this->load_groups();

		return isset($this->groupes[$id]);
	}

	/**
	 * Renvoie la liste des id des groupes dont fait parti l'utilisateur séparés par des virgules
	 */
	function get_groups_csv ( )
	{
		if ( $this->_grps )
			return $this->_grps;

		if ( !$this->groupes )
			$this->load_groups();

		$this->_grps ="";
		foreach ( $this->groupes as $id => $n )
		{
			if ( $this->_grps ) $this->_grps .= ",";
			$this->_grps .= $id;
		}

		if ( $this->_grps == "" ) // Pour éviter tout un tas de bugs
			$this->_grps ="0";

		return $this->_grps;
	}

	/* Extra infos management */
	/** Change toutes les informations secondaires de l'utilisateur
	 */
	function load_all_extra ()
	{
		$req = new requete($this->db, "SELECT * FROM `utl_etu`
				WHERE `id_utilisateur` = '" . mysql_real_escape_string($this->id). "'
				LIMIT 1");

		if ( ($req->lines == 1) && ($this->etudiant || $this->ancien_etudiant) )
		{
			$row = $req->get_row();
			$this->citation = $row["citation"];
			$this->adresse_parents = $row["adresse_parents"];
			$this->ville_parents = $row["ville_parents"];
			$this->cpostal_parents = $row["cpostal_parents"];
			$this->pays_parents = $row["pays_parents"];
			$this->tel_parents = $row["tel_parents"];
			$this->nom_ecole_etudiant = $row["nom_ecole_etudiant"];
		}

		$req = new requete($this->db, "SELECT * FROM `utl_etu_utbm`
				WHERE `id_utilisateur` = '" . mysql_real_escape_string($this->id). "'
				LIMIT 1");

		if ( ($req->lines == 1) && $this->utbm )
		{
			$row = $req->get_row();
			$this->semestre = $row["semestre_utbm"];
			$this->branche = $row["branche_utbm"];
			$this->filiere = $row["filiere_utbm"];
			$this->surnom = $row["surnom_utbm"];
			$this->email_utbm = $row["email_utbm"];
			$this->promo_utbm = $row["promo_utbm"];
			$this->date_diplome_utbm = ($row["date_diplome_utbm"]!=NULL)?strtotime($row["date_diplome_utbm"]):null;
		}
	}

	/**
	 * Sauve les informations de l'utilisateur.
	 * Au vu du nombre d'informations, le passage se fait par les variables de l'objet.
	 */
	function saveinfos ()
	{
               global $topdir;
		$req = new update($this->dbrw,
		  	"utilisateurs",
			array(
				'nom_utl' => $this->nom,
				'prenom_utl' => $this->prenom,
				'sexe_utl' => $this->sexe,
				'date_naissance_utl' => date("Y-m-d",$this->date_naissance),
				'addresse_utl' => $this->addresse,
				'ville_utl' => $this->ville,
				'cpostal_utl' => $this->cpostal,
				'pays_utl' => $this->pays,
				'tel_maison_utl' => $this->tel_maison,
				'tel_portable_utl' => $this->tel_portable,
				'alias_utl' => $this->alias,
				'droit_image_utl' => $this->droit_image==true,
				'site_web' => $this->site_web,
				'date_maj_utl' => date("Y-m-d H:i:s",$this->date_maj),
				'publique_utl'=> $this->publique, 
        'publique_mmtpapier_utl'=>$this->publique_mmtpapier
				),
			array( 'id_utilisateur' => $this->id));

		if ( $this->etudiant || $this->ancien_etudiant )
		{
			$req = new update($this->dbrw,
			  	"utl_etu",
				array(
					'citation' => $this->citation,
					'adresse_parents' => $this->adresse_parents,
					'ville_parents' => $this->ville_parents,
					'cpostal_parents' => $this->cpostal_parents,
					'pays_parents' => $this->pays_parents,
					'tel_parents' => $this->tel_parents,
					'nom_ecole_etudiant' => $this->nom_ecole_etudiant
					),
				array( 'id_utilisateur' => $this->id));

		}

		if ( $this->utbm )
		{
			$req = new update($this->dbrw,
			  	"utl_etu_utbm",
				array(
					'semestre_utbm' => $this->semestre,
					'branche_utbm' => $this->branche,
					'filiere_utbm' => $this->filiere,
					'surnom_utbm' => $this->surnom,
					'promo_utbm' => $this->promo_utbm,
					'date_diplome_utbm'=> ($this->date_diplome_utbm!=NULL)?date("Y-m-d H:i:s",$this->date_diplome_utbm):NULL
					),
				array( 'id_utilisateur' => $this->id));
		}

		if ( XML_RPC_USE )
		{
              require_once($topdir . "include/inscriptions/xmlrpc-client.inc.php");
	      $ch = new ClientHelper("mmt", "08084e11");
		      $ret = $ch->addUser($this->nom, $this->prenom, $this->email, $this->sexe,
                $this->branche, $this->semestre, date("Y-m-d",$this->date_naissance));

              if ( $ret == FALSE )
			    return false;
			  else
			    return true;
		}

		return true;
	}

	/**
	 * Transforme l'utilisateur en utilisateur UTBM
	 * @param $email_utbm Adresse utbm de l'utilisateur (requise!)
	 * @param $admin Précise si la modification a été faite par un administrateur, si c'est le cas le compte ne sera pas invalidé
	 */
	function became_utbm ( $email_utbm, $admin=false  )
	{
		$this->utbm=true;
		$this->became_etudiant();
		$req = new insert($this->dbrw,"utl_etu_utbm",array("id_utilisateur" => $this->id,"email_utbm"=>$email_utbm));
		$req = new update($this->dbrw,"utilisateurs",array("utbm_utl"=>$this->utbm),array("id_utilisateur"=>$this->id));

		if ( $admin ) return;

		$this->invalidate();
		$this->send_activation_email($email_utbm);
	}

	/**
	 * Transforme l'utilisateur en étudiant
	 * @param $ancien Précise s'il s'ahit d'un ancien étudiant
	 */
	function became_etudiant ( $ancien=false )
	{
		$req = new insert($this->dbrw,"utl_etu",array("id_utilisateur" => $this->id));
		$req = new update($this->dbrw,"utilisateurs",array("etudiant_utl"=>$this->etudiant,"ancien_etudiant_utl"=>$this->ancien_etudiant),array("id_utilisateur"=>$this->id));
		$this->etudiant = !$ancien;
		$this->ancien_etudiant = $ancien;
	}

	/** Gnration de mot de passe
	 * Cette fonction va gnrer une chane alatoire de la longueur
	 * spcifie. C'est notamment utile pour gnrer des mots de passe.
	 *
	 * @param nameLength Longueur de la chane
	 *
	 * @return La chane alatoire
	 */
	function genere_pass ($nameLength=12)
	{
	  $NameChars = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKMNLOP';
	  $Vouel = 'aeiouAEIOU';
	  $Name = "";

	  for ($index = 1; $index <= $nameLength; $index++)
	    {
			if ($index % 3 == 0)
			{
				$randomNumber = rand(1,strlen($Vouel));
				$Name .= substr($Vouel,$randomNumber-1,1);
			}
			else
			{
				$randomNumber = rand(1,strlen($NameChars));
				$Name .= substr($NameChars,$randomNumber-1,1);
			}
		}

	return $Name;

	}

	function set_droit_image ( $droit_image )
	{
		$this->droit_image = $droit_image;

		$req = new update($this->dbrw,
		  	"utilisateurs",
			array(
				'droit_image_utl' => $this->droit_image
				),
			array( 'id_utilisateur' => $this->id));


		if ( $droit_image )
		{
			$sql = new update ($this->dbrw,
				"sas_personnes_photos",
				array("accord_phutl"=> true),
				array("id_utilisateur"=>$this->id)
				);

			$sql = new requete($this->dbrw, "UPDATE sas_photos SET droits_acquis=1 " .
					"WHERE droits_acquis=0 AND incomplet=0 AND " .
					"(SELECT COUNT(*) FROM `sas_personnes_photos` " .
				"WHERE sas_personnes_photos.`id_photo`=sas_photos.id_photo " .
				"AND `accord_phutl`='0' " .
				"AND `modere_phutl`='1')=0");
		}
	}

	function new_utbm_user ( $nom, $prenom, $email, $emailutbm, $alias, $password, $semestre, $branche, $promo, $etudiant, $droit_image, $nom_ecole, $date_naissance = null , $sexe = 1)
	{
          $this->nom = convertir_nom($nom);
          $this->prenom = convertir_prenom($prenom);

          /* On inscrit un mec UTBM */
          if (!empty($emailutbm) && $emailutbm) {
            if (CheckEmail($emailutbm,1)) {
              $this->email_utbm = $emailutbm;
              $this->utbm = true;
            } else {
              $this->id = -1;
              return FALSE;
            }
          } elseif ($nom_ecole == "UTBM") {
            $this->utbm = true;
          } else {
            $this->utbm = false;
            $this->email_utbm = null;
          }

          if (!$email || empty($email))
            $this->email = $emailutbm;
          else
            $this->email = $email;

          $this->alias = $alias;

          if (!$password)
            $password = genere_pass(7);
          $this->pass = crypt($password, "ae");
          $this->etudiant = $etudiant==true;

          $this->modere = true;
          $this->sexe = $sexe;
          if ($date_naissance)
            $this->date_naissance = $date_naissance;

          $this->publique = true;
          $this->publique_mmtpapier = true;
          
	  $this->droit_image = $droit_image;

          $sql = new insert ($this->dbrw,
                             "utilisateurs",
                             array(
                                   "nom_utl" => $this->nom,
                                   "prenom_utl" => $this->prenom,
                                   "email_utl" => $this->email,
                                   "alias_utl" => $this->alias,
                                   "pass_utl" => $this->pass,
                                   "hash_utl" => "",
                                   "sexe_utl" => $this->sexe,
                                   "date_naissance_utl" => date("Y-m-d",$this->date_naissance),
                                   "etudiant_utl" => $this->etudiant,
                                   "utbm_utl" => $this->utbm,
                                   "droit_image_utl" => $this->droit_image,
                                   "ancien_etudiant_utl"=> false,
                                   "ae_utl"=>false,
                                   "modere_utl"=> $this->modere,
                                   "montant_compte"=> 0,
                                   'publique_utl'=> $this->publique, 
                                   'publique_mmtpapier_utl'=>$this->publique_mmtpapier
                                   )
                             );

          if ( $sql )
            $this->id = $sql->get_id();
          else
            {
              $this->id = -1;
              return false;
            }

          $this->set_droit_image($this->droit_image);
          $this->invalidate ();

          if ($this->etudiant && $nom_ecole)
            {
              $this->nom_ecole_etudiant = $nom_ecole;

              $req = new insert($this->dbrw,"utl_etu",
                                array(
                                      "id_utilisateur" => $this->id,
                                      "nom_ecole_etudiant" => $this->nom_ecole_etudiant
                                      ));

              if (!$req) {
                $this->id = -1;
                return false;
              }
            }

          if (strtoupper($nom_ecole) == "UTBM")
            {
              $this->promo_utbm = $promo;
              $this->branche = $branche;
              $this->semestre = $semestre;
              $this->email_utbm = $emailutbm;
              $req = new insert($this->dbrw,"utl_etu_utbm",
                                array("id_utilisateur" => $this->id,
                                      "semestre_utbm"  => $this->semestre,
                                      "branche_utbm"   => $this->branche,
                                      "promo_utbm"     => $this->promo_utbm,
                                      "email_utbm"     => $this->email_utbm
                                      ));
            }

          if ( $this->email_utbm )
            $this->send_first_email($this->email_utbm,$password);
          else if ( $this->email )
            $this->send_first_email($this->email,$password);

          return true;
	}

    function set_email ( $email, $admin=false )
	{
		$this->email = $email;

		$req = new update($this->dbrw,
		  	"utilisateurs",
			array(
				'email_utl' => $this->email
				),
			array( 'id_utilisateur' => $this->id));

		if ( $admin ) return;

		$this->invalidate();
		$this->send_activation_email($email);
	}

	function set_email_utbm ( $email, $admin=false  )
	{
		$this->email_utbm = $email;

		$req = new update($this->dbrw,
		  	"utl_etu_utbm",
			array(
				'email_utbm' => $this->email_utbm
				),
			array( 'id_utilisateur' => $this->id));

		if ( $admin ) return;

		$this->invalidate();
		$this->send_activation_email($email);
	}

	function send_activation_email ( $email )
	{

  $body = "Bonjour,
Votre adresse email a été changée.

Pour valider votre adresse email et reactiver votre compte, veuillez vous rendre à l'adresse
http://ae.utbm.fr/confirm.php?id=" . $this->id . "&hash=" . $this->hash . "

L'équipe info AE";

		$ret = mail($email, "[Site AE] Activation de votre compte", utf8_decode($body),
                            "From: \"AE UTBM\" <ae@utbm.fr>\nReply-To: ae@utbm.fr");

	}

	function send_first_email ( $email, $password )
	{

  $body = "Bonjour,
Votre compte a été crée sur le site de l'AE
Votre mot de passe: $password

Pour activer votre compte, veuillez vous rendre à l'adresse
http://ae.utbm.fr/confirm.php?id=" . $this->id . "&hash=" . $this->hash . "

L'équipe info AE";

		$ret = mail($email, "[Site AE] Votre compte sur le site de l'AE", utf8_decode($body),
                            "From: \"AE UTBM\" <ae@utbm.fr>\nReply-To: ae@utbm.fr");

	}

	/** Recharge le montant du compte de l'utilisateur courant
	 *
	 */
	function refresh_solde ()
	{
		$req = new requete($this->db, "SELECT * FROM `utilisateurs`
				WHERE `id_utilisateur` = '" . $this->id . "'
				LIMIT 1");

		if ( $req->lines == 1 )
		{
			$row = $req->get_row();
			$this->montant_compte = $row['montant_compte'];
		}
	}


	/** Credite le compte AE de l'utilisateur
	 * @param $id_operateur Id de l'opérateur ayant réalisé l'opération
	 * @param $type_paiement Mode de paiement
	 * @param $banque Id de la banque
	 * @param $valeur Montant du chargement
	 * @param $id_cptasso Id du compte association qui a perçu la somme (devrai toujours être AE=1)
	 * @param $id_comptoir Id du comptoir où a été encaissé la somme
	 * @return true si le rechargement a réussi, false sinon
	 * @todo à tester
	 */
	function crediter ($id_operateur,
				$type_paiement,
				$banque,
				$valeur,
				$id_assocpt,
				$id_comptoir)
	{

		if ( !$this->dbrw ) // On est en lecture seule
			return false;


		$sql = new insert($this->dbrw,
			"cpt_rechargements",
			array(
				"id_utilisateur"=>$this->id,
				"id_comptoir" => $id_comptoir,
				"id_utilisateur_operateur" => $id_operateur,
				"id_assocpt" => $id_assocpt,
				"montant_rech" => $valeur,
				"type_paiement_rech" => $type_paiement,
				"banque_rech" => $banque,
				"date_rech" => date("Y-m-d H:i:s")
				)
			);

		if ( !$sql )
			return false;

		$sql2 = new requete($this->dbrw,"UPDATE `utilisateurs`
	          SET `montant_compte` = `montant_compte` + $valeur
	          WHERE `id_utilisateur` = '" . $this->id ."'");

		$sql3 = new requete($this->dbrw,"UPDATE `cpt_association`
	        SET `montant_rechargements_asso` = `montant_rechargements_asso` + $valeur
	        WHERE `id_assocpt` = '" . $id_assocpt ."'");

		$this->refresh_solde();

		return true;
	}

	/** Annule un rechargement
	 * @param $id Identifiant du rechargement
	 * @todo à tester
	 */
	function annuler_credit ( $id )
	{
		if ( !$this->dbrw ) // On est en lecture seule
			return false;

		$sql = new requete($this->dbrw,"SELECT * FROM `cpt_rechargements` " .
				"WHERE `id_rechargement` = '" . intval($id) ."'");

		if ( $sql->lines != 1 )
			return false;

		$row = $sql->get_row();

		$sql = new requete($this->dbrw,"UPDATE `utilisateurs` " .
				"SET `montant_compte` = `montant_compte` - " . $row['montant_rech'] ." " .
				"WHERE `id_utilisateur` = '" . $row['id_utilisateur'] ."'");

		$sql = new requete($this->dbrw,"UPDATE `cpt_association` " .
				"SET `montant_rechargements_asso` = `montant_rechargements_asso` - " . $row['montant_rech'] ." " .
				"WHERE `id_assocpt` = '" . $row['id_assocpt'] ."'");

		$sql = new delete($this->dbrw, "cpt_rechargements", array("id_rechargement"=>$row['id_rechargement']));

		return true;
	}

	function credit_suffisant ( $prix )
	{
		return $this->montant_compte >= $prix;
	}

	/**
	 * Ajoute un parrain à l'utilisateur
	 * @param $id_utilisateur Id du parrain
	 */
	function add_parrain ( $id_utilisateur )
	{

		$sql = new insert($this->dbrw,
			"parrains",
			array(
				"id_utilisateur"=> $id_utilisateur,
				"id_utilisateur_fillot" => $this->id
				)
			);
	}

	/**
	 * Ajoute un fillot à l'utilisateur
	 * @param $id_utilisateur Id du fillot
	 */
	function add_fillot ( $id_utilisateur )
	{

		$sql = new insert($this->dbrw,
			"parrains",
			array(
				"id_utilisateur"=> $this->id,
				"id_utilisateur_fillot" => $id_utilisateur
				)
			);


	}

	/**
	 * Enlève un parrain à l'utilisateur
	 * @param $id_utilisateur Id du parrain
	 */
	function remove_parrain ( $id_utilisateur )
	{

		$sql = new delete($this->dbrw,
			"parrains",
			array(
				"id_utilisateur"=> $id_utilisateur,
				"id_utilisateur_fillot" => $this->id
				)
			);
	}

	/**
	 * Enlève un fillot à l'utilisateur
	 * @param $id_utilisateur Id du fillot
	 */
	function remove_fillot ( $id_utilisateur )
	{


		$sql = new delete($this->dbrw,
			"parrains",
			array(
				"id_utilisateur"=> $this->id,
				"id_utilisateur_fillot" => $id_utilisateur
				)
			);


	}

	/**
	 * Enlève l'utilisateur a un groupe
	 * @param $id_group Id du groupe
	 */
	function add_to_group ( $id_group )
	{
		if ( $this->is_in_group_id($id_group) ) return;

		if ( $id_group >= 10000 ) return;

		$sql = new insert($this->dbrw,"utl_groupe",
						array (
							"id_utilisateur" => $this->id,
							"id_groupe" => $id_group
						));
	}

	/**
	 * Enlève l'utilisateur d'un groupe
	 * @param $id_group Id du groupe
	 */
	function remove_from_group ( $id_group )
	{
		if ( !$this->is_in_group_id($id_group) ) return;

		if ( $id_group >= 10000 ) return;

		$sql = new delete($this->dbrw,"utl_groupe",
						array (
							"id_utilisateur" => $this->id,
							"id_groupe" => $id_group
						));
	}

	/** Charge tous les paramètres de l'utilisateur.
	 * ATTENTION: ceci est UNIQUEMENT concu pour stocker des paramètres et non des informations sur l'utilisateur !
	 * @private
	 */
	function load_params()
	{
		$this->params = array();

		$req = new requete($this->db, "SELECT `nom_param`,`valeur_param` " .
				"FROM `utl_parametres` " .
				"WHERE `id_utilisateur` = '" . mysql_real_escape_string($this->id) . "'");

		while ( list($id,$name) = $req->get_row() )
			$this->params[$id] = $name;

	}

	/**
	 * Obtient un paramètre pour l'utilisateur.
	 * @param $name Nom du paramètre
	 * @param $value $default par défaut retrouné si il n'est pas définit
	 */
	function get_param ( $name, $default=null )
	{
		if ( $this->id < 1 )
			return $default;

		if ( !$this->params )
			$this->load_params();

		if ( !isset($this->params[$name]) )
			return $default;

		return unserialize($this->params[$name]);
	}

	/**
	 * Définit un paramètre pour l'utilisateur.
	 * @param $name Nom du paramètre
	 * @param $value Valeur du paramètre.
	 */
	function set_param ( $name, $value )
	{
		if ( !$this->params )
			$this->load_params();

		$value = serialize($value);

		if ( !isset($this->params[$name]) )
		{
			$sql = new insert($this->dbrw,"utl_parametres",
				array (
					"id_utilisateur" => $this->id,
					"nom_param" => $name,
					"valeur_param" => $value
				));
		}
		elseif ( $this->params[$name] !== $value )
		{
			$sql = new update($this->dbrw,"utl_parametres",
				array( "valeur_param" => $value),
				array("id_utilisateur" => $this->id, "nom_param" => $name));
		}
		$this->params[$name] = $value;
	}

	/** Determine la liste des associations dans les quelles l'utilisateur a au moins le rôle spécifié.
	 * @param $role Role minimum (=0 par défaut)
	 * @return la liste associative des ids avec le nom des associations. La liste vide si aucune.
	 */
	function get_assos ( $role = 0)
	{
		$assos=array();
		$req = new requete($this->db,
			"SELECT `asso`.`id_asso`, `asso`.`nom_asso` " .
			"FROM `asso_membre` " .
			"INNER JOIN `asso` ON `asso`.`id_asso`=`asso_membre`.`id_asso` " .
			"WHERE `asso_membre`.`id_utilisateur`='".intval($this->id)."' " .
			"AND `asso_membre`.`date_fin` is NULL " .
			"AND `asso_membre`.`role`>='".intval($role)."' " .
			"ORDER BY `asso`.`nom_asso`");

		while ( list($id,$value) = $req->get_row() ) $assos[$id] = $value;

		return $assos;
	}

	/**
	 * Renvoie la liste des id des association dont fait parti l'utilisateur séparés par des virgules
	 * @param $role Role minimum (=0 par défaut)
	 */
	function get_assos_csv ( $role=0 )
	{
		$assos = $this->get_assos($role);

		$csv ="";
		foreach ( $assos as $id => $n )
		{
			if ( $csv ) $csv .= ",";
			$csv .= $id;
		}

		if ( empty($csv) ) // Pour éviter tout un tas de bugs
			return "0";

		return $csv;
	}

	/** Determine si l'utilisteur est actuellemnt membre d'une association et occupe un poste spécial
	 * @param $id_asso	ID de l'association
	 * @param $role	Role minimum à occuper
	 * @return true si vrai, false sinon
	 */
	function is_asso_role ( $id_asso, $role )
	{
		$req = new requete($this->db, "SELECT * FROM `asso_membre`
				WHERE `id_asso` = '" . mysql_real_escape_string($id_asso) . "'
				AND `id_utilisateur` = '" . mysql_real_escape_string($this->id) . "'
				AND `date_fin` is NULL AND `role` >= '".mysql_real_escape_string($role)."'
				LIMIT 1");

		return ($req->lines == 1);
	}



	function output_vcard()
	{
		global $topdir;

		echo "BEGIN:VCARD\n";
		echo "VERSION:3.0\n";
		echo "N;CHARSET=UTF-8:".$this->nom.";".$this->prenom.";;;\n";
		echo "FN;CHARSET=UTF-8:".$this->prenom." ".$this->nom."\n";
		echo "REV: ".date("YmdHi")."\n";
		echo "UID: aeutbm-utl-".$this->id."\n";

		if ( $this->surnom )
			echo "NICKNAME;CHARSET=UTF-8:".$this->surnom."\n";
		else if ( $this->alias )
			echo "NICKNAME;CHARSET=UTF-8:".$this->alias."\n";

		echo "EMAIL;type=INTERNET;type=HOME:".$this->email."\n";

		if ( $this->email_utbm && ($this->email_utbm != $this->email ) )
			echo "EMAIL;type=INTERNET;type=WORK:".$this->email_utbm."\n";

		if ( $this->tel_maison )
			echo "TEL;type=HOME:".$this->tel_maison."\n";

		if ( $this->tel_portable )
			echo "TEL;type=CELL:".$this->tel_portable."\n";

		if ( $this->tel_parents )
			echo "TEL;type=HOME:".$this->tel_parents."\n";

		if ( $this->date_naissance )
			echo "BDAY;value=date:".date("Y-m-d",$this->date_naissance)."\n";

		if ( $this->addresse )
		{
			echo "item1.ADR;CHARSET=UTF-8;type=HOME:;;".$this->addresse.";".$this->ville.";;".$this->cpostal.";".$this->pays."\n";
			echo "item1.X-ABADR:fr\n";
		}
		if ( $this->addresse_parents )
		{
			echo "item2.ADR;CHARSET=UTF-8;type=HOME:;;".$this->addresse_parents.";".$this->ville_parents.";;".$this->cpostal_parents.";".$this->pays_parents."\n";
			echo "item2.X-ABADR:fr\n";
		}

		if ( file_exists($topdir."var/img/matmatronch/".$this->id.".jpg"))
		{
			echo "PHOTO;TYPE=JPEG;BASE64:\n";
			echo "  ".chunk_split(base64_encode(file_get_contents($topdir."var/img/matmatronch/".$this->id.".jpg")),76,"\n  ");
			echo "\n";
		}
		echo "END:VCARD\n";





	}
}

?>
