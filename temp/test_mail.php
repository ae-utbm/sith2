<?
//exit();
$topdir = '../';
require_once($topdir. "include/site.inc.php");
require_once($topdir."include/entities/files.inc.php");
require_once($topdir."include/entities/folder.inc.php");
require_once($topdir.'include/lib/mailer.inc.php');
$site = new site();

$mailer = new mailer('Association des Étudiants <ae@utbm.fr>',
                     'Weekmail du 28 septembre au 04 octobre 2009');
//$mailer->add_dest('simon.lopez@utbm.fr');
///*
$mailer->add_dest(array('etudiants@utbm.fr',
                        'enseignants@utbm.fr',
                        'iatoss@utbm.fr',
                        'aude.petit@utbm.fr',
                        'info@ml.aeinfo.net'));
//*/
$content=<<<EOF
Salut les UTbohémiens,

Nouvelle semaine, nouveau weekmail ! Il est grand temps d'attaquer le vif du
sujet. Les clubs ont presque tous repris du service, et le congrès se
profile à l'horizon. Voici les news de la semaine.



Sommaire :

 * Assemblées Générales de l’AE, du BDF, du BDS et du CETU
 * Troll Penché - Killer
 * Matmatronch' - L'édition papier du Mat'matronch revient!
 * Prom'Art - Concert unique : 8bit electro à La Poudrière
 * Semaine De Mars 2010 – Première réunion
 * Le Congrès Industriel arrive à grand pas !
 * Le 4L Trophy fait sa rentrée!
 * Club Kart - Réunion de rentrée
 * le club magie UT est de retour
 * UNITEC - Réunion de rentrée du club de Robotique Mercredi a 20H en A200
 * UTtoons - Séances de la semaine
 * Prix Universitaire du Logiciel Libre - Réunion de Lancement
 * Relance du Petit Géni !
 * Le club-zik paye son boeuf
 * Club Cuisine - C'est reparti


----------------------------------------------------
Assemblées Générales de l’AE, du BDF, du BDS et du CETU
----------------------------------------------------

Le mardi 29 septembre à 13 heures à la MDE - Kfet (en-dessous du Restaurant
Universitaire) de Sevenans et à 20 heures au Foyer de la Maison des Élèves
de Belfort, les quatre associations étudiantes de l’UTBM t’invitent à venir
à leurs Assemblées Générales de début de semestre. Il s’agit d’une
présentation rapide des associations, de leurs budgets et de leurs projets
pour le semestre. Donc si toi aussi tu veux apprendre à connaitre tes
associations et leurs fonctionnements, viens le mardi 29 septembre à 13
heures à la MDE - Kfet de Sevenans ou à 20 heures au  Foyer à Belfort. De
plus, après la session à Belfort au Foyer, tu pourras te restaurer pour une
petite somme avec un barbecue organisé par les quatre associations.

A mardi !
Les équipes AE, BDF, BDS et CETU

----------------------------------------------------
Troll Penché - Killer
----------------------------------------------------

Wanted : dead or... dead

Hey Gringo, tu en as marre de chasser le coyote ? Tu rêves d’une vie
de Cowboy, galopant dans la grande plaine à la poursuite de dangereux
bandits. Mais fais bien attention à ton scalp, les peaux rouges ne
sont pas tendres avec les voyageurs solitaires.

Le Killer Western est un jeu d’équipe où les joueurs vont s’affronter
dans toute l’UTBM pendant dix jours. Cet événement se déroulera bien
entendu sur les 3 sites de l’UTBM, pendant 10 jours, et est ouvert à
tous les UTBohéMiens. L’accomplissement de missions et les morts que
vous ferez détermineront quelle équipe sera la meilleure.

N’hésitez pas à prendre part à cette énorme partie de rigolade, qui
vous permettra de vous détendre après (ou avant) les cours.

Pour s’inscrire, direction le site du Killer Western :
http://killer.troll.penche.free.fr

