<?php

/** @file
 * Gestion des nouvelles
 *
 */

/**
 * @defgroup newstype Type de nouvelles
 * @{
 */
/** Nouvelle informative (resultat éléction, resultat concours) associé à aucune date */
define("NEWS_TYPE_NOTICE",0);
/** Nouvelle sur un evenement ponctuel associé à une date (avec une durée <= 5 jours) */
define("NEWS_TYPE_EVENT",1);
/** Nouvelle sur une activitée hebdomadaire associé à de nombreuses dates */
define("NEWS_TYPE_HEBDO",2);
/** Nouvelle sur un appel à candidature, recherche de bénévoles, concours... associé à une date avec une durée trés longue */
define("NEWS_TYPE_APPEL",3);
/**
 * @}
 */

class nouvelle extends stdentity
{
  var $id_utilisateur;
  var $id_asso;
  var $titre;
  var $resume;
  var $contenu;
  var $date;
  var $modere;
  var $id_utilisateur_moderateur;

  var $asso_seule;
  var $id_lieu;
  
  /** Charge une nouvelle en fonction de son id
   * $this->id est égal à -1 en cas d'erreur
   * @param $id id de la fonction
   */
  function load_by_id ( $id )
  {
    $req = new requete($this->db, "SELECT * FROM `nvl_nouvelles`
				WHERE `id_nouvelle` = '" .
		       mysql_real_escape_string($id) . "'
				LIMIT 1");

    if ( $req->lines == 1 )
		{
			$this->_load($req->get_row());
			return true;
		}
		
		$this->id = null;	
		return false;
  }

  /*
   * fonction de chargement (privee)
   *
   * @param row tableau associatif
   * contenant les informations sur la nouvelle.
   *
   */
  function _load ( $row )
  {
    $this->id			= $row['id_nouvelle'];
    $this->id_utilisateur	= $row['id_utilisateur'];
    $this->id_asso		= $row['id_asso'];
    $this->titre			= $row['titre_nvl'];
    $this->resume		= $row['resume_nvl'];
    $this->contenu		= $row['contenu_nvl'];
    $this->date			= strtotime($row['date_nvl']);
    $this->modere		= $row['modere_nvl'];
    $this->id_utilisateur_moderateur	= $row['id_utilisateur_moderateur'];
    $this->type	= $row['type_nvl'];
    $this->asso_seule = $row['asso_seule_nvl'];
    $this->id_lieu = $row['id_lieu'];
  }

  /** Construit un stdcontents avec le contenu de la nouvelle
   */
  function get_contents ()
  {
    global $wwwtopdir,$topdir;
    
    $asso = new asso($this->db);
    $asso->load_by_id($this->id_asso);
    
    $cts = new contents($this->titre);
    
    if ( $asso->is_valid() )
    {
      if ( !file_exists($topdir."var/img/logos/".$asso->nom_unix.".small.png") )
        $img =  $wwwtopdir."images/default/news.small.png";
      else
        $img = "/var/img/logos/".$asso->nom_unix.".small.png";
      
      $cts->add(new image($asso->nom, $img, "newsimg"));
    }
    else
      $cts->add(new image("Nouvelle",  $wwwtopdir."images/default/news.small.png", "newsimg"));      
    
    $cts->add(new wikicontents(false,$this->contenu));
  
    $req = new requete ( $this->db,
        "SELECT * FROM nvl_dates ".
        "WHERE id_nouvelle='".$this->id."' ORDER BY date_debut_eve");
  
    if ( $req->lines == 1 )
    {
      $row = $req->get_row();
      $cts->add_paragraph("Date : le ".textual_plage_horraire(strtotime($row['date_debut_eve']),
            strtotime($row['date_fin_eve'])));
    }
    elseif ( $req->lines > 1 )
    {
      $cts->add_paragraph("Dates :");
      $lst = new itemlist();
      while ( $row = $req->get_row() )
        $lst->add("Le ".textual_plage_horraire(strtotime($row['date_debut_eve']),
            strtotime($row['date_fin_eve'])));
      $cts->add($lst);
    }
      
    if ( !is_null($this->id_lieu) && class_exists("lieu") )
    {
      $lieu = new lieu($this->db);
      $lieu->load_by_id($this->id_lieu);
      $cts->add_paragraph("Lieu : ".$lieu->get_html_link());
    }
      
    if ( $asso->is_valid() )
    {
      $cts->puts("<div class=\"clearboth\"></div>");
      $cts->add_title(2,"");
      $cts->add_paragraph(classlink($asso));
      if ( !is_null($asso->id_parent) )
        $cts->add_paragraph("Inscrivez vous pour recevoir les nouvelles de ".$asso->nom." par e-mail et participer aux discussions, c'est simple et rapide : <a href=\"".$wwwtopdir."asso.php?id_asso=".$asso->id."&amp;action=selfenroll\">cliquez ici</a>");      
    }

    return $cts;
  }
  
  /** Construit un stdcontents avec le contenu de la nouvelle
   */
  function get_contents_nobrand_flow ()
  {
    global $wwwtopdir,$topdir;

    $cts = new contents($this->titre);
    
    $cts->add(new image("Nouvelle",  $wwwtopdir."images/default/news.small.png", "newsimg"));      
    
    $cts->add(new wikicontents(false,$this->contenu));
  
    $req = new requete ( $this->db,
        "SELECT * FROM nvl_dates ".
        "WHERE id_nouvelle='".$this->id."' ORDER BY date_debut_eve");
        
    $cts->add_paragraph("Posté le ".date("d/m/Y",$this->date));
            
    if ( $req->lines == 1 )
    {
      $row = $req->get_row();
      $cts->add_paragraph("Date : le ".textual_plage_horraire(strtotime($row['date_debut_eve']),
            strtotime($row['date_fin_eve'])));
    }
    elseif ( $req->lines > 1 )
    {
      $cts->add_paragraph("Dates :");
      $lst = new itemlist();
      while ( $row = $req->get_row() )
        $lst->add("Le ".textual_plage_horraire(strtotime($row['date_debut_eve']),
            strtotime($row['date_fin_eve'])));
      $cts->add($lst);
    }
    if ( !is_null($this->id_lieu) && class_exists("lieu") )
    {
      $lieu = new lieu($this->db);
      $lieu->load_by_id($this->id_lieu);
      $cts->add_paragraph("Lieu : ".$lieu->get_html_link());
    }

    return $cts;
  }
  
  /** Supprime la nouvelle
   */
  function delete ()
  {
    if ( !$this->dbrw ) return;
    new delete($this->dbrw,"nvl_nouvelles",array("id_nouvelle"=>$this->id));
    new delete($this->dbrw,"nvl_dates",array("id_nouvelle"=>$this->id));
    $this->id = null;
  }

  /** Valide la nouvelle
   */
  function validate($id_utilisateur_moderateur)
  {
    if ( !$this->dbrw ) return;
    new update($this->dbrw,"nvl_nouvelles",array("modere_nvl"=>1,"id_utilisateur_moderateur"=>$id_utilisateur_moderateur),array("id_nouvelle"=>$this->id));
    $this->modere_nvl = 1;
    $this->id_utilisateur_moderateur = $id_utilisateur_moderateur;
  }

  /** Invalide la nouvelle
   */
  function unvalidate()
  {
    if ( !$this->dbrw ) return;
    new update($this->dbrw,"nvl_nouvelles",array("modere_nvl"=>0),array("id_nouvelle"=>$this->id));
    $this->modere_nvl = 0;
  }


  /** @brief Ajoute une nouvelle
   *
   * @param id_utilisateur l'identifiant de l'utilisateur
   * @param id_asso (facultatif) l'identifiant de l'association
   * @param titre titre de la nouvelle
   * @param resume un resume de la nouvelle
   * @param contenu le contenu (format wiki2xhtml)
   *
   * @return true ou false en fonction du resultat
   */
  function add_news($id_utilisateur,
		    $id_asso = null,
		    $titre,
		    $resume,
		    $contenu,
		    $type=NEWS_TYPE_EVENT,
		    $asso_seule=false,
		    $id_lieu=NULL)
  {
    if (!$this->dbrw)
      return false;
      
    $this->asso_seule = $asso_seule;
    $this->id_lieu = $id_lieu;

    $req = new insert ($this->dbrw,
		       "nvl_nouvelles",
		       array ("id_utilisateur" => $id_utilisateur,
			      "id_asso" => $id_asso,
			      "titre_nvl" => $titre,
			      "resume_nvl" => $resume,
			      "contenu_nvl" => $contenu,
			      "date_nvl" => date("Y-m-d H:i:s"),
			      "modere_nvl" =>  false,
			      "id_utilisateur_moderateur"=>null,
			      "type_nvl"=>$type,
			      "asso_seule_nvl" => $this->asso_seule,
			      "id_lieu"=>$this->id_lieu));
			      
		if ( $req )
			$this->id = $req->get_id();
		else
			$this->id = null;
			      
    return ($req != false);
  }
  
  
	function add_date($debut,$fin)
	{
		$req = new insert ($this->dbrw,
				"nvl_dates",
				array ("id_nouvelle" => $this->id,
						"date_debut_eve" => date("Y-m-d H:i:s",$debut),
						"date_fin_eve" => date("Y-m-d H:i:s",$fin)
					));
	}
	
	function delete_date ( $id_date )
	{
		$req = new delete ($this->dbrw,
				"nvl_dates",
				array ("id_nouvelle" => $this->id,
						"id_dates_nvl" => $id_date,
					));
		
		
	}
	
	function save_news(
		    $id_asso = null,
		    $titre,
		    $resume,
		    $contenu,
		    $modere,
		    $id_utilisateur_moderateur,
		    $type=NEWS_TYPE_EVENT,
		    $asso_seule=0,
		    $id_lieu=NULL)
	{
		if (!$this->dbrw)
			return false;

		$this->titre = $titre;
		$this->resume = $resume;
		$this->contenu = $contenu;
		$this->modere = $modere;
		$this->id_asso = $id_asso;
		$this->type = $type;
		$this->id_utilisateur_moderateur = $id_utilisateur_moderateur;
    $this->asso_seule = $asso_seule;
    $this->id_lieu = $id_lieu;

		$req = new update ($this->dbrw,
		       "nvl_nouvelles",
		       array (
			      "id_asso" => $id_asso,
			      "titre_nvl" => $titre,
			      "resume_nvl" => $resume,
			      "contenu_nvl" => $contenu,
			      "modere_nvl" =>  $modere,
			      "id_utilisateur_moderateur"=>$id_utilisateur_moderateur,
			      "type_nvl"=>$type,
			      "asso_seule_nvl" => $this->asso_seule,
			      "id_lieu"=>$this->id_lieu),
			   array(
			   	"id_nouvelle"=>$this->id
			   	));

	}
}


?>
