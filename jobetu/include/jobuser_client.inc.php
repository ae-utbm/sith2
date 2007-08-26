<?
/* Copyright 2007
 * - Manuel Vonthron < manuel DOT vonthron AT acadis DOT org >
 *
 * Ce fichier fait partie du site de l'Association des étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

require_once($topdir. "include/cts/user.inc.php");

class jobuser_client extends utilisateur
{
	var $annonces = array();
	
  function new_particulier( $nom, $prenom, $alias, $email, $password, $droit_image, $date_naissance, $sexe)
  {
  	$this->create_user( $nom, $prenom, $alias, $email, $password, $droit_image, $date_naissance, $sexe);
  	
  }
  
  function new_societe( $nom, $prenom, $nom_ste, $email, $password, $droit_image, $date_naissance, $sexe) //Ne prévoit pas la France de demain :(
  {
  	//En attente du flag sur la table utilisateur
  	$this->create_user( $nom, $prenom, "Société ". $nom_ste, $email, $password, $droit_image, $date_naissance, $sexe);
  }
    
  function connexion($email, $passwd)
  {
  	/*$site->user->load_by_email($email);
		if ( !$site->user->is_password($passwd) )
		{
			header("Location: http://ae.utbm.fr/article.php?name=site-wrongpassoruser");
			exit();
		}*/
  }

  function is_jobetu_client()
  {
    return $this->is_in_group("jobetu_client");
  }

  
  function load_annonces()
  {
   // if( is_jobetu_client() )
      {
      	$sql = new requete($this->db, "SELECT * FROM job_annonces WHERE id_client = $this->id");
				
        while($line = $sql->get_row())
			    $this->annonces[] = $line;
	    }
  }
}   	
?>