<?
//exit();
$topdir = '../';
require_once($topdir. "include/site.inc.php");
require_once($topdir."include/entities/files.inc.php");
require_once($topdir."include/entities/folder.inc.php");
require_once($topdir.'include/lib/mailer.inc.php');
$site = new site();

$mailer = new mailer('Association des Étudiants <ae@utbm.fr>',
                     'Weekmail du 21 au 27 septembre 2009');
$mailer->add_dest('simon.lopez@utbm.fr');
/*$mailer->add_dest(array('etudiants@utbm.fr',
                        'enseignants@utbm.fr',
                        'iatoss@utbm.fr',
                        'aude.petit@utbm.fr'));
*/
$content=<<<EOF
Salut les UTbohémiens,

L'intégration s'achève en beauté, mais le semestre débute tout juste comme
vous allez le voir dans ce deuxième weekmail remplis de réunions de
présentation de clubs de notre chère association !



Sommaire :

 * AE - Commissions de pôle
 * Club Welcome - Welcome need U !
 * Club Mix & MAO - Réunions de rentrée
 * La Bohème fait sa rentrée
 * [ART] is Back !
 * Le club Astro fait sa rentrée
 * Club Zik - réouverture des crenaux de repetitions
 * Club Zenith - Réunion de rentré
 * Club danse moderne jazz a besoin de vous !
 * Le club de modélisme fait sa rentré !
 * UTtoons, ça repars

----------------------------------------------------
AE - Commissions de pôle
----------------------------------------------------

Bonjour à tous,

Avec la fin de l'intégration, arrive la reprise des clubs de l'AE, si tu te
sens
l'âme 'un responsable de club, si tu souhaites créer une nouvelle activité,
ou
en réactiver un, le moment est venu de te faire connaître ! En effet, la
semaine prochaine auront lieu les comissions de pôles, où seront discutés
les
projets du semestre ainsi que les budgets.

Si l'aventure de responsable de club te tente, rendez-vous :

- Lundi 21/09/09 à 20 heures: Pôle Technique
- Lundi 21/09/09 à 21 heures: Pôle Culturel
- Mardi 22/09/09 à 20 heures 30: Grandes Activités
- Mercredi 23/09/09 à 20 heures: Pôle Entraide et Humanitaire
- Mercredi 24/09/09 à 21 heures: Pôle Artistique

Si vous êtes intéressés par l'aventure, pensez à un budget prévisionnel, et
si
vous avez des questions, rendez vous sur le forum de l'AE

à bientôt

L'équipe AE

----------------------------------------------------
Club Welcome - Welcome need U !
----------------------------------------------------

Bonjour à tous,

Une expérience à l'international vous tente ? Par simple curiosité ou ayant
déjà un projet de mobilité bien défini, le club Welcome propose à vous,
étudiants de l'UTBM, de nouer des liens dans un environnement interculturel
et convivial ! Venez à la rencontre des étudiants étrangers provenant des
quatre coins du monde pour étudier à l'UTBM et découvrir la culture
Française !

La richesse des échanges interculturels, ayant aussi bien lieu à Belfort
qu'à l'étranger, est souvent insoupçonnée! L'enrichissement personnel est
indéniable; par ailleurs créer des liens avec des étudiants désireux de
franchir le pont culturel peut vous ouvrir de nouveaux horizons, créer de
nouvelles opportunités.

Au moment de l'intégration et de la reprise des cours, le club Welcome fait
également sa rentrée, et vous convie à une réunion d'information pour vous
faire découvrir les activités qui vont se dérouler tout au long de ce
semestre ! Tout le monde peut participer, Ancien comme Nouveau, Tronc commun
autant que Branche !

Alors n'hésitez plus, et venez nous rencontrer le jeudi 17 septembre à 16h
en salle Rantanplan (bâtiment des Dalton de la Maison des Eleves à Belfort).

D'ici là, nous vous donnons rendez-vous sur le site web du club Welcome:
http://ae.utbm.fr/welcome/

