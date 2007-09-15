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

require_once($topdir."include/rss.inc.php");

class jobetu extends stdentity
{
  var $job_types;
  var $job_main_cat;

  
  /** Récupère les différents types de job
   * renvoie un tableau: array( array("id_type", "cat", "nom") ) dans $this->jobtypes
   */
  function get_job_types()
  {
    $sql = new requete($this->db, "SELECT * FROM job_types ORDER BY id_type ASC");
    
    while($type = $sql->get_row())
    {
			$this->job_types[ $type['id_type'] ] = $type['nom'];
			if(!($type['id_type']%100))
				$this->job_main_cat[ $type['id_type'] ] = $type['nom'];
    }
  }

  /** Ajoute une nouvelle catégorie de job
   * @param $name nom de la catégorie
   * @return num de la nouvelle catégorie, -1 si échec
   * @todo problème de placement !!
   */
  function add_cat_type($nom)
  {
    /* récupération du maximum existant */
    $sql = new requete($this->db, "SELECT MAX(id_type) FROM job_types");
    $cat = $sql->get_row();
    $max = floor($cat[0] / 100);
    
    $sql = new insert($this->dbrw, 
		      "job_types", 
		      array(
			    "id_type" => ($max + 1)*100,
			    "nom" => mysql_real_escape_string($nom)
			    )
		      );
    
    if($sql)
      return ($max + 1)*100;
    else
      return -1;
  }

  /** Ajoute un nouveau type de job (dans une catégorie existante)
   * @param $name nom du type
   * @param $id_cat id de la catégorie associée
   * @return num du nouveau type, -1 si échec
   */

  function add_subtype($nom, $id_cat)
  {
    $sql = new requete($this->db, "SELECT MAX(id_type) FROM job_types WHERE id_type > ".($id_cat - 1)." AND id_type < ".($id_cat + 100)."");
    $max = $sql->get_row();

    $sql = new insert($this->dbrw,
		      "job_types",
		      array(
						    "id_type" => $max[0] + 1,
						    "nom" => mysql_real_escape_string($nom)
			    			));

    if($sql)
      return $id_cat + $max[0] + 1;
    else
      return -1;   
  }

}

class rssjobetu extends rssfeed
{
	var $db;
	
	function rssjobetu(&$db)
	{
		$this->db = $db;
    $this->title = "AE JobEtu";
    $this->description = "Les dernières annonces de JobEtu";
    $this->link = "http://ae.utbm.fr/jobetu/";
    
		$this->rssfeed();
	}
	
	function output_items()
	{
		$sql = new requete($this->db, "SELECT `id_annonce`, `titre`, `desc`, `date` FROM `job_annonces` WHERE `closed` != '1' AND `id_select_etu` IS NULL ORDER BY `date` DESC LIMIT 15");
		
		while( $row = $sql->get_row() )
		{
			echo "<item>\n";
  		echo "<title>".htmlspecialchars($row["titre"],ENT_NOQUOTES,"UTF-8")."</title>\n";
  		echo "<link>http://ae.utbm.fr/jobetu/board_etu.php?view=general&action=detail&id_annonce=".$row["id_annonce"]."</link>\n";
  		echo "<description>".htmlspecialchars($row["desc"],ENT_NOQUOTES,"UTF-8")."</description>\n";
  		echo "<pubDate>".gmdate("D, j M Y G:i:s T",strtotime($row["date"]))."</pubDate>\n";
  		echo "</item>\n";	
		}
		
	}
}
