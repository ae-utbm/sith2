<?php

/**
 * @file
 * Tutoriel n°1 : stdentities et stdcontents
 *
 * Exemple des plannings
 *
 * @author Julien Etelain
 */

/*
 ************** Partie A : Les bases d'une page **************
 */

/*
 * première ligne effective de toute page du site : le chemin vers la racine
 */
$topdir = "../";

// Rapporter les E_NOTICE peut vous aider ? am?liorer vos scripts
// (variables non initialis?es, variables mal orthographi?es..)
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
ini_set('display_errors', '1');

/*
 * "inclusion" de tous les fichiers requis,
 *
 * il y a forcément site.inc.php qui fourni la classe site ainsi que tous les
 * éléments communs
 */
require_once($topdir. "include/site.inc.php");
/*
 * Inclusion des stdentities dont on va avoir besoin : entities/planning.inc.php
 * Les stdentities permettent d'intérragir avec la base de données, ils fournissent
 * en général en plus les fonctions de traitement. (les fonctions "métiers")
 * En faisant le parallèle avec le modèle MVC, ici c'est M+C : Model, Control
 */
require_once($topdir."include/entities/planning2.inc.php");
/*
 * Inclusion des stdcontents : cts/weekplanning.inc.php
 * Les stdcontents permettent l'affichage, ils correspondant aux "widget" des
 * applications lourdes (un bouton, un datagrid...)
 * en reprennant le parallèle avec le modèle MVC, ici c'est le V : View
 */
require_once($topdir."include/cts/planning.inc.php");

/*
 * Une fois que l'on a inclus tout ce dont on a besoin, on crée une instance de
 * site. On récupère ainsi
 * - les liens à la base de données : $site->db et $site->dbrw
 * - l'utilisateur connecté $site->user
 * - la racine dans la quelle on va insérer les stdcontents pour qu'ils soient
 * affiché coté client (transformé en html et renvoyé au client)
 * - quelques fonction utilitaires (on verra un exemple)
 */
$site = new site();

/*
 * Cette page n'est accessible qu'aux utilisateurs connecté, on appelle
 * donc la fonction consacrée. Si l'utilisateur n'est pas connecté,
 * l'execution de la page s'arrête là. (un joli message sera affiché à l'utilisateur)
 * On précise la section, pour que l'onglet correspodant soit selectionné lors
 * de l'erreur
 */
$site->allow_only_logged_users("services");

/*
 * Cette page est par ailleurs uniquement accessible qu'aux membres du groupe
 * "root". On vérifie cela et dan sle cas contraire on utilise une fonction de
 * $site pour afficher un message d'erreur et arrêter le page ici.
 * On précise la section et la raison de l'erreur.
 * Voir la documentation de site::error_forbidden pour plus de détails.
 */
if ( !$site->user->is_in_group("root") )
  $site->error_forbidden("services","group");

$cts = new contents("Test");

$planning = new planning2($site->db, $site->dbrw);

$id_planning = $planning->add("Test",0,0,true,'2013-06-11 00:00:00','2013-06-12 00:00:00');
if(!$id_planning)
	echo "Erreur creation planning";
echo mysql_error ();

$planning->remove();


?>