Pour toutes questions, contactez-nous via le forum
(http://ae.utbm.fr/forum2/?id_sujet=9569) ou directement par mail à
killer.troll.penche@gmail.com.

----------------------------------------------------
Matmatronch' - L'édition papier du Mat'matronch revient!
----------------------------------------------------

Mais qui est-il, mais de qui parlez-vous, comment puis-je le contacter ???
Heureusement, Monsieur Mat’Matronch est là pour éviter au maximum ces
situations…

Hé oui, le Mat’Matronch est la seule, l’unique bible indispensable de tout
étudiant consciencieux, en tant qu’annuaire des étudiants de l’école.

Toute la population de l’UTBM est recensée, le Mat’ Matronch donne enfin un
nom à un visage, le numéro de téléphone du binôme introuvable, l’adresse de
cette personne que l’on recherche tant depuis cette fameuse soirée et tout
autre renseignement aussi sérieux administrativement parlant que
fantaisiste.

Mais voila pour que celui ci prenne vie, on a besoin de vous!

En effet, pour que l'annuaire soit "parfait":
1°) il faut que vos fiches Mat'matronch soit complétées et à jour, pour
cela, une seule adresse : http://ae.utbm.fr
2°) et encore plus important il nous faut "des bras", c'est pour cela que
j'invite les curieux à nous rejoindre!

Ensuite pour vous le procurer en pré-vente, rendez vous sur l'E-boutic à
cette adresse: http://ae.utbm.fr/e-boutic/?id_produit=566

Celui ci est en vente pour la modique somme de 4€ et paraîtra courant
Janvier, le thème vous sera alors dévoiler à ce moment!

Pour plus d'info, n'hésitez pas à vous rendre à cette adresse
http://ae.utbm.fr/asso.php?id_asso=27 ou alors à nous contacter à
matmatronch@utbm.fr

A bientôt

Gon pour le Mat'matronch

----------------------------------------------------
Prom'Art - Concert unique : 8bit electro à La Poudrière
----------------------------------------------------

Samedi 3 octobre prochain, ne ratez un concert unique en son genre, du 8-bit
electro chipmusic par des as du genre, Dumbmood et DeDlAy_GaMeBoY_aDDiCt.
Ces musiciens allient les sonorités de nos vieilles gameboys et autres Atari
pour faire un son aux rythmes endiablés. Laissez vous emporter par cette
programmation exceptionnelle à La Poudrière de Belfort.

Infos : http://ae.utbm.fr/news.php?id_nouvelle=1686
Prix : 5e (Préventes ou Eboutic)
Lieu : La Poudrière, Belfort, 22h

Vid pour Prom'Art

----------------------------------------------------
Semaine De Mars 2010 – Première réunion
----------------------------------------------------

Bonjour à tous,

Après un long inter-semestre passé loin de Belfort et de l'UTBM, la semaine
de mars te permet de retrouver tes amis qui t'ont tellement manqué.

Mais avant ces festivités, il faut penser à son organisation. Et qui dit
organisation, dit recherche de bénévole. Toi aussi, tu as envie de
t'investir dans la vie associative, la semaine de mars est là pour toi.
Viens nous aider à organiser activités et soirées, et partager une
expérience hors du commun pour le plus grand plaisir de chacun.

Si tu es intéressé par l'organisation de la Semaine de Mars 2010, participe
à la première réunion qui aura lieu le jeudi 01 octobre à 18 heures en salle
A209 (site de Belfort)

A jeudi.

Couscous' et Iss',
Responsables SDM10

----------------------------------------------------
Le Congrès Industriel arrive à grand pas !
----------------------------------------------------

Les 7 et 8 octobre prochain sur le site de Sevenans :

  - 10 conférences sur le thème de la communication
  - 22 entreprises présentes au Forum à votre disposition pour les offres de
stage ST10, ST40, ST50 et des offres d’emploi
  - des simulations d’entretiens d’embauche animés par des professionnels
  - des ateliers premier emploi proposés par l’APE

Que demander de mieux pour votre insertion dans la vie professionnelle !
Préparez-vous, venez retrouver toutes les informations sur notre site
internet :
http://ae.utbm.fr/congres

Nous vous attendons nombreux !

L’équipe Congrès Industriel 2009

----------------------------------------------------
Le 4L Trophy fait sa rentrée!
----------------------------------------------------

Amies baroudeuses, amis baroudeurs

Vous avez l'âme d'un aventurier, un grand coeur de bisounours!!! Le 4L
trophy est fait pour vous! Le 4L Trophy est un raid à la fois humanitaire et
sportif. Le challenge à relever est d’acheminer des fournitures scolaires
aux enfants marocains en Renault 4L et cela en traversant l’Europe pour
rejoindre le désert marocain soit environ 7000km!
Cette expérience est unique sur tous les plans et les participants en
gardent des souvenirs impérissables. Lors de l’édition 2009, 3 véhicules
UTBM ont vécu cette épopée mêlant galères et joie, paysages enneigés et
sableux, solidarité et échanges culturels… Maintenant, si l’aventure vous
tente, rejoignez nous lors de notre réunion de rentrée le jeudi 01 Octobre
2009 à 18h en salle rantanplan!

