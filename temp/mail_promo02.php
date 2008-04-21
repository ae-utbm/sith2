<?
$topdir = "../";
require_once($topdir . "include/globals.inc.php");
require_once ("../include/mysql.inc.php");
require_once ("../include/mysqlae.inc.php");

$subject = 'Évennement Assidu "Retrouvailles" pour le FIMU';
$headers = 'From: Gauthier Douchet <gauthier.douchet@gmail.com>' . "\r\n" .
    'Reply-To: Gauthier Douchet <gauthier.douchet@gmail.com>' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

$message = "Bonjour à tous,

Cher membres de la promo02, je vous transmets une info d'Assidu,
l'association des anciens de l'UTBM, l'ENIBe, l'IPSé et l'UTCS.

A l'occasion du FIMU, celle-ci organise un évènement spécial pour tous les
anciens le samedi 11 mai: le Week-end Retrouvailles.

Toutes les informations sur :
http://www.assidu-utbm.fr/index.php?page=article&id=132

Une bonne occasion si vous voulez retrouver entre anciens de la 02 (et
d'autres promos d'ailleurs).
En plus de cette occasion, Assidu en profite pour vous convier sur son site
web pour notamment y mettre à jour votre fiche et ainsi profiter entre autre
de l'e-mail à vie.

N'hésitez pas à les contacter si vous avez des questions sur l'évènement ou
sur l'association !

Teury pour le transfert d'infos";

$req = new requete (new mysqlae(),
        "SELECT utilisateurs.id_utilisateur, 
            CONCAT(prenom_utl,' ',nom_utl) as nom,
            email_utl
         FROM utilisateurs
         INNER JOIN utl_etu_utbm USING(id_utilisateur)
         WHERE promo_utbm='2'
        ORDER BY id_utilisateur");

while($res = $req->get_row())
{
  echo $res['nom'] . " &lt;" . $res['email_utl'] . "&gt; : Sent <br />";
/*  mail($res['nom']." <".$res['email_utl'].">", $subject, $message, $headers);*/
}

?>
