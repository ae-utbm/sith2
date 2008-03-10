<?
$topdir = "../";
require_once($topdir . "include/globals.inc.php");
require_once ("../include/mysql.inc.php");
require_once ("../include/mysqlae.inc.php");

$subject = '[FF1J] Pilote de groupe';
$headers = 'From: Thomas Chatenet <thomas.chatenet@utbm.fr>' . "\r\n" .
    'Reply-To: Thomas Chatenet <thomas.chatenet@utbm.fr>' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

$message = "Bonjour à tous,

Vous recevez ce mail car vous faites partis des privilégiés qui peuvent
participer au Festival du Film d'1 Jour.
En effet, comme vous avez plus de 21 ans il vous est possible de participer
activement au FF1j en devenant pilote.

   - Le FF1J qu'est ce que c'est ?
C'est un festival cinématographique qui accueille, du 1er au 4 mai, une
quinzaine d'équipes des plus grandes écoles de France pour relever un défi :
réaliser un film en 50 heures.
2008 est la troisième édition de ce festival. Largement soutenu par l'UTBM
et l'AE il a rapidement su se faire une place de choix comme activités
culturelles majeures de l'aire urbaine.
   -  Pilote qu'est ce que c'est  ?
Les pilotes du FF1J sont les pièces maîtresses du festival. Ils ont pour
charge d'assurer les déplacements des équipes qui participent au festival
pour que celles-ci puissent entièrement se consacrer au film qu'elles ont à
produire. C'est une tache capitale ; les pilotes font le lien entre
l'équipe et l'organisation.
Mais derrière ces responsabilités se cache une merveilleuse expérience
humaine : les pilotes sont aux premières loges de la réalisation d'un court
métrage ; de la scènarisation au montage en passant bien sûr par le tournage
et même la diffusion ils assistent à l'évolution complète de l'œuvre.
Ces postes de pilotes sont des places de privilégiés auxquelles vous avez
accès.
N'attendez plus, les places sont limitées.

Pour tous autres renseignements ou quelconque question, n'hésitez pas à me
contacter.

Amicalement votre.

Thomas Chatenet
Responsable Pilotes FF1J
thomas.chatenet@utbm.fr";

$req = new requete (new mysqlae(),
        "SELECT utilisateurs.id_utilisateur, 
            CONCAT(prenom_utl,' ',nom_utl) as nom,
            email_utl 
         FROM utilisateurs
         INNER JOIN utl_extra on utilisateurs.id_utilisateur = utl_extra.id_utilisateur
         WHERE (datediff('2008-05-01',date_naissance_utl))/365 >= '21'
            AND ae_utl='1' 
            AND utbm_utl='1'
            AND etudiant_utl='1'
            AND permis_conduire_utl='1'
        ORDER BY id_utilisateur");

while($res = $req->get_row())
{
  echo $res['nom'] . " &lt;" . $res['email_utl'] . "&gt; : Sent <br />";
  mail($res['nom']." <".$res['email_utl'].">", $subject, $message, $headers);
}

?>
