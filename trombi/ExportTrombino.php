<?php


$topdir = "../";

include($topdir. "include/site.inc.php");
require_once($topdir. "include/cts/sqltable.inc.php");
require_once($topdir. "include/entities/asso.inc.php");
require_once($topdir. "include/cts/user.inc.php");
require_once($topdir. "sas2/include/photo.inc.php");
require_once($topdir. "include/cts/gallery.inc.php");
require_once($topdir. "include/cts/special.inc.php");
require_once($topdir. "include/globals.inc.php");
require_once($topdir. "include/entities/ville.inc.php");
require_once($topdir. "include/entities/pays.inc.php");
require_once($topdir. "include/graph.inc.php");
require_once($topdir. "include/cts/imgcarto.inc.php");
require_once($topdir. "include/pgsqlae.inc.php");
require_once("include/entities/commentaire.inc.php");
require_once("include/cts/commentaire.inc.php");

$site = new site();
if ( $site->user->is_in_group("root") )
{
    $result="<xml>";
    $req = new requete($site->db,"SELECT utilisateurs.id_utilisateur,utilisateurs.nom_utl, utilisateurs.prenom_utl, utl_etu_utbm.surnom_utbm, utilisateurs.email_utl, utilisateurs.tel_portable_utl FROM `utilisateurs` JOIN utl_etu_utbm ON utilisateurs.id_utilisateur = utl_etu_utbm.id_utilisateur  WHERE publique_mmtpapier_utl =1 AND utl_etu_utbm.promo_utbm =10 ");
    if ($req->lines >0){
        while($row = $req->get_row()){
            $result.="<utilisateur>";
            $result.="<nom>".$row["nom_utl"]."</nom>";
            $result.="<prenom>".$row["prenom_utl"]."</prenom>";
            $result.="<surnom>".$row["surnom_utbm"]."</surnom>";
            $result.="<email>".$row["email_utl"]."</email>";
            $result.="<tel>".$row["tel_portable_utl"]."</tel>";
            $req_fillots= new requete($site->db,"SELECT utl_etu_utbm.surnom_utbm from parrains join utl_etu_utbm on parrains.id_utilisateur_fillot=utl_etu_utbm.id_utilisateur where id_utilisateur = ".$row["utilisateurs.id_utilisateur"]." ");
            while($row_fillots = $req_fillots->get_row()){
                 $result.="<fillot>".$row_fillots["surnom_utbm"]."</fillot>";
            }
            $req_parrains= new requete($site->db,"SELECT utl_etu_utbm.surnom_utbm from parrains join utl_etu_utbm on parrains.id_utilisateur=utl_etu_utbm.id_utilisateur where id_utilisateur_fillot = ".$row["utilisateurs.id_utilisateur"]."");
            while($row_parrains = $req_parrains->get_row()){
                $result.="<parrain>".$row_parrains["surnom_utbm"]."</parrain>";
            }
            $req_assoc = new requete($site->db,
                "SELECT `asso`.`nom_asso`,`asso_membre`.`role`, " .
                "IF(`asso`.`id_asso_parent` IS NULL,`asso_membre`.`role`+100,`asso_membre`.`role`) AS `role`, ".
                "`asso_membre`.`date_debut`, `asso_membre`.`desc_role`, " .
                "CONCAT(`asso`.`id_asso`,',',`asso_membre`.`date_debut`) as `id_membership` " .
                "FROM `asso_membre` " .
                "INNER JOIN `asso` ON `asso`.`id_asso`=`asso_membre`.`id_asso` " .
                "WHERE asso_membre.id_utilisateur='".$req["utilisateurs.id_utilisateur"]."' " .
                "AND `asso_membre`.`date_fin` is NULL " .
                "ORDER BY `asso`.`nom_asso`");
            while($row_assoc= $req_assoc->get_row()){
                $result.="<asso><nom>".$row_assoc["nom_asso"]."</nom>";
                $result.="<role>".$row_assoc['role']."</role></asso>";
            }
            $req_comment = new requete($site->db,
            "select commentaire, utl_etu_utbm.surnom_utbm where id_commente = ".$req["utilisateurs.id_utilisateur"]." from trombi_commentaire_ join utl_etu_utbm on trombino_commentaires.id_commentateur=utl_etu_utbm.id_utilisateur"
            );
            while($row_comment= $req_comment->get_row()){
                $result.="<commentaire><nom>".$row_comment["surnom_utbm"]."</nom>";
                $result.="<contenu>".$row_comment['commentaire']."</contenu></commentaire>";
            }
            $result.="</utilisateur>";

        }
        echo $result;
    }




}