Pour nous contacter, une seule adresse: 4l.trophy@utbm.fr ou suivez les
4L....

Gon pour le 4L trophy

----------------------------------------------------
Club Kart - Réunion de rentrée
----------------------------------------------------

Bonjour à tous !

Fan de sport automobile, envie de se faire plaisir sur un karting 2 temps.
Le club Kart est la pour toi et te défouler après les cours. Le club a
besoin de toi pour continuer à vivre !

Viens découvrir le club en assistant à la réunion de rentrée qui se tiendra
à la salle Jolly Jumper ce mercredi 30/09 à 19h30.

A bientôt,

Dagrume pour le club Kart.

----------------------------------------------------
le club magie UT est de retour
----------------------------------------------------

Salut à tous,

le club magie fait sa rentrée,

Alors toi qui veux percer le secret et l'art de la magie,
je t'invite à la réunion de magie UT ce mercredi à 20h00 pour la réunion de
rentrée.
Et surtout n'es pas peur, tous les niveaux sont acceptés.

Le but de cette réunion est de comprendre comment vont dérouler les séances.
C'est aussi location de me dire quels sont tes attentes.
Tu aime plutôt les carte, changer l'eau en bière, la lecture des cerveaux
pour trouver les bonnes réponses lors des médians...

@+ les magicos

Dézande
responsable de Magie UT

----------------------------------------------------
UNITEC - Réunion de rentrée du club de Robotique Mercredi a 20H en A200
----------------------------------------------------

Bonjour a tous et a toutes,

Le club Unitec participe tout les ans à la coupe de France de Robotique
(anciennement appelée coupe E=m6).
Nous effectuons une réunion de rentrée le mercredi 30 septembre 2009 à 20h
dans
l'amphi A200 du Bâtiment A de l'UTBM de Belfort pour y présenter le club et
le
thème de la coupe 2010 nommé « Feed The World ».

Le club est ouvert à tous les départements de l'UTBM (GESC, GM, GI, IMAP,
EDIM).
Nous recrutons notamment pour faire de la communication (particulièrement
pour
démarcher des sponsors ou des partenariats), de la logistique et régie et
bien
sûr de la conception et réalisation.

Le club est ouvert à TOUS : curieux, débutant, ou confirmé, il y a de la
place pour tout le
monde, n'hésitez pas a venir découvrir le club.

On vous attend nombreux mercredi !


Marc et Daouid, pour Unitec
----------------------------------------------------
UTtoons - Séances de la semaine
----------------------------------------------------

Bonjour à toutes et à tous,
En ce début de semaine, nous vous invitons à venir découvrir ce lundi 28
20h dans l'amphi A200, les films Coraline et Fourmiz.

Coraline

Coraline Jones est une fillette intrépide et douée d'une curiosité sans
limites. Ses parents, qui ont tout juste emménagé avec elle dans une étrange
maison, n'ont guère de temps à lui consacrer. Pour tromper son ennui,
Coraline décide donc de jouer les exploratrices. Ouvrant une porte
condamnée, elle pénètre dans un appartement identique au sien... mais où
tout est différent. Dans cet Autre Monde, chaque chose lui paraît plus
belle, plus colorée et plus attrayante.

Fourmiz

Z-4195, fourmi ouvrière, est amoureuse de la belle princesse Bala. Simple
numéro parmi les milliards composant sa colonie il n'a aucune chance
d'attirer le regard de la belle. Pourtant il demande l'aide de son meilleur
ami, la fourmi soldat Weaver, afin d'approcher l'élue de son coeur. C'est
ainsi que par un caprice du hasard, il parasite involontairement le plan
machiavélique de l'ambitieux général Mandibule qui veut tout bonnement
liquider la colonie afin de la recréer a son image. Z se retrouve bientôt a
la tète d'une révolution.

En attendant de vous voir, bonne semaine à tous.

Ticho et Otine

----------------------------------------------------
Prix Universitaire du Logiciel Libre - Réunion de Lancement
----------------------------------------------------

Le Prix Universitaire du Logiciel Libre est un projet visant à récompenser
l’investissement des étudiants, enseignants, chercheurs, dans des projets de
logiciels libre, et à leur permettre de continuer à donner de leur temps
pour de tels projets. Il s'agit d'une toute nouvelle activité, lancée ce
semestre par l'AE.

