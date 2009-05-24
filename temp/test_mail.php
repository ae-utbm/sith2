<?
//exit();
$topdir = '../';
require_once($topdir. "include/site.inc.php");
require_once($topdir."include/entities/files.inc.php");
require_once($topdir."include/entities/folder.inc.php");
require_once($topdir.'include/lib/mailer.inc.php');
$site = new site();

$mailer = new mailer('Association des Étudiants <ae@utbm.fr>',
                     'Weekmail du 25 au 31 Mai 2009');
//$mailer->add_dest(array('etudiants@utbm.fr',
//                        'enseignants@utbm.fr',
//                        'iatoss@utbm.fr',
//                        'aude.petit@utbm.fr'));
$mailer->add_dest("simon.lopez@utbm.fr");
//$file = new dfile($site->db);
//$file->load_by_id(3957);
//$mailer->add_img($file);
$plain = 'Salut les UTbohémiens,

La grande fête approche ! Le soleil brille, les oiseaux chantent et le
groupes répètent, le FIMU c\'est ce week-end !! N\'hésitez pas à venir
profiter de ce grand festival 100% gratuit offert par la ville de
Belfort et les étudiants de l\'aire urbaine. Pour plus d\'informations :
http://www.fimu.com


Sommaire :

  * Le FIMU, c\'est ce week-end !!
  * Reflex - Formation photo
  * Club Welcome: Soirée ASIE Mercredi 27 à 20h au Foyer de Belfort !
  * Semaine des Arts
  * La Bohème
  * Le Seven\'Art présente Dans la brume électrique
  * Le Bigband de l\'UTBM au FIMU 2009 !!
  * Club astro : observation/photo de la Lune ou traitement d\'images


----------------------------------------------------
Le FIMU, c\'est ce week-end !!
----------------------------------------------------

Chaque année depuis 23 ans, durant le week-end de la Pentecôte, la
ville de Belfort et les étudiants de l\'aire urbaine organisent le
Festival International de Musique Universitaire.
Participez au FIMU en tant que bénévoles.

Le FIMU concentre sur trois jours les aspirations de près de 2 500
musiciens et choristes amateurs à se produire et à faire connaître
leur pratique. Ils sont représentants de leur école ou bande de
copains, "fous de musique" qui se retrouvent dans le festival pour
leur plaisir, en véhiculant l\'esprit de fête et d\'indépendance propre
au monde étudiant.

133 formations ou orchestres issus d\'universités ou de conservatoires
venant de 37 pays ont été sélectionnés pour l\'édition 2009.

Le FIMU, c\'est l\'occasion de la rencontre entre les formations et le
public toujours plus nombreux. En 2008, celui-ci était évalué à 80 000
visiteurs.
La Vieille Ville de Belfort sert de cadre privilégié à ce
rassemblement, à travers quatorze scènes, en salle ou en plein air.

L\'accès à l\'ensemble des concerts est totalement gratuit, cela permet
à un public familial, mélomane ou curieux, de découvrir et d\'apprécier
dans la convivialité un programme de musique "à la carte". Accessibilité

Le Festival est ouvert à toute formation musicale française ou
étrangère composée majoritairement d\'étudiants ou d\'élèves des
conservatoires et écoles de musique, ainsi qu\'aux groupes musicaux
divers pratiquant la musique en amateurs. Le Festival accueille tous
les genres musicaux: musique classique, musiques nouvelles, jazz,
musiques actuelles, musique du monde, chanson...

Pour plus d\'informations, rendez-vous sur : http://www.fimu.com

----------------------------------------------------
Reflex - Formation photo
----------------------------------------------------

Bonjour à tous,

Reflex, votre club photo préféré vous propose ce jeudi 28 mai, de 18h
à 20h, non pas une mais deux formations à la photographie. La première
reprendra les bases techniques de la photographie, pour ceux qui sont
intéressés par le pourquoi du comment, ceux qui possèdent un appareil
photo depuis peu et veulent apprendre à mieux le maitriser, ou encore,
ceux qui en ont un depuis longtemps et à qui la mémoire fait défaut...
La seconde couvrira les techniques de cadrage et composition, comment
rendre vos images plus intéressantes, dans la théorie du moins.

