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
require_once($topdir."include/entities/planning.inc.php");
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

/*
 ************** Partie B : manipuler des données avec un stdentity *************
 */

/*
 * Tous les stdentities fournissent un certain nombre de fonctionalités que nous 
 * ne verrons pas dans ce tutorirel, ces fonctions sont liées à stdentity.
 * Pour voir ces fonctions avancées, voir la focumentation de la classe
 * stdentity.
 */

/*
 * Pour commencer on créer une instance de la stdentity en lecture et écriture :
 * on passe donc au constructeur les deux liens à la base de donnée.
 * Le constructeur est le même pour *toutes* les stdentities.
 */
$planning = new planning($site->db,$site->dbrw);


/* 
 * Créons un planning dans le quel nous allons ajouter des créneaux
 */
 
/*
 * Les stdentites manipulent les dates et les heures sous forme de timestamp unix
 * Préparons les dates de début et de fin de validité pour notre planning de test
 */ 
 
$start_date = strtotime("2008-04-01");
$end_date = strtotime("2008-08-01");

/*
 * Ajoute le planning dans la base et l'affecte dans l'instance
 */ 
$planning->add ( 
  1, /* id_asso : ici 1 pour AE */
  "Planning d'essai", /* un nom pour le planning */
  -1, /* nombre de personne par créneau : ici -1 pour infini */
  $start_date /* date de début de validité du planning */, 
  $end_date /* date de fin de validité du planning */, 
  true /* il s'agit d'un planning hebdomadaire */ );

/*
 * Calcule quelques dates en secondes depuis le début de la semaine :
 * ici on fait commencer la semaine à Lundi 00:00:00
 *
 * Si on travaillait dans un planning "non hebdomadaire", 0 correspondrait à
 * $start_date
 */
$lundi = 0;
$mardi = 3600*24;
$jeudi = 3600*24*3;
$h8 = 8*3600;
$h12 = 12*3600;
$h14 = 14*3600;
$h18 = 18*3600; 

/*
 * Crée des créneaux et récupère leur identifiant
 */
$id_creneau_1 = $planning->add_gap( $lundi+$h8, $lundi+$h12h );
$id_creneau_2 = $planning->add_gap( $lundi+$h14, $lundi+$h18h );
$id_creneau_3 = $planning->add_gap( $mardi+$h8, $mardi+$h12h );
$id_creneau_4 = $planning->add_gap( $mardi+$h14, $mardi+$h18h );
$id_creneau_5 = $planning->add_gap( $jeudi+$h8, $jeudi+$h12h );
$id_creneau_6 = $planning->add_gap( $jeudi+$h14, $jeudi+$h18h );
$id_creneau_7 = $planning->add_gap( $jeudi+$h12, $jeudi+$h14h );

/*
 * Affecte des utilisateurs aux creneaux
 */
$planning->add_user_to_gap($id_creneau_1,142);
$planning->add_user_to_gap($id_creneau_2,142);
$planning->add_user_to_gap($id_creneau_3,142);
$planning->add_user_to_gap($id_creneau_4,166);
$planning->add_user_to_gap($id_creneau_5,166);
$planning->add_user_to_gap($id_creneau_6,166);
$planning->add_user_to_gap($id_creneau_1,3033);
$planning->add_user_to_gap($id_creneau_2,3253);
$planning->add_user_to_gap($id_creneau_4,3033);
$planning->add_user_to_gap($id_creneau_5,3253);
$planning->add_user_to_gap($id_creneau_5,1827); 
$planning->add_user_to_gap($id_creneau_5,3033);

/*
 * On ne crée pas l'objet à chaque chargement de page dans la vrai vie,
 * on souhaite le conserver et le ré-utiliser. Dans ces cas il faut le 
 * charger : c'est le rôle de load_by_id.
 *
 * En cas d'echec, $objet->is_valid() renvoie faux, et là on affiche une erreur
 * grâce à une fonction de site. En précisant toujours la section.
 */
 
/*
 * affecte l'id du planning dans $_REQUEST pour la simulation
 */
$_REQUEST["id_planning"] = $planning->id;

/*
 * charge (tente de charger) l'objet dans l'insyance
 */
$planning->load_by_id($_REQUEST["id_planning"]);

/*
 * teste si tout s'est bien passé
 */
if ( !$planning->is_valid() )
  $site->error_not_found("services");

/*
 ************** Partie C : afficher des données avec un stdcontents *************
 */

/*
 * Commence les choses sérieuses : on définit la section de la page et son titre
 * grâce à site. C'est la première étape pour construire la page.
 */
$site->start_page("section","Mon planning de test");


/*
 * Crée un stdcontents, du type contents, dans le quel on va mettre tous les autres
 * On lui met comme titre le nom du planning 
 */
$cts = new contents($planning->name);

/*
 * On prépare une requête SQL pour selectionner les données que l'on veu afficher.
 * Il existe de nombreux contents, certains sont capables de récupérer
 * automatiquement les données, mais les génériques on besoin d'être alimenté soit
 * par une requête SQL (éxécuté ou non), soit des stdentites, soit des arrays
 */

$unlundi = strtotime("2008-03-10 00:00:00");

$sql = 
    "SELECT 
     id_gap,
     FROM_UNIXTIME($unlundi+start_gap) AS debut, 
     FROM_UNIXTIME($unlundi+end_gap) AS fin,
     IFNULL(utlisateurs.alias,'(personne)') AS texte
     FROM pl_gap
     LEFT JOIN pl_gap_user USING(id_gap)
     LEFT JOIN utilisateurs USING(id_utilisateur)
     WHERE pl_gap.id_planning='".mysql_real_escape_string($planning->id)."'";   
     
/*
 * Construit note stdcontents weekplanning pour afficher notre planning
 */
$pl = new weekplanning ( 
"Planning", /* Titre du stdcontents */
$site->db, /* Lien à la base de données */
$sql,  /* La requête SQL */
"id_gap", /* Champ SQL de l'identifiant de chaque créneau */
"debut",  /* Champ SQL de la date de début */
"fin", /* Champ SQL de la date de fin */
"texte", /* Champ sql contentenant le texte à afficher */
"demoplanning.php", /* URL de cette page (ou naviguer entre les dates) */
"demoplanning.php?action=details", /*
"", /* Fin de la requête SQL (ici vide) (ce qui vient après les conditions) */
$unlundi /* Notre date de référence pour basculer en mode hebdomadaire */
 );
    
/*
 * Ajoute le weekplanning dans le contents en activant l'affichage du titre (en H2).
 */
$cts->add($pl,true);

/*
 * Ajoute le contents dans le site pour l'affichage, s'il y a un titre il est 
 * toujours affiché (en H1). Nous avons définit un titre, il sera donc affiché.
 */
$site->add_contents($cts);

/*
 * Termine la construction de la page : génére tout le contenu HTML et l'envoie
 * vers le client
 */
$site->end_page();

/*
 ************** Partie D : supprime les données que l'on a ajoutées *************
 */

/*
 * Enlève un utilisateur d'un creneau
 */
$planning->remove_user_from_gap($id_creneau_1,142);

/*
 * Supprime un creneau et toutes les données liées
 */
$planning->remove_gap($id_creneau_5);

/*
 * Supprime finalement le planning  et toutes les données liées que l'on a 
 * ajouté dans la partie B.
 */
$planning->remove();


/*
 * Voilà c'est tout pour cette fois ci !
 * Pour voir le résultat : http://ae.utbm.fr/taiste/temp/demoplanning.php
 */
 
?>