A très bientôt !!

L'équipe du club Welcome

----------------------------------------------------
Club Mix & MAO - Réunions de rentrée
----------------------------------------------------

Salut à tous,

Le club Mix et le club MAO (Musique Assistée par Ordinateur) reprennent
enfin du service !

Passionné de mix, avide de découvrir, besoin de matériel pour perfectionner
sa pratique ? Rejoignez nous lors de notre réunion de présentation afin de
mettre en place l'organisation du club pour le semestre.

Néophytes ou expérimentés sont les bienvenus, venez discuter avec nous ce
Jeudi 24 Septembre à 14h à la MDE de Sévenans.

On vous espère nombreux !

A6 & Spinnou pour le CX et la MAO !

----------------------------------------------------
La Bohème fait sa rentrée
----------------------------------------------------

La Bohème fait sa rentrée
Le journal que vous avez eu l'occasion de lire depuis votre rentrée en début
du mois est rédigé par des étudiants. Ils mettent le plus régulièrement
possible tout en oeuvre pour vous fournir du contenu de qualité à travers
différents types d'articles.
Afin de poursuivre cet effort d'informations et de détente, vous êtes tous
invité à venir intégrer l'équipe de rédaction pour des  articles de temps en
temps quand cela vous dit.
On en recherche sur tous les sujets ;)

Réunion mercredi 23 septembre à 18h en salle rantanplan
contact : laboheme@utbm.fr
site : ae.utbm.fr/boheme

----------------------------------------------------
[ART] is Back !
----------------------------------------------------

La Semaine des Arts et le club Prom'Art reviennent pour faire découvrir les
arts et la culture au plus grand nombre !
Pendant une Semaine et tout au long du semestre nous organisons des sorties,
événements et visites à l'UTBM ou dans les lieux culturels des environs.
Si toi aussi tu t'intéresse de près ou de loin au domaine culturel nous
sommes à la recherche de bonne volonté pour monter la seconde édition de la
SdA.
Postes recherchés : trésoriers, responsable sponsors, responsable...

Réunion jeudi 24 septembre à 16h en salle rantanplan Belfort
contact : promart@utbm.fr
site : ae.utbm.fr/sda

----------------------------------------------------
Le club Astro fait sa rentrée
----------------------------------------------------

C'est l'occasion de venir découvrir le Club de l'UTBM et de voir le
déroulement des séances que nous vous proposons.
En cette année mondiale de l'astronomie, il serait dommage de rater
l'initiation que nous proposons !

Le Club Astro dispose de moyen pour l'observation ainsi que des
documentaires pour apprendre l'astronomie.
Je rappelle que ce club est ouvert à tous les niveaux.

N'hésiter pas à me contacter pour plus de renseignements :
tristan.lebeaume@utbm.fr
Venez nombreux jeudi 24 septembre à 20 heures, devant la salle Jolly Jumper
sous la ME(Maison des Élèves) de Belfort.

----------------------------------------------------
Club Zik - réouverture des crenaux de repetitions
----------------------------------------------------

Bonjour à toi Musicien(e)s de l'UTBM en manque de lieux pour répéter ?

La salle de répétition de Sévenans est désormais ouverte et il est tant de
réserver vos créneaux de répète, l'idéal est d?en prendre un de deux heures
au semestre pour laissez exprimer votre art.

Petit rappel, la salle comporte une batterie (baguettes non fournies), des
amplis guitares, un ampli basse, un piano, et tout se qu'il faut pour vos
vocalises. Pour finir, il vous faut obligatoirement être cotisant à l'AE
pour utiliser ce matériel.

Pour réserver un créneau c'est simple, renvoyer un mail à
Club.Zik@utbm.fr avec vos coordonnées et votre choix de réservation.
Certains créneaux étant
assez convoités, nous emploierons la technique du 1er servis.

