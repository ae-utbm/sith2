<?php

$topdir = "../";
require_once ($topdir . "include/mysql.inc.php");
require_once ($topdir . "include/mysqlae.inc.php");

$req = new requete(new mysqlae (),
   "SELECT id_asso FROM `asso` WHERE id_asso_parent=1");// en gros tous les poles
$buffer='';
while(list($id)=$req->get_row())
{
  $req2=new requete(new mysqlae (),
  "SELECT id_asso, email_asso FROM `asso` WHERE id_asso_parent=".$id);//tous les clubs du pole :P
  while(list($_id,$email)=$req2->get_row())
  {
    if(!empty($email))
    {
      $req3=new requete(new mysqlae (),
      "SELECT email_utl, email_utbm ".
      "FROM asso_membre ".
      "INNER JOIN utilisateurs USING(id_utilisateur) ".
      "LEFT JOIN utl_etu_utbm USING(id_utilisateur) ".
      "WHERE id_asso=".$_id." ".
      "AND (role=10 OR role=9 OR role=7) ".
      "AND date_fin IS NULL");
      if($req3->lines==0)
        $buffer.='ADD '.$email.' ae@utbm.fr'."\n";
        // on inscrit ae@utbm.fr
      else
      {
        while(list($emailult,$emailutbm)=$req3->get_row())
        {
          if(is_null($emailutbm))
            $to=$emailult;
          else
            $to=$emailutbm;
          $buffer.='ADD '.$email.' '.$to."\n";
        }
      }
    }
  }
}
echo "<pre>";
echo $buffer;
echo "</pre>";

?>