Cette première réunion aura pour but de constituer une équipe, qui
s’occupera à la fois du travail en amont, mais aussi de l’organisation de la
cérémonie de remise des prix. Rejoignez-nous en Salle activité, ce lundi à
19h30, si vous souhaitez-participer à ce nouveau projet !

contacts : kiri : jeremie.laval@utbm.fr ou zaps : loic.geslin@utbm.fr

A Bientôt et souvenez vous, we are all PULL lovers !

----------------------------------------------------
Relance du Petit Géni !
----------------------------------------------------

Petit rappel pour tous ceux qui sont arrivés cette année : le Petit Géni est
un guide (gratuit!) qui répertorie toutes les adresses utiles de l'aire
urbaine Belfort-Montbéliard. Il est entièrement réalisé par les étudiants de
l'UTBM et tiré à 10 000 exemplaires. Il s'adresse aussi bien aux étudiants
qu'aux habitants de l'aire urbaine.

Seulement pour fonctionner, il faut une équipe... c'est là que vous entrez
en jeu !
La première étape consistera à préparer le site Internet et à remettre à
jour toutes les données.

On recherche, en plus de membres actifs :
-un trésorier
-un responsable informatique
-un secrétaire  / responsable com'

Si vous souhaiter participer ou pour toute question, envoyez un mail à
petit.geni@utbm.fr

Eboul' pour le club Petit Géni

----------------------------------------------------
Le club-zik paye son boeuf
----------------------------------------------------

Bonjour a tous

Le club zik paye son bœuf pour la rentrée… que tu sois simple amateur ou
envie d’exprimer ton talent devant un public
ou encore trouver des comparses de répétitions. Alors le bœuf de rentrée est
fait pour toi.
Nous te donnons donc rendez-vous au foyer ce jeudi 1er octobre à partir de
20h.
Apporte tes instrus, de la viole de gambe au kazou en passant par la guitare
et la contre bassine.
Si tu a une voix et que tu veux en faire profiter, tu es le bienvenue….
Nous vous attendons nombreux. Le bar sera bien sûr ouvert.

Flex pour le club-zik

----------------------------------------------------
Club Cuisine - C'est reparti
----------------------------------------------------

Bonjours tout le monde,

Le club cuisine est de retour. Petit rappel du club : L’objectif du club est
de se faire des repas bien cossu entre nous. On se fait tous notre
tambouille, ensuite on la mange. Les repas se font par groupe (groupe
entrée, groupe plat, groupe dessert), chaque groupe s’organise pour choisir
ce qu’il va faire, et pour se financer (entre 2 et 4 € par personnes, maxi).
Comme on est un petit nombre, on peut se faire un très bon repas pour pas
cher.

Après une petite réunion un peu privée, la date et le thème du premier repas
ont été trouvés. Cela sera le dimanche 4 Octobre à 20 heures au foyer, avec
comme thème « agrume ». Merci de prévenir avant vendredi si vous êtes
intéressé pour le repas, en m’envoyant un mail (julien.mottez@utbm.fr). On
peut fournir les ustensiles pour cuisiner, le foyer est réservé avant donc,
vous pourrez faire la cuisine dedans.

J’espère que vous viendrez nombreux. Si vous n’avez des questions ou des
remarques, n’hésitez pas à me le dire.

Zévou pour le club cuisine.

PS : Pour toutes les personnes intéressées par le club, n’hésitez pas à vous
inscrire sur le site AE : http://ae.utbm.fr/asso.php?id_asso=79

----------------------------------------------------
La blague !
----------------------------------------------------

Dans le gruyère, il y a des trous.
Plus il y a de gruyère, plus il y a de trous.
Mais plus il y a de trous, moins il y a de gruyère.
Donc plus il y a de gruyère, moins il y a de gruyère.