Toute fois un créneau n'est pas disponible, il s'agit du jeudi soir de 20 à
22h, occupé par le Big Band de l'UTBM. La salle reste disponible du lundi au
samedi de 08h à 23h.

Musciquement vôtre
Flex pour le club zik

----------------------------------------------------
Club Zenith - Réunion de rentré
----------------------------------------------------

Zénith fait sa rentrée ! Le club de l'AE qui participe au Shell Eco
Marathon, ou comment faire le plus de kilomètre possible avec un litre
d'essence.

C'est donc de vivre une compétition automobile dont il est question, mais
toutes les branches de notre université sont nécessaires pour faire avancer
ce prototype !

Au cours de cette réunion de découverte nous verrons une présentation du
club et son historique, les objectifs à venir pour cette saison et la
voiture! Bien entendu l'équipe sera la pour faire connaissance avec toi.

La réunion aura lieu Mardi 22 septembre sur le site de Belfort dans le
bâtiment A salle A210 à 19h30.

Carbonman pour le club Zenith

----------------------------------------------------
Club danse moderne jazz a besoin de vous !
----------------------------------------------------

Bonjour à tous,

le club danse refait son entrée pour cette année. Venez nous rejoindre pour
danser lors de séances sympathiques entre nous. Envie de participer au gala,
nous faisons une représentation lors de cette soirée inoubliable. Le but est
tout d'abord de passer des moments agréables donc si vous êtes danseuse et
danseur professionnel(le) ou amateur, vous êtes les bienvenu(e)s...
Nécessitez pas à venir voir comment cela se passe lors des séances le mardi
et le jeudi...
Pour plus d'informations, rendez vous sur le site de l'AE :
http://ae.utbm.fr/
A très bientôt
Le club Danse Moderne Jazz

----------------------------------------------------
Le club de modélisme fait sa rentré !
----------------------------------------------------

Le club de modélisme fait sa rentré !

Alors si tu as toujours rêvé d'être aux commandes d'un hélicoptère ou d'un
avion mais que tu n'en as jamais eu l'occasion, viens découvrir le club
aéro'UT qui met à ta disposition tout le matériel nécessaire . Bien entendu,
les modélistes expérimentés sont eux aussi invités afin de venir partager
connaissances et savoir faire.

Cette réunion de rentré est l'occasion de présenter l'univers du modélisme
mais surtout de découvrir le fonctionnement du club ainsi que les
hélicoptères et avion mis à votre disposition.

Rendez vous à 18h en salle Rantanplan ce jeudi 24 septembre !


Merci,
ZAVA pour le club modélisme

----------------------------------------------------
UTtoons, ça repars
----------------------------------------------------

Oyez, oyez,

UTtoons recommence à égayer vos soirées du lundi de la meilleure manière
possible : Une petite dose régulière d'anciens ou nouveaux dessins animés,
Disney/Pixar ou même japonais,  vous refera plonger en enfance.

Pour bien débuter ce semestre, venez apprécier Princesse Mononoke et Volt
star, ce lundi 21 Septembre à 20h en A200.

Princesse Mononoke

Au XVe siècle, durant l'ère Muromachi, la forêt japonaise, jadis protégée
par des animaux géants, se dépeuple à cause de l'homme. Un sanglier
transformé en démon dévastateur en sort et attaque le village d'Ashitaka,
futur chef du clan Emishi. Touché par le sanglier qu'il a tué, celui-ci est
forcé de partir à la recherche du dieu Cerf pour lever la malédiction qui
lui gangrène le bras.

Volt star

Pour le chien Volt, star d'une série télévisée à succès, chaque journée est
riche d'aventure, de danger et de mystère - du moins devant les caméras. Ce
n'est plus le cas lorsqu'il se retrouve par erreur loin des studios de
Hollywood, à New York... Il va alors entamer la plus grande et la plus
périlleuse de ses aventures - dans le monde réel, cette fois.

En espérant vous voir nombreux,

Otine & Ticho

----------------------------------------------------
La blague !
----------------------------------------------------

