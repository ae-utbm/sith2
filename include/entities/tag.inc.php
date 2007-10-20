<?

class tag extends stdentity
{
  var $nom;
  var $modere;
  
  function load_by_id ( $id )
  {
    $req = new requete($this->db, "SELECT * FROM `tag`
                                   WHERE `id_tag` = '" . mysql_real_escape_string($id) . "'
                                   LIMIT 1");

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
    $this->id = $row["id_tag"];  
    $this->nom = $row["nom_tag"];  
    $this->modere = $row["modere_tag"];  
  }
  
}



?>