--
à la semaine prochaine !
A6
EOF;
$mailer->set_plain($content);
$html = <<<EOF
<html>
<body bgcolor="#333333" width="700px">
<table bgcolor="#333333" width="700px">
<tr><td align="center">
<table bgcolor="#ffffff" width="600" border="0" cellspacing="0" cellpadding="0" align="center">
<tr><td width="601" height="241"><img src="http://ae.utbm.fr/d.php?id_file=4523&action=download" /></td></tr>
<tr bgcolor="#000000"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Introduction</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Salut les UTbohémiens,<br />
<br />
Nouvelle semaine, nouveau weekmail ! Il est grand temps d'attaquer le vif du
sujet. Les clubs ont presque tous repris du service, et le congrès se
profile à l'horizon. Voici les news de la semaine.
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Sommaire</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px"><ul>
<li>Assemblées Générales de l’AE, du BDF, du BDS et du CETU</li>
<li>Troll Penché - Killer</li>
<li>Matmatronch' - L'édition papier du Mat'matronch revient!</li>
<li>Prom'Art - Concert unique : 8bit electro à La Poudrière</li>
<li>Semaine De Mars 2010 – Première réunion</li>
<li>Le Congrès Industriel arrive à grand pas !</li>
<li>Le 4L Trophy fait sa rentrée!</li>
<li>Club Kart - Réunion de rentrée</li>
<li>le club magie UT est de retour</li>
<li>UNITEC - Réunion de rentrée du club de Robotique Mercredi a 20H en A200</li>
<li>UTtoons - Séances de la semaine</li>
<li>Prix Universitaire du Logiciel Libre - Réunion de Lancement</li>
<li>Relance du Petit Géni !</li>
<li>Le club-zik paye son boeuf</li>
<li>Club Cuisine - C'est reparti</li>
</ul>
</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Assemblées Générales de l’AE, du BDF, du BDS et du CETU</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Le mardi 29 septembre à 13 heures à la MDE - Kfet (en-dessous du Restauranti Universitaire) de Sevenans et à 20 heures au Foyer de la Maison des Élèves de Belfort, les quatre associations étudiantes de l’UTBM t’invitent à venir à leurs Assemblées Générales de début de semestre. Il s’agit d’une présentation rapide des associations, de leurs budgets et de leurs projets pour le semestre. Donc si toi aussi tu veux apprendre à connaitre tes associations et leurs fonctionnements, viens le mardi 29 septembre à 13 heures à la MDE - Kfet de Sevenans ou à 20 heures au  Foyer à Belfort. De plus, après la session à Belfort au Foyer, tu pourras te restaurer pour une petite somme avec un barbecue organisé par les quatre associations.
<br /><br />
A mardi !<br />
Les équipes AE, BDF, BDS et CETU
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Troll Penché - Killer</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Wanted : dead or... dead
<br /><br />
Hey Gringo, tu en as marre de chasser le coyote ? Tu rêves d’une vie de Cowboy, galopant dans la grande plaine à la poursuite de dangereux
bandits. Mais fais bien attention à ton scalp, les peaux rouges ne sont pas tendres avec les voyageurs solitaires.<br /><br />