Un vieil Arabe vit depuis plus de 40 ans à Chicago. Il aimerait bien planter
des pommes de terre dans son jardin mais il est tout seul, vieux et trop
faible. Il envoie alors un e-mail à son fils qui étudie à Paris pour lui
faire part de son problème.
-"Cher Ahmed, je suis très triste car je ne peux pas planter des pommes de
terre dans mon jardin. Je suis sûr que si tu étais ici avec moi tu aurais pu
m'aider à retourner la terre. Je t'aime, ton Père"
Le lendemain, le vieil homme reçoit un e-mail :
-"Cher Père, s'il te plaît, ne touche surtout pas au jardin ! J'y ai caché
la "chose". Moi aussi je t'aime. Ahmed"
A 4heures du matin arrivent chez le vieillard l'US Army, les Marines, le
FBI,la CIA et même une unité d'élite des Rangers. Ils fouillent tout le
jardin, millimètre par millimètre et repartent déçus car ils n'ont rien
trouvé. Le lendemain, le vieil homme reçoit un nouvel e-mail de la part de
son fils :
- "Cher Père, je suis certain que la terre de tout le jardin est désormais
retournée et que tu peux planter tes pommes de terre.Je ne pouvais pas faire
mieux. Je t'aime, Ahmed"

--
à la semaine prochaine !
A6
EOF;


//$mailer->add_dest("simon.lopez@utbm.fr");
$file = new dfile($site->db);
$file->load_by_id(4328);
$mailer->add_img($file);
$mailer->set_plain($content);
$html = <<<EOF
<html>
<body bgcolor="#333333" width="700px">
<table bgcolor="#333333" width="700px">
<tr><td align="center">
<table bgcolor="#ffffff" width="600" border="0" cellspacing="0" cellpadding="0" align="center">
<tr><td width="600" height="241"><img src="http://ae.utbm.fr/d.php?id_file=4328&action=download" /></td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">Introduction</font></td></tr>
<tr><td>Salut les UTbohémiens,<br />
<br />
L'intégration s'achève en beauté, mais le semestre débute tout juste comme
vous allez le voir dans ce deuxième weekmail remplis de réunions de
présentation de clubs de notre chère association !
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">Sommaire</font></td></tr>
<tr><td><ul><li>AE - Commissions de pôle</li>
<li>Club Welcome - Welcome need U !</li>
<li>Club Mix & MAO - Réunions de rentrée</li>
<li>La Bohème fait sa rentrée</li>
<li>[ART] is Back !</li>
<li>Le club Astro fait sa rentrée</li>
<li>Club Zik - réouverture des crenaux de repetitions</li>
<li>Club Zenith - Réunion de rentré</li>
<li>Club danse moderne jazz a besoin de vous !</li>
<li>Le club de modélisme fait sa rentré !</li>
<li>UTtoons, ça repars</li>
</ul>
</td></tr>
<tr bgcolor="#00BBFF"><td><font color="#ffffff">AE - Commissions de pôle</font></td></tr>
<tr><td>Bonjour à tous,<br />
<br />
Avec la fin de l'intégration, arrive la reprise des clubs de l'AE, si tu te
sens l'âme 'un responsable de club, si tu souhaites créer une nouvelle activité,
ou en réactiver un, le moment est venu de te faire connaître ! En effet, la
semaine prochaine auront lieu les comissions de pôles, où seront discutés
les projets du semestre ainsi que les budgets.<br />
<br />
Si l'aventure de responsable de club te tente, rendez-vous :
<ul>
<li>Lundi 21/09/09 à 20 heures: Pôle Technique</li>
<li>Lundi 21/09/09 à 21 heures: Pôle Culturel</li>
<li>Mardi 22/09/09 à 20 heures 30: Grandes Activités</li>
<li>Mercredi 23/09/09 à 20 heures: Pôle Entraide et Humanitaire</li>
<li>Mercredi 24/09/09 à 21 heures: Pôle Artistique</li>
</ul>
Si vous êtes intéressés par l'aventure, pensez à un budget prévisionnel, et
si vous avez des questions, rendez vous sur le forum de l'AE<br />
<br />
à bientôt<br />
L\'équipe AE
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td><font color="#ffffff">Club Welcome - Welcome need U !</font></td></tr>
<tr><td>Bonjour à tous,

