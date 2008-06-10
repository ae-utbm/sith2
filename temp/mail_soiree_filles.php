<?
$topdir = "../";
require_once($topdir . "include/globals.inc.php");
require_once ("../include/mysql.inc.php");
require_once ("../include/mysqlae.inc.php");

$subject = 'Soirée filles';
$headers = 'From: Laure Guicherd <laure.guicherd@gmail.com>' . "\r\n" .
    'Reply-To: Laure Guicherd <laure.guicherd@gmail.com>' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

$message = "Rebonjour les filles !

J'espère que vous n'avez pas oublié que la soirée filles est demain :p
Pour celles qui n'ont pas encore cotisé, je vous rappelle que la cotiz est toujours dispo sur l'e-boutic, et que demain à 10h devant l'A200 se tiendra une perm :)
Au cas ou il y a vraiment un problème, contactez-moi (numéro sur le matmatronch)

D'autre part, je vous contacte aussi à propos des perms de bar : si l'une d'entre vous est motivée, manifestez-vous rapidement histoire qu'on essaie de se mettre d'accord sur des horaires approximatifs.

A demain !

Locoms
";

$req = new requete (new mysqlae(),
        "SELECT utilisateurs.id_utilisateur, 
            CONCAT(prenom_utl,' ',nom_utl) as nom,
            email_utl 
         FROM utilisateurs
         WHERE utilisateurs.sexe_utl='2'
            AND ae_utl='1'
            AND etudiant_utl='1'
            AND utbm_utl='1'
        ORDER BY id_utilisateur");

while($res = $req->get_row())
{
  echo $res['nom'] . " &lt;" . $res['email_utl'] . "&gt; : Sent <br />";
  //mail($res['nom']." <".$res['email_utl'].">", $subject, $message, $headers);
}

?>