Le Killer Western est un jeu d’équipe où les joueurs vont s’affronter dans toute l’UTBM pendant dix jours. Cet événement se déroulera bien entendu sur les 3 sites de l’UTBM, pendant 10 jours, et est ouvert à tous les UTBohéMiens. L’accomplissement de missions et les morts que vous ferez détermineront quelle équipe sera la meilleure.
<br /><br />
N’hésitez pas à prendre part à cette énorme partie de rigolade, qui vous permettra de vous détendre après (ou avant) les cours.
<br /><br />
Pour s’inscrire, direction le site du Killer Western :<br />
<a href="http://killer.troll.penche.free.fr">ici</a>
<br /><br />
Pour toutes questions, contactez-nous via le <a href="http://ae.utbm.fr/forum2/?id_sujet=9569">forum</a> ou directement par <a href="mailto:killer.troll.penche@gmail.com">mail</a>
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Matmatronch' - L'édition papier du Mat'matronch revient!</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Mais qui est-il, mais de qui parlez-vous, comment puis-je le contacter ???
Heureusement, Monsieur Mat’Matronch est là pour éviter au maximum ces
situations…
<br /><br />
Hé oui, le Mat’Matronch est la seule, l’unique bible indispensable de tout
étudiant consciencieux, en tant qu’annuaire des étudiants de l’école.
<br /><br />
Toute la population de l’UTBM est recensée, le Mat’ Matronch donne enfin un
nom à un visage, le numéro de téléphone du binôme introuvable, l’adresse de
cette personne que l’on recherche tant depuis cette fameuse soirée et tout
autre renseignement aussi sérieux administrativement parlant que
fantaisiste.
<br /><br />
Mais voila pour que celui ci prenne vie, on a besoin de vous!
<br />
En effet, pour que l'annuaire soit "parfait":<br />
1°) il faut que vos fiches Mat'matronch soit complétées et à jour, pour
cela, une seule adresse : <a href="http://ae.utbm.fr">http://ae.utbm.fr</a><br />
2°) et encore plus important il nous faut "des bras", c'est pour cela que
j'invite les curieux à nous rejoindre!
<br /><br />
Ensuite pour vous le procurer en pré-vente, rendez vous sur l'E-boutic à
cette adresse: http://ae.utbm.fr/e-boutic/?id_produit=566
<br /><br />
Celui ci est en vente pour la modique somme de 4€ et paraîtra courant
Janvier, le thème vous sera alors dévoiler à ce moment!
<br /><br />
Pour plus d'info, n'hésitez pas à vous rendre à cette adresse
http://ae.utbm.fr/asso.php?id_asso=27 ou alors à nous contacter à
matmatronch@utbm.fr
<br /><br />
A bientôt
<br />
Gon pour le Mat'matronch
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Prom'Art - Concert unique : 8bit electro à La Poudrière</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Samedi 3 octobre prochain, ne ratez un concert unique en son genre, du 8-bit
electro chipmusic par des as du genre, Dumbmood et DeDlAy_GaMeBoY_aDDiCt.
Ces musiciens allient les sonorités de nos vieilles gameboys et autres Atari
pour faire un son aux rythmes endiablés. Laissez vous emporter par cette
programmation exceptionnelle à La Poudrière de Belfort.
<br /><br />
Infos : <a href="http://ae.utbm.fr/news.php?id_nouvelle=1686">ici</a><br />
Prix : 5e (Préventes ou Eboutic)<br />
Lieu : La Poudrière, Belfort, 22h<br />
<br /><br />
Vid pour Prom'Art
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Semaine De Mars 2010 – Première réunion</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Bonjour à tous,
<br /><br />
Après un long inter-semestre passé loin de Belfort et de l'UTBM, la semaine
de mars te permet de retrouver tes amis qui t'ont tellement manqué.
<br /><br />
Mais avant ces festivités, il faut penser à son organisation. Et qui dit
organisation, dit recherche de bénévole. Toi aussi, tu as envie de
t'investir dans la vie associative, la semaine de mars est là pour toi.
Viens nous aider à organiser activités et soirées, et partager une
expérience hors du commun pour le plus grand plaisir de chacun.
<br /><br />
Si tu es intéressé par l'organisation de la Semaine de Mars 2010, participe
à la première réunion qui aura lieu le jeudi 01 octobre à 18 heures en salle
A209 (site de Belfort)
<br /><br />
A jeudi.
<br /><br />
Couscous' et Iss',<br />
Responsables SDM10
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Le Congrès Industriel arrive à grand pas !</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">
Les 7 et 8 octobre prochain sur le site de Sevenans :
<ul>
<li>10 conférences sur le thème de la communication</li>
<li>22 entreprises présentes au Forum à votre disposition pour les offres de stage ST10, ST40, ST50 et des offres d’emploi</li>
<li>des simulations d’entretiens d’embauche animés par des professionnels</li>
<li>des ateliers premier emploi proposés par l’APE</li>
</ul>
Que demander de mieux pour votre insertion dans la vie professionnelle !<br />
Préparez-vous, venez retrouver toutes les informations sur notre site
internet : <a href="http://ae.utbm.fr/congres">http://ae.utbm.fr/congres</a>
<br /><br />
Nous vous attendons nombreux !
<br /><br />
L’équipe Congrès Industriel 2009
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Le 4L Trophy fait sa rentrée!</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Amies baroudeuses, amis baroudeurs
<br /><br />
Vous avez l'âme d'un aventurier, un grand coeur de bisounours!!! Le 4L
trophy est fait pour vous! Le 4L Trophy est un raid à la fois humanitaire et
sportif. Le challenge à relever est d’acheminer des fournitures scolaires
aux enfants marocains en Renault 4L et cela en traversant l’Europe pour
rejoindre le désert marocain soit environ 7000km!<br />
Cette expérience est unique sur tous les plans et les participants en
gardent des souvenirs impérissables. Lors de l’édition 2009, 3 véhicules
UTBM ont vécu cette épopée mêlant galères et joie, paysages enneigés et
sableux, solidarité et échanges culturels… Maintenant, si l’aventure vous
tente, rejoignez nous lors de notre réunion de rentrée le jeudi 01 Octobre
2009 à 18h en salle rantanplan!
<br /><br />
Pour nous contacter, une seule adresse: 4l.trophy@utbm.fr ou suivez les
4L....
<br /><br />
Gon pour le 4L trophy
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Club Kart - Réunion de rentrée</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Bonjour à tous !
<br /><br />
Fan de sport automobile, envie de se faire plaisir sur un karting 2 temps.
Le club Kart est la pour toi et te défouler après les cours. Le club a
besoin de toi pour continuer à vivre !
<br /><br />
Viens découvrir le club en assistant à la réunion de rentrée qui se tiendra
à la salle Jolly Jumper ce mercredi 30/09 à 19h30.
<br /><br />
A bientôt,
<br />
Dagrume pour le club Kart.
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">le club magie UT est de retour</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Salut à tous,
<br /><br />
le club magie fait sa rentrée,
<br /><br />
Alors toi qui veux percer le secret et l'art de la magie,
je t'invite à la réunion de magie UT ce mercredi à 20h00 pour la réunion de
rentrée.<br />
Et surtout n'es pas peur, tous les niveaux sont acceptés.
<br /><br />
Le but de cette réunion est de comprendre comment vont dérouler les séances.
C'est aussi location de me dire quels sont tes attentes.
Tu aime plutôt les carte, changer l'eau en bière, la lecture des cerveaux
pour trouver les bonnes réponses lors des médians...
<br /><br />
@+ les magicos<br />
Dézande, responsable de Magie UT
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">UNITEC - Réunion de rentrée du club de Robotique Mercredi a 20H en A200</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Bonjour a tous et a toutes,
<br /><br />
Le club Unitec participe tout les ans à la coupe de France de Robotique
(anciennement appelée coupe E=m6).<br />
Nous effectuons une réunion de rentrée le mercredi 30 septembre 2009 à 20h
danis l'amphi A200 du Bâtiment A de l'UTBM de Belfort pour y présenter le club et
le thème de la coupe 2010 nommé « Feed The World ».
<br /><br />
Le club est ouvert à tous les départements de l'UTBM (GESC, GM, GI, IMAP,
EDIM).<br />
Nous recrutons notamment pour faire de la communication (particulièrement
pour démarcher des sponsors ou des partenariats), de la logistique et régie et
bien sûr de la conception et réalisation.
<br /><br />
Le club est ouvert à TOUS : curieux, débutant, ou confirmé, il y a de la
place pour tout le monde, n'hésitez pas a venir découvrir le club.
<br /><br />
On vous attend nombreux mercredi !
<br /><br />
Marc et Daouid, pour Unitec
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">UTtoons - Séances de la semaine</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Bonjour à toutes et à tous,
<br /><br />
En ce début de semaine, nous vous invitons à venir découvrir ce lundi 28
20h dans l'amphi A200, les films Coraline et Fourmiz.
<br /><br />
Coraline :<br />
Coraline Jones est une fillette intrépide et douée d'une curiosité sans
limites. Ses parents, qui ont tout juste emménagé avec elle dans une étrange
maison, n'ont guère de temps à lui consacrer. Pour tromper son ennui,
Coraline décide donc de jouer les exploratrices. Ouvrant une porte
condamnée, elle pénètre dans un appartement identique au sien... mais où
tout est différent. Dans cet Autre Monde, chaque chose lui paraît plus
belle, plus colorée et plus attrayante.
<br /><br />
Fourmiz :<br />
Z-4195, fourmi ouvrière, est amoureuse de la belle princesse Bala. Simple
numéro parmi les milliards composant sa colonie il n'a aucune chance
d'attirer le regard de la belle. Pourtant il demande l'aide de son meilleur
ami, la fourmi soldat Weaver, afin d'approcher l'élue de son coeur. C'est
ainsi que par un caprice du hasard, il parasite involontairement le plan
machiavélique de l'ambitieux général Mandibule qui veut tout bonnement
liquider la colonie afin de la recréer a son image. Z se retrouve bientôt a
la tète d'une révolution.
<br /><br />
En attendant de vous voir, bonne semaine à tous.
<br />
Ticho et Otine
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Prix Universitaire du Logiciel Libre - Réunion de Lancement</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Le Prix Universitaire du Logiciel Libre est un projet visant à récompenser
l’investissement des étudiants, enseignants, chercheurs, dans des projets de
logiciels libre, et à leur permettre de continuer à donner de leur temps
pour de tels projets. Il s'agit d'une toute nouvelle activité, lancée ce
semestre par l'AE.
<br /><br />
Cette première réunion aura pour but de constituer une équipe, qui
s’occupera à la fois du travail en amont, mais aussi de l’organisation de la
cérémonie de remise des prix. Rejoignez-nous en Salle activité, ce lundi à
19h30, si vous souhaitez-participer à ce nouveau projet !
<br /><br />
contacts : <a href="mailto:jeremie.laval@utbm.fr">kiri</a> ou <a href="mailto:loic.geslin@utbm.fr">zaps</a>
<br /><br />
A Bientôt et souvenez vous, we are all PULL lovers !
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Relance du Petit Géni !</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Petit rappel pour tous ceux qui sont arrivés cette année : le Petit Géni est
un guide (gratuit!) qui répertorie toutes les adresses utiles de l'aire
urbaine Belfort-Montbéliard. Il est entièrement réalisé par les étudiants de
l'UTBM et tiré à 10 000 exemplaires. Il s'adresse aussi bien aux étudiants
qu'aux habitants de l'aire urbaine.
<br /><br />
Seulement pour fonctionner, il faut une équipe... c'est là que vous entrez
en jeu !<br />
La première étape consistera à préparer le site Internet et à remettre à
jour toutes les données.
<br /><br />
On recherche, en plus de membres actifs :
<ul>
<li>un trésorier</li>
<li>un responsable informatique</li>
<li>un secrétaire  / responsable com'</li>
</ul>
Si vous souhaiter participer ou pour toute question, envoyez un mail à
<a href="mailto:petit.geni@utbm.fr">petit.geni@utbm.fr</a>
<br /><br />
Eboul' pour le club Petit Géni
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Le club-zik paye son boeuf</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Bonjour a tous
<br /><br />
Le club zik paye son bœuf pour la rentrée… que tu sois simple amateur ou
envie d’exprimer ton talent devant un public
ou encore trouver des comparses de répétitions. Alors le bœuf de rentrée est
fait pour toi.<br /><br />
Nous te donnons donc rendez-vous au foyer ce jeudi 1er octobre à partir de
20h.<br /><br />
Apporte tes instrus, de la viole de gambe au kazou en passant par la guitare
et la contre bassine.<br />
Si tu a une voix et que tu veux en faire profiter, tu es le bienvenue….
Nous vous attendons nombreux. Le bar sera bien sûr ouvert.
<br /><br />
Flex pour le club-zik
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Club Cuisine - C'est reparti</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Bonjours tout le monde,
<br /><br />
Le club cuisine est de retour. Petit rappel du club : L’objectif du club est
de se faire des repas bien cossu entre nous. On se fait tous notre
tambouille, ensuite on la mange. Les repas se font par groupe (groupe
entrée, groupe plat, groupe dessert), chaque groupe s’organise pour choisir
ce qu’il va faire, et pour se financer (entre 2 et 4 € par personnes, maxi).
Comme on est un petit nombre, on peut se faire un très bon repas pour pas
cher.
<br /><br />
Après une petite réunion un peu privée, la date et le thème du premier repas
ont été trouvés. Cela sera le dimanche 4 Octobre à 20 heures au foyer, avec
comme thème « agrume ». Merci de prévenir avant vendredi si vous êtes
intéressé pour le repas, en m’envoyant un mail (julien.mottez@utbm.fr). On
peut fournir les ustensiles pour cuisiner, le foyer est réservé avant donc,
vous pourrez faire la cuisine dedans.
<br /><br />
J’espère que vous viendrez nombreux. Si vous n’avez des questions ou des
remarques, n’hésitez pas à me le dire.
<br /><br />
Zévou pour le club cuisine.
<br /><br />
PS : Pour toutes les personnes intéressées par le club, n’hésitez pas à vous
inscrire sur le <a href="http://ae.utbm.fr/asso.php?id_asso=79">site AE</a>
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">La blague !</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">
Dans le gruyère, il y a des trous.<br />
Plus il y a de gruyère, plus il y a de trous.<br />
Mais plus il y a de trous, moins il y a de gruyère.<br />
Donc plus il y a de gruyère, moins il y a de gruyère.
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Le mot de la fin</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">à la semaine prochaine !<br />
A6<br />
<br />
Pour toute réclamation sur le weekmail HTML => ayolo</td></tr>
</table><br />
</td></tr></table>
</body>
</html>
EOF;
$mailer->set_html($html);
$mailer->send();

?>