Une expérience à l'international vous tente ? Par simple curiosité ou ayant
déjà un projet de mobilité bien défini, le club Welcome propose à vous,
étudiants de l'UTBM, de nouer des liens dans un environnement interculturel
et convivial ! Venez à la rencontre des étudiants étrangers provenant des
quatre coins du monde pour étudier à l'UTBM et découvrir la culture
Française !

La richesse des échanges interculturels, ayant aussi bien lieu à Belfort
qu'à l'étranger, est souvent insoupçonnée! L'enrichissement personnel est
indéniable; par ailleurs créer des liens avec des étudiants désireux de
franchir le pont culturel peut vous ouvrir de nouveaux horizons, créer de
nouvelles opportunités.<br />
<br />
Au moment de l'intégration et de la reprise des cours, le club Welcome fait
également sa rentrée, et vous convie à une réunion d'information pour vous
faire découvrir les activités qui vont se dérouler tout au long de ce
semestre ! Tout le monde peut participer, Ancien comme Nouveau, Tronc commun
autant que Branche !

Alors n'hésitez plus, et venez nous rencontrer le jeudi 17 septembre à 16h
en salle Rantanplan (bâtiment des Dalton de la Maison des Eleves à Belfort).

D'ici là, nous vous donnons rendez-vous sur le site web du club Welcome:
http://ae.utbm.fr/welcome/

A très bientôt !!