La formation aura lieu à Belfort (bâtiment A, salle à confirmer, voir
sur le site de l\'AE très bientôt), de 18h à 20h

à jeudi donc

Zaps et Senic pour le club Reflex

contact : reflex@utbm.fr ou loic.geslin@utbm.fr

----------------------------------------------------
Club Welcome: Soirée ASIE Mercredi 27 à 20h au Foyer de Belfort !
----------------------------------------------------

Et oui le semestre se termine...
Notre dernière grosse soirée mettra à l\'honneur l\'Asie et ses cultures.
Dégustations, boissons, Karaoké, DDR et musique seront au rendez-vous,
ainsi que VOUS TOUS !

Venez nombreux mercredi soir dès 20h au Foyer de la Maison des
Étudiants de Belfort. Entrée gratuite !

Bien entendu nous avons toujours besoin de monde pour préparer la
soirée donc n\'hésitez pas si ça vous intéresse !

Nous recherchons d\'ailleurs toujours des étudiants motivés pour
reprendre le flambeau à l\'automne prochain... Plus on est de fou plus
on rit et moins il y a de travail par personne!

une question, un doute, une envie? -> welcome@utbm.fr

Pour le Club Welcome

----------------------------------------------------
Semaine des Arts
----------------------------------------------------

La Semaine des Arts s\'est achevée il y a une petite semaine déjà, ce
fût un réel plaisir pour toute l\'équipe de vous offrir ce programme
artistique et culturel fourni en découverte. C\'est aussi un réel
succès au vue de la fréquentation des différents événements que nous
vous proposions.
Merci à vous tous d\'avoir participé à cette Réédition de la Semaine
des Arts. Nous vous donnons rendez-vous l\'année prochaine pour une
nouvelle édition.

Pour continuer sur notre lancée et encore apporter découverte et
expériences originales aux étudiants nous avons besoin de former une
équipe conséquente, alors n\'hésitez plus, faites-vous connaître auprès
de nous ;) On attend toutes vos idées et suggestions.

Enfin je rappelle que les magnifiques t-shirts de la Semaine des Arts
sont en vente. c\'est par ici : http://ae.utbm.fr/forum2/?id_sujet=9188

Artistiquement votre,
Vid au nom de l\'équipe SdA

----------------------------------------------------
La Bohème
----------------------------------------------------

Elle vous manque votre BiMeBo, dites-le qu\'elle vous manque, c\'est
normal avec tout ce qui s\'est enchaîne (Semaine des Arts, FF1J et
bientôt FIMU) l\'équipe n\'avait pas vraiment le temps de vous sortir
quelque chose. Mais le mal va être réparé :)
Nous vous donnons rendez-vous jeudi 4 juin prochain pour lire la
prochaine BiMeBo. Cela veut aussi dire que les articles, jeux,
blagues, citations sont à rendre pour le lundi 1er juin dans la soirée.
Cela serait bien d\'y retrouver des retours de les 3 événements passés
(SdA, FF1J, FIMU). Et évidemment des articles sur l\'actualité, le
cinéma, et tout plein d\'autres choses.
Je vous fais confiance

Bohèmement votre,
Vid

----------------------------------------------------
Le Seven\'Art présente Dans la brume électrique
----------------------------------------------------

Bonjour,

Cette semaine le Seven\'Art propose Dans la brume électrique le
mercredi 27 mai en P108 à 20h30. Un film de Bertrand Tavernier avec
Tommy Lee Jones, John Goodman, Peter Sarsgaard

Synopsis :
New Iberia, Louisiane. Le détective Dave Robicheaux est sur les traces
d\'un tueur en série qui s\'attaque à de très jeunes femmes. De retour
chez lui après une investigation sur la scène d\'un nouveau crime
infâme, Dave fait la rencontre d\'Elrod Sykes. La grande star
hollywoodienne est venue en Louisiane tourner un film, produit avec le
soutien de la fine fleur du crime local, Baby Feet Balboni. Elrod
raconte à Dave qu\'il a vu, gisant dans un marais, le corps décomposé
d\'un homme noir enchaîné. Cette découverte fait rapidement resurgir
des souvenirs du passé de Dave. Mais à mesure que Dave se rapproche du
meurtrier, le meurtrier se rapproche de la famille de Dave?


Deux affiches du film seront à gagner par tirage au sort durant la séance.

Cotisants AE : 2,50 euros | Autres : 3,50 euros (Possibilité de payer
par carte AE)

Entrée par le parking du bas.


Cinéphilement,
L\'Equipe Seven\'Art

