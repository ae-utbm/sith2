<?php


class Partenariat extends stdentity
{
  /* ID du partenariat */
  var $id_partenariat;
  /* ID de l'utilisateur */
  var $id_utilisateur;
  /* Date d'ajout */
  var $date;

  function load_by_id($id_partenariat, $id_utilisateur)
  {
    $req = new requete($this->db, "SELECT id_utilisateur ".
            "FROM `partenariats_utl` ".
            "WHERE id_utilisateur = '".$id_utilisateur."' ".
            "AND id_partenariat = '".$id_partenariat."'");
    if($req->lines == 1)
    {
      $row = $req->fetch_row();
      $this->id_partenariat = $row['id_partenariat'];
      $this->id_utilisateur = $row['id_utilisateur'];
      $this->date = $row['date_partenariat'];
    }
  }

  function is_valid()
  {
    return !is_null($this->id_partenariat) && ($this->id_partenariat != -1)
        && !is_null($this->id_utilisateur) && ($this->id_utilisateur != -1);
  }

  function add($id_partenariat, $id_utilisateur)
  {
    $req = new insert($site->dbrw, "partenariats_utl",
              array('id_partenariat'=>$id_partenariat,
                'id_utilisateur'=>$id_utilisateur,
                'date_partenariat'=>date('Y-m-d'),
              ));
    $this->id_partenariat = $id_partenariat;
    $this->id_utilisateur = $id_utilisateur;
    $this->id = $req->get_id();
  }

  function remove($id_partenariat, $id_utilisateur)
  {
    $req = new delete($site->dbrw, "partenariats_utl",
      array('id_partenariat'=>$id_partenariat, 'id_utilisateur'=>$id_utilisateur)
      );
    $this->id_partenariat = null;
    $this->id_utilisateur = null;
  }
}


?>