L'équipe du club Welcome
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td><font color="#ffffff">Club Mix & MAO - Réunions de rentrée</font></td></tr>
<tr><td>
Salut à tous,
<br /></br />
Le club Mix et le club MAO (Musique Assistée par Ordinateur) reprennent
enfin du service !
<br /></br />
Passionné de mix, avide de découvrir, besoin de matériel pour perfectionner sa pratique ? Rejoignez nous lors de notre réunion de présentation afin de
mettre en place l'organisation du club pour le semestre.
<br /></br />
Néophytes ou expérimentés sont les bienvenus, venez discuter avec nous ce
Jeudi 24 Septembre à 14h à la MDE de Sévenans.
<br /></br />
On vous espère nombreux !
<br /></br />
A6 & Spinnou pour le CX et la MAO !
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">La Bohème fait sa rentrée</font></td></tr>
<tr><td>
La Bohème fait sa rentrée<br /></br />
Le journal que vous avez eu l'occasion de lire depuis votre rentrée en début
du mois est rédigé par des étudiants. Ils mettent le plus régulièrement
possible tout en oeuvre pour vous fournir du contenu de qualité à travers
différents types d'articles.<br />
Afin de poursuivre cet effort d'informations et de détente, vous êtes tous
invité à venir intégrer l'équipe de rédaction pour des  articles de temps en
temps quand cela vous dit.<br />
On en recherche sur tous les sujets ;)<br /></br />
Réunion mercredi 23 septembre à 18h en salle rantanplan<br />
contact : laboheme@utbm.fr<br />
site : <a href="http://ae.utbm.fr/boheme">ici</a><br />
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">[ART] is Back !</font></td></tr>
<tr><td>
La Semaine des Arts et le club Prom'Art reviennent pour faire découvrir les
arts et la culture au plus grand nombre !<br /><br />
Pendant une Semaine et tout au long du semestre nous organisons des sorties,
événements et visites à l'UTBM ou dans les lieux culturels des environs.
Si toi aussi tu t'intéresse de près ou de loin au domaine culturel nous
sommes à la recherche de bonne volonté pour monter la seconde édition de la
SdA.<br /><br/>
Postes recherchés : trésoriers, responsable sponsors, responsable...
<br /><br />
Réunion jeudi 24 septembre à 16h en salle rantanplan Belfort<br />
contact : promart@utbm.fr<br />
site : <a href="http://ae.utbm.fr/sda">ici</a><br />
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">Le club Astro fait sa rentrée</font></td></tr><tr><td>
C'est l'occasion de venir découvrir le Club de l'UTBM et de voir le
déroulement des séances que nous vous proposons.<br />
En cette année mondiale de l'astronomie, il serait dommage de rater
l'initiation que nous proposons !<br /><br />
Le Club Astro dispose de moyen pour l'observation ainsi que des
documentaires pour apprendre l'astronomie.</br ><br />
Je rappelle que ce club est ouvert à tous les niveaux.<br /><br />
N'hésiter pas à me contacter pour plus de renseignements :<br />
tristan.lebeaume@utbm.fr<br /><br />
Venez nombreux jeudi 24 septembre à 20 heures, devant la salle Jolly Jumper
sous la ME(Maison des Élèves) de Belfort.
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">Club Zik - réouverture des crenaux de repetitions</font></td></tr>
<tr><td>
Bonjour à toi Musicien(e)s de l'UTBM en manque de lieux pour répéter ?
<br /><br />
La salle de répétition de Sévenans est désormais ouverte et il est tant de
réserver vos créneaux de répète, l'idéal est d?en prendre un de deux heures
au semestre pour laissez exprimer votre art.
<br /><br />
Petit rappel, la salle comporte une batterie (baguettes non fournies), des amplis guitares, un ampli basse, un piano, et tout se qu'il faut pour vos
vocalises. Pour finir, il vous faut obligatoirement être cotisant à l'AE
pour utiliser ce matériel.
<br /><br />
Pour réserver un créneau c'est simple, renvoyer un mail à
Club.Zik@utbm.fr avec vos coordonnées et votre choix de réservation.
Certains créneaux étant assez convoités, nous emploierons la technique du 1er servis.
<br /><br />
Toute fois un créneau n'est pas disponible, il s'agit du jeudi soir de 20 à
22h, occupé par le Big Band de l'UTBM. La salle reste disponible du lundi au
samedi de 08h à 23h.
<br /><br />
Musciquement vôtre<br />
Flex pour le club zik
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">Club Zenith - Réunion de rentré</font></td></tr>
<tr><td>
Zénith fait sa rentrée ! Le club de l'AE qui participe au Shell Eco
Marathon, ou comment faire le plus de kilomètre possible avec un litre
d'essence.
<br /><br />
C'est donc de vivre une compétition automobile dont il est question, mais toutes les branches de notre université sont nécessaires pour faire avancer ce prototype !
<br /><br />
Au cours de cette réunion de découverte nous verrons une présentation du
club et son historique, les objectifs à venir pour cette saison et la
voiture! Bien entendu l'équipe sera la pour faire connaissance avec toi.
<br /><br />
La réunion aura lieu Mardi 22 septembre sur le site de Belfort dans le
bâtiment A salle A210 à 19h30.
<br /><br />
Carbonman pour le club Zenith
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">Club danse moderne jazz a besoin de vous !</font></td></tr>
<tr><td>
Bonjour à tous,
<br /><br />
le club danse refait son entrée pour cette année. Venez nous rejoindre pour
danser lors de séances sympathiques entre nous. Envie de participer au gala,
nous faisons une représentation lors de cette soirée inoubliable. Le but est
tout d'abord de passer des moments agréables donc si vous êtes danseuse et
danseur professionnel(le) ou amateur, vous êtes les bienvenu(e)s...
<br /><br />
Nécessitez pas à venir voir comment cela se passe lors des séances le mardi
et le jeudi...
<br /><br />
Pour plus d'informations, rendez vous sur le site de l'AE :<br />
<a href="http://ae.utbm.fr/">ae.utbm.fr</a><br /><br />
A très bientôt<br />
Le club Danse Moderne Jazz
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">Le club de modélisme fait sa rentré !</font></td></tr>
<tr><td>
Le club de modélisme fait sa rentré !
<br /><br />
Alors si tu as toujours rêvé d'être aux commandes d'un hélicoptère ou d'un
avion mais que tu n'en as jamais eu l'occasion, viens découvrir le club
aéro'UT qui met à ta disposition tout le matériel nécessaire . Bien entendu,
les modélistes expérimentés sont eux aussi invités afin de venir partager
connaissances et savoir faire.
<br /><br />
Cette réunion de rentré est l'occasion de présenter l'univers du modélisme
mais surtout de découvrir le fonctionnement du club ainsi que les
hélicoptères et avion mis à votre disposition.
<br /><br />
Rendez vous à 18h en salle Rantanplan ce jeudi 24 septembre !
<br /><br />
Merci,<br />
ZAVA pour le club modélisme
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">UTtoons, ça repart</font></td></tr>
<tr><td>Oyez, oyez,
<br /><br />
UTtoons recommence à égayer vos soirées du lundi de la meilleure manière
possible : Une petite dose régulière d'anciens ou nouveaux dessins animés,
Disney/Pixar ou même japonais,  vous refera plonger en enfance.
<br /><br />
Pour bien débuter ce semestre, venez apprécier Princesse Mononoke et Volt
star, ce lundi 21 Septembre à 20h en A200.
<br /><br />
Princesse Mononoke :
<br />
Au XVe siècle, durant l'ère Muromachi, la forêt japonaise, jadis protégée
par des animaux géants, se dépeuple à cause de l'homme. Un sanglier
transformé en démon dévastateur en sort et attaque le village d'Ashitaka,
futur chef du clan Emishi. Touché par le sanglier qu'il a tué, celui-ci est
forcé de partir à la recherche du dieu Cerf pour lever la malédiction qui
lui gangrène le bras.
<br /><br />
Volt star :<br />
Pour le chien Volt, star d'une série télévisée à succès, chaque journée est
riche d'aventure, de danger et de mystère - du moins devant les caméras. Ce
n'est plus le cas lorsqu'il se retrouve par erreur loin des studios de
Hollywood, à New York... Il va alors entamer la plus grande et la plus
périlleuse de ses aventures - dans le monde réel, cette fois.
<br /><br />
En espérant vous voir nombreux,
<br />
Otine & Ticho
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">La blague !</font></td></tr>
<tr><td>
Un vieil Arabe vit depuis plus de 40 ans à Chicago. Il aimerait bien planter
des pommes de terre dans son jardin mais il est tout seul, vieux et trop
faible. Il envoie alors un e-mail à son fils qui étudie à Paris pour lui
faire part de son problème.<br />
-"Cher Ahmed, je suis très triste car je ne peux pas planter des pommes de
terre dans mon jardin. Je suis sûr que si tu étais ici avec moi tu aurais pu
m'aider à retourner la terre. Je t'aime, ton Père".<br />
Le lendemain, le vieil homme reçoit un e-mail :<br />
-"Cher Père, s'il te plaît, ne touche surtout pas au jardin ! J'y ai caché
la "chose". Moi aussi je t'aime. Ahmed"<br />
A 4 heures du matin arrivent chez le vieillard l'US Army, les Marines, le
FBI,la CIA et même une unité d'élite des Rangers. Ils fouillent tout le
jardin, millimètre par millimètre et repartent déçus car ils n'ont rien
trouvé. Le lendemain, le vieil homme reçoit un nouvel e-mail de la part de
son fils :<br />
- "Cher Père, je suis certain que la terre de tout le jardin est désormais
retournée et que tu peux planter tes pommes de terre.Je ne pouvais pas faire
mieux. Je t'aime, Ahmed"
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">Le mot de la fin</font></td></tr>
<tr><td>à la semaine prochaine !<br />
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