----------------------------------------------------
Le Bigband de l\'UTBM au FIMU 2009 !!
----------------------------------------------------

Le Bigband de l\'UTBM est ravi de vous apprendre qu\'il participera au
FIMU 2009 !!

*Date*
dimanche 31 Mai à 14h

*Lieu*
Scène du Lion !

*Programme*
Une set-list spéciale a été préparée !!!
Du jazz, du funk, du rock, du boogie, de la bossa...
Des reprises de grands standards, des compositions
A ne rater sous aucun prétexte

A dimanche !
Gaël pour le Bigband

----------------------------------------------------
Club astro : observation/photo de la Lune ou traitement d\'images
----------------------------------------------------

En fonction de l\'humeur des participants et de la météo on pourra,
soit faire l\'observation/photographie de la Lune, soit faire le
traitement des images faites durant le semestre.

* Date et heure : jeudi 28 mai à 20h30
* Lieu : salle activités (Jolly Jumper) à la ME (Belfort)
* Contact : club.astro@utbm.fr

----------------------------------------------------
La blague, offerte et assumée par notre éléphantesque Mage !
----------------------------------------------------

Salon de l\'auto : Comment reconnaître les nationalités des visiteurs
du Mondial de l\'Automobile ?

- L\'Allemand examine le moteur
- L\'Anglais examine les cuirs
- Le Grec examine l\'échappement
- L\'Italien examine le Klaxon
- Le Portugais examine la peinture
- L\'Americain examine la taille
- Le Suisse examine le coffre
- Le Chinois examine tout
- Le Belge examine rien
- Le Français examine la vendeuse

