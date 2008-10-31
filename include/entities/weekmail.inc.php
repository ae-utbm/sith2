<?php
/*
 * Copyright 2008
 * - Simon Lopez < simon dot lopez at ayolo dot org >
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

 /**
 * @file
 */

class weekmail extends stdentity
{

  var $id;
  var $date;
  var $titre;
  var $content;
	var $statut;
	var $imgheder="http://ae.utbm.fr/images/headerweekmail.png";

  function weekmail ($db, $dbrw = null)
  {
    $this->stdentity ($db, $dbrw);
  }

  function load_by_id ($id)
  {
		$req = new requete
		(
			$this->db,
			'SELECT * FROM weekmail WHERE id='.intval($id).' AND statut=1 LIMIT 1'
		);

    if ( $req->lines == 1 )
    {
	    $this->_load($req->get_row());
	    return true;
    }

		$this->id = null;
    return false;
  }

  function _load ( $row )
  {
    $this->id = $row['id'];
    $this->date = $row['date'];
    $this->title = $row['title'];
    $this->content = $row['content'];
    $this->statut = $row['statut'];
  }

}

?>
