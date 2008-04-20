<?
$topdir = "../";
require_once($topdir . "include/globals.inc.php");
require_once ("../include/mysql.inc.php");
require_once ("../include/mysqlae.inc.php");

$subject = 'Soirée filles';
$headers = 'From: Laure Guicherd <laure.guicherd@gmail.com>' . "\r\n" .
    'Reply-To: Laure Guicherd <laure.guicherd@gmail.com>' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

$message = "Salut les filles !

Marre de ne cotoyer que des garçons ? Besoin d'un peu de douceur (ou pas) dans ce monde presque exclusivement masculin ?
Nous avons la solution : Viens cotoyer 2 fois plus de chromosomes X à la soirée filles, exclusivement réservée au sexe statistiquement faible de l'école ( <- nous ) et interdite aux 88% poilus restants !

Le planning très chargé du semestre de nous ayant pas vraiment laissé le choix, la soirée aura lieu au foyer le mercredi 11 juin.

Si tu veux en être, nous avons besoin de ton avis pour le choix du thème, parmi les trois suivants sortis tout droit de nos cerveaux mignons mais malades :

- Animales : pour celles qui ont toujours rêvé de se déguiser en lapine, en chatte ou même en moule... et rugir de plaisir (ok, pas pour la moule, mais c'est vous qui voyez)

- Militaire : pour les amoureuses de l'uniforme et de la discipline...

- Barbie : pour celles qui ne se sont jamais remises de ce fol amour avec Ken...

Viens donc voter pour ton thème préféré sur le site de l'AE !


Nous organiserons une réunion une fois que le thème sera fixé, afin de préparer tout ça plus en détail. Si tu veux aider à l'organisation, fais-nous signe... Plus nous serons nombreuses, plus la soirée sera réussie.

 A bientôt

 Chattement vôtre,

 Dyna, éCoume et Locoms
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