--
à la semaine prochaine !
A6';
//$mailer->set_plain($plain);
$html = '<html>
<body bgcolor="#333333" width="700px">
<table bgcolor="#333333" width="700px">
<tr><td align="center">
<table bgcolor="#ffffff" width="600" border="0" cellspacing="0" cellpadding="0" align="center">
<tr><td width="600" height="241"><img src="http://ae.utbm.fr/d.php?id_file=3970&action=download" /></td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">Introduction</font></td></tr>
<tr><td>Salut les UTbohémiens,<br />
<br />
La grande fête approche ! Le soleil brille, les oiseaux chantent et le
groupes répètent, le FIMU c\'est ce week-end !! N\'hésitez pas à venir
profiter de ce grand festival 100% gratuit offert par la ville de
Belfort et les étudiants de l\'aire urbaine. Pour plus d\'informations :
<a href="http://fimu.com" target="_blank">http://www.fimu.com</a>
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">Sommaire</font></td></tr>
<tr><td><ul><li>Le FIMU, c\'est ce week-end !!</li>
<li>Reflex - Formation photo</li>
<li>Club Welcome: Soirée ASIE Mercredi 27 à 20h au Foyer de Belfort !</li>
<li>Semaine des Arts</li>
<li>La Bohème</li>
<li>Le Seven\'Art présente Dans la brume électrique</li>
<li>Le Bigband de l\'UTBM au FIMU 2009 !!</li>
<li>Club astro : observation/photo de la Lune ou traitement d\'images</li>
</ul>
</td></tr>
<tr bgcolor="#00BBFF"><td><font color="#ffffff">Le FIMU, c\'est ce week-end !!</font></td></tr>
<tr><td>Chaque année depuis 23 ans, durant le week-end de la Pentecôte, la
ville de Belfort et les étudiants de l\'aire urbaine organisent le
Festival International de Musique Universitaire.
Participez au FIMU en tant que bénévoles.<br />
<br />
Le FIMU concentre sur trois jours les aspirations de près de 2 500
musiciens et choristes amateurs à se produire et à faire connaître
leur pratique. Ils sont représentants de leur école ou bande de
copains, "fous de musique" qui se retrouvent dans le festival pour
leur plaisir, en véhiculant l\'esprit de fête et d\'indépendance propre
au monde étudiant.<br />
<br />
133 formations ou orchestres issus d\'universités ou de conservatoires
venant de 37 pays ont été sélectionnés pour l\'édition 2009.<br />
<br />
Le FIMU, c\'est l\'occasion de la rencontre entre les formations et le
public toujours plus nombreux. En 2008, celui-ci était évalué à 80 000
visiteurs.<br />
La Vieille Ville de Belfort sert de cadre privilégié à ce
rassemblement, à travers quatorze scènes, en salle ou en plein air.<br />
<br />
L\'accès à l\'ensemble des concerts est totalement gratuit, cela permet
à un public familial, mélomane ou curieux, de découvrir et d\'apprécier
dans la convivialité un programme de musique "à la carte".<br />
<br />
Le Festival est ouvert à toute formation musicale française ou
étrangère composée majoritairement d\'étudiants ou d\'élèves des
conservatoires et écoles de musique, ainsi qu\'aux groupes musicaux
divers pratiquant la musique en amateurs. Le Festival accueille tous
les genres musicaux: musique classique, musiques nouvelles, jazz,
musiques actuelles, musique du monde, chanson...<br />
<br />
Pour plus d\'informations, rendez-vous sur :
<a href="http://www.fimu.com" target="_blank">http://www.fimu.com</a>
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td><font color="#ffffff">Reflex - Formation photo</font></td></tr>
<tr><td>Bonjour à tous,<br />
<br />
Reflex, votre club photo préféré vous propose ce jeudi 28 mai, de 18h
à 20h, non pas une mais deux formations à la photographie. La première
reprendra les bases techniques de la photographie, pour ceux qui sont
intéressés par le pourquoi du comment, ceux qui possèdent un appareil
photo depuis peu et veulent apprendre à mieux le maitriser, ou encore,
ceux qui en ont un depuis longtemps et à qui la mémoire fait défaut...
La seconde couvrira les techniques de cadrage et composition, comment
rendre vos images plus intéressantes, dans la théorie du moins.<br />
<br />
La formation aura lieu à Belfort (bâtiment A, salle à confirmer, voir
sur le site de l\'AE très bientôt), de 18h à 20h<br />
<br />
à jeudi donc<br />
<br />
Zaps et Senic pour le club Reflex<br />
<br />
contact : reflex@utbm.fr ou loic.geslin@utbm.fr
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td><font color="#ffffff">Club Welcome: Soirée ASIE Mercredi 27 à 20h au Foyer de Belfort !</font></td></tr>
<tr><td>Et oui le semestre se termine...<br />
Notre dernière grosse soirée mettra à l\'honneur l\'Asie et ses cultures.
Dégustations, boissons, Karaoké, DDR et musique seront au rendez-vous,
ainsi que VOUS TOUS !<br />
<br />
Venez nombreux mercredi soir dès 20h au Foyer de la Maison des
Étudiants de Belfort. Entrée gratuite !<br />
<br />
Bien entendu nous avons toujours besoin de monde pour préparer la
soirée donc n\'hésitez pas si ça vous intéresse !<br />
<br />
Nous recherchons d\'ailleurs toujours des étudiants motivés pour
reprendre le flambeau à l\'automne prochain... Plus on est de fou plus
on rit et moins il y a de travail par personne!<br />
<br />
une question, un doute, une envie? -> welcome@utbm.fr<br />
<br />
Pour le Club Welcome
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td><font color="#ffffff">Semaine des Arts</font></td></tr>
La Semaine des Arts s\'est achevée il y a une petite semaine déjà, ce
fût un réel plaisir pour toute l\'équipe de vous offrir ce programme
artistique et culturel fourni en découverte. C\'est aussi un réel
succès au vue de la fréquentation des différents événements que nous
vous proposions.<br />
Merci à vous tous d\'avoir participé à cette Réédition de la Semaine
des Arts. Nous vous donnons rendez-vous l\'année prochaine pour une
nouvelle édition.<br />
<br />
Pour continuer sur notre lancée et encore apporter découverte et
expériences originales aux étudiants nous avons besoin de former une
équipe conséquente, alors n\'hésitez plus, faites-vous connaître auprès
de nous ;) On attend toutes vos idées et suggestions.<br />
<br />
Enfin je rappelle que les magnifiques t-shirts de la Semaine des Arts
sont en vente. c\'est par
<a href="http://ae.utbm.fr/forum2/?id_sujet=9188" target="_blank">ici</a><br />
<br />
Artistiquement votre,<br />
Vid au nom de l\'équipe SdA
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td><font color="#ffffff">La Bohème</font></td></tr>
<tr><td>Elle vous manque votre BiMeBo, dites-le qu\'elle vous manque, c\'est
normal avec tout ce qui s\'est enchaîne (Semaine des Arts, FF1J et
bientôt FIMU) l\'équipe n\'avait pas vraiment le temps de vous sortir
quelque chose. Mais le mal va être réparé :)<br />
Nous vous donnons rendez-vous jeudi 4 juin prochain pour lire la
prochaine BiMeBo. Cela veut aussi dire que les articles, jeux,
blagues, citations sont à rendre pour le lundi 1er juin dans la soirée.
Cela serait bien d\'y retrouver des retours de les 3 événements passés
(SdA, FF1J, FIMU). Et évidemment des articles sur l\'actualité, le
cinéma, et tout plein d\'autres choses.<br />
Je vous fais confiance<br />
<br />
Bohèmement votre,<br />
Vid
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td><font color="#ffffff">Le Seven\'Art présente Dans la brume électrique</font></td></tr>
<tr><td>Bonjour,<br /<
<br />
Cette semaine le Seven\'Art propose Dans la brume électrique le
mercredi 27 mai en P108 à 20h30. Un film de Bertrand Tavernier avec
Tommy Lee Jones, John Goodman, Peter Sarsgaard<br />
<br />
Synopsis :<br />
New Iberia, Louisiane. Le détective Dave Robicheaux est sur les traces
d\'un tueur en série qui s\'attaque à de très jeunes femmes. De retour
chez lui après une investigation sur la scène d\'un nouveau crime
infâme, Dave fait la rencontre d\'Elrod Sykes. La grande star
hollywoodienne est venue en Louisiane tourner un film, produit avec le
soutien de la fine fleur du crime local, Baby Feet Balboni. Elrod
raconte à Dave qu\'il a vu, gisant dans un marais, le corps décomposé
d\'un homme noir enchaîné. Cette découverte fait rapidement resurgir
des souvenirs du passé de Dave. Mais à mesure que Dave se rapproche du
meurtrier, le meurtrier se rapproche de la famille de Dave?<br />
<br />
Deux affiches du film seront à gagner par tirage au sort durant la séance.<br />
<br />
Cotisants AE : 2,50 euros | Autres : 3,50 euros (Possibilité de payer
par carte AE)<br />
<br />
Entrée par le parking du bas.<br />
<br />
Cinéphilement,<br /
L\'Equipe Seven\'Art
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td><font color="#ffffff">Le Bigband de l\'UTBM au FIMU 2009 !!</font></td></tr>
<tr><td>Le Bigband de l\'UTBM est ravi de vous apprendre qu\'il participera au
FIMU 2009 !!<br />
<br />
<b>Date</b> : dimanche 31 Mai à 14h<br />
<b>Lieu</b> : Scène du Lion !<br />
<b>Programme</b> :<br />
Une set-list spéciale a été préparée !!!<br />
Du jazz, du funk, du rock, du boogie, de la bossa...<br />
Des reprises de grands standards, des compositions<br />
A ne rater sous aucun prétexte<br />
<br />
A dimanche !<br />
Gaël pour le Bigband
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td><font color="#ffffff">Club astro : observation/photo de la Lune ou traitement d\'images</font></td></tr>
<tr><td>En fonction de l\'humeur des participants et de la météo on pourra,
soit faire l\'observation/photographie de la Lune, soit faire le
traitement des images faites durant le semestre.<br />
<br />
<ul><li>Date et heure : jeudi 28 mai à 20h30</li>
<li>Lieu : salle activités (Jolly Jumper) à la ME (Belfort)</li>
<li>Contact : club.astro@utbm.fr</li></ul>
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">La blague, offerte et assumée par notre éléphantesque Mage !</font></td></tr>
<tr><td>Salon de l\'auto : Comment reconnaître les nationalités des visiteurs
du Mondial de l\'Automobile ?<br />
<br />
- L\'Allemand examine le moteur<br />
- L\'Anglais examine les cuirs<br />
- Le Grec examine l\'échappement<br />
- L\'Italien examine le Klaxon<br />
- Le Portugais examine la peinture<br />
- L\'Americain examine la taille<br />
- Le Suisse examine le coffre<br />
- Le Chinois examine tout<br />
- Le Belge examine rien<br />
- Le Français examine la vendeuse<br />
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">Le mot de la fin</font></td></tr>
<tr><td>à la semaine prochaine !<br />
A6<br />
<br />
Pour toute réclamation sur le weekmail HTML => ayolo</td></tr>
</table><br />
</td></tr></table>
</body>
</html>';
$mailer->set_html($html);
$mailer->send();

?>
