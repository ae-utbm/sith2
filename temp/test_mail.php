<?
//exit();
$topdir = '../';
require_once($topdir. "include/site.inc.php");
require_once($topdir."include/entities/files.inc.php");
require_once($topdir."include/entities/folder.inc.php");
require_once($topdir.'include/lib/mailer.inc.php');
$site = new site();

$mailer = new mailer('Association des Étudiants <ae@utbm.fr>',
                     'Weekmail du 05 au 11 octobre 2009');
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

Avec l'AE les semaines passent mais ne se ressemblent pas ! Juste une petite
question comme ça, vos CV sont-ils prêts ? Non ? Alors au boulot ! Cette
semaine c'est le congrès : deux jours de conférences, deux jours autour de
votre future profession, à ne manquer sous aucun prétexte.

Vous pourrez assister aux conférences sur le site de Sévenans ce mercredi 7
et 8 octobre, vous pourrez aussi rencontrer les 22 entreprises présentes et
participer à des simulations d'entretien.

Vous avez compris le mot d'ordre, cette semaine, ne loupez pas le congrès !


Sommaire :

 * AE - Le Grand Cocktail des 10ans !!!
 * AE - Decade Party
 * Le club babyfoot fait sa rentrée !!!
 * Club Welcome - Soirée Bowling
 * UTtoons - Projections de la semaine
 * Troll Penché - Repas Western
 * Com'ET - Nuit de la Cabine


----------------------------------------------------
AE - Le Grand Cocktail des 10ans !!!
----------------------------------------------------

L'Association des Etudiants de l'UTBM est heureuse de vous inviter au
Cocktail organisé pour célébrer les 10 ans de l'association. Ce cocktail
aura lieu Jeudi 15 octobre à 20h au Foyer de la Maison des Elèves sur le
site de l'UTBM à Belfort (6 Boulevard Anatole France - 90000 BELFORT).

Ce cocktail sera l'occasion de partager un moment convivial avec les
différents partenaires de l'AE, l'administration de l'UTBM ainsi que les
cotisants de l'association.

Tenue correcte exigée
Entrée sur présentation de l'invitation

----------------------------------------------------
AE - Decade Party
----------------------------------------------------

Voilà 10 ans que l'AE existe, 10 ans de dur labeur, mais surtout 10 ans
d'activités, de joie et de souvenirs inoubliables. L'AE a participé à la
formation des ingénieurs que nous sommes et serons, mais aussi à la
rencontre de nos amis. Donc un seul mot à dire : Venez faire la fête le 15
octobre à 22h avec vos amis au Foyer de Belfort !

La Decade party est là pour fêter les 10 ans d'aniv de l'AE dans un décor
clubber.

Tenue correcte exigée
Entrée Gratuite et réservée aux cotisants AE

L'équipe AE

----------------------------------------------------
Le club babyfoot fait sa rentrée !!!
----------------------------------------------------

Salut à tous, le club babyfoot reprend du service autour des deux tables de
babyfoot présentes au foyer !
4 choses à savoir :

lieu : foyer
date : tous les mercredis soirs
horaire : de 20h à 22h
pour qui ? : tout cotisant AE ayant envie de jouer au babyfoot. tous les
niveaux sont acceptés.

Venez nombreux, que l'on puisse tourner et ne pas s'ennuyer à jouer tout le
temps contre les mêmes !

telect pour le baby

----------------------------------------------------
Club Welcome - Soirée Bowling
----------------------------------------------------

Le Club Welcome organise une soirée bowling le mardi 6 Octobre, au bowling
des 4 AS (Belfort). Une partie coûte 3 euros (location des chaussures
comprise) : vous pouvez cotiser auprès des responsables du Club mais aussi
sur e-boutic.

Pour tous ceux qui ne sauraient pas où se trouve le bowling, départ prévu à
la ME à :
                  ** 19h30 devant le foyer **

Pour les autres, rendez-vous à :

                  ** 20h00 au bowling **

Attention à ne pas arriver en retard!!!

A mardi,

Yruana pour Welcome

----------------------------------------------------
UTtoons - Projections de la semaine
----------------------------------------------------

Bonjour à vous,

Nous vous proposons cette semaine de vous divertir avec Monstres contre
Aliens ainsi que Monstres et Compagnie. Rendez-vous-même heure, même lieu :
20h dans l'amphi A200.

Monstres contre Aliens

Le jour de son mariage, la jeune Susan Murphy reçoit sur la tête... une
météorite qui la transforme en un monstre de plus de 20 mètres. L'armée
entre promptement en action, neutralise la géante et l'incarcère dans une
prison top secrète. Rebaptisée Génormica, Susan fait connaissance avec ses
compagnons d'infortune : le brillant Dr Cafard, à tête d'insecte, l'hybride
macho de singe et de poisson appelé Maillon Manquant, l'indestructible et
gélatineux BOB et le gigantesque Insectosaure.

Monstres et Compagnie

Monstropolis est une petite ville peuplée de monstres dont la principale
source d'énergie provient des cris des enfants. Monstres & Cie est la plus
grande usine de traitement de cris de la ville. Grâce au nombre
impressionnant de portes de placards dont dispose l'usine, une équipe de
monstres d'élite pénètre dans le monde des humains pour terrifier durant la
nuit les enfants et récolter leurs hurlements.


Bonne semaine à tous et sans doute à bientôt.

Ticho et Otine

----------------------------------------------------
Troll Penché - Repas Western
----------------------------------------------------

Salut à tous, citoyennes et citoyens des environs d’After Eight !

Un grand repas va être organisé par le bien (ou pas) aimé maire de notre
belle commune : John White.
Il aura lieu au Saloon d’After Eight et toutes et tous pourront y prendre
part pour seulement 4$ !

Comme vous l’aurez sans doute deviné, il s’agit là d’un repas clôturant le
Killer Western et se déroulant au Foyer.
Il n’est pas réservé aux participants donc tous les cotisants AE peuvent
venir.

Pour la modique somme de 4€ (payable sur e-boutic), vous vous régalerez avec
un magnifique (bon d’accord, je pousse un peu ) chili con carne arrosé d’une
sangria.

N’hésitez pas à venir déguisé et ramenez ceux qui trainent près de vous.

Lien e-boutic : http://ae.utbm.fr/e-boutic/?id_produit=574

El Gringo

----------------------------------------------------
Com'ET - Nuit de la Cabine
----------------------------------------------------

La Nuit de la Cabine est la soirée de ce début de semestre à ne surtout pas
rater!! C'est une occasion pour tous les étudiants de l'UTBM de rencontrer
les autres étudiants de l'Aire Urbaine de Belfort-Montbéliard et découvrir
une autre ambiance de soirée!

Rendez vous le 8 octobre de 21 à 4h au parc Airexpos d’Andelnans.

Cette année le chanteur SINGUILA sera présent ainsi que N.J et le groupe
Ventolin. Avec également des danseuses « J.A.C alliance » et DJ ONE, et
surtout pleins animations et de surprises!!

Des navettes seront mises en place.

La place en pré-vente est à 7 euros et vous pouvez l'acheter au Foyer à
Belfort, à la MDE de Sevenans (mercredi et jeudi au bureau AE pour cause de
congrès) ou au bureau AE de Montbéliard.

Pour plus d'informations,
http://ae.utbm.fr/forum2/?id_message=2156708#msg2156708 ou envoyez un mail à
ae@utbm.fr

Jvémla pour Com'Et

----------------------------------------------------
La blague offerte et assumée par Gautier !
----------------------------------------------------

LA BONNE DU CURE.

Une bonne dit au Curé : « Mr le Curé, notre vin de messe est arrivé ! »
Le Curé répond : « Marie, ce n'est pas NOTRE vin de messe puisque tu n'as
pas le droit d'en boire, tu dois donc dire VOTRE vin de messe. » « Compris ?
»
Le lendemain Marie dit au Curé : « Mr le Curé votre bois de chauffage est
arrivé ! »
Le Curé dit :« Marie, tu dois dire NOTRE bois de chauffage puisque nous nous
en servons tous les 2 ! » « As-tu compris cette fois ? »
« Oui», dit Marie.
« Est-ce tout pour aujourd'hui Marie ? »
« Non », répond la Bonne,« Mr le Curé, VOTRE braguette est ouverte, et NOTRE
pénis est sorti !»

* Vous pouvez vous aussi fustiger Gautier sur : gautier.risch@utbm.fr *

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
<tr><td width="601"><img src="http://ae.utbm.fr/d.php?id_file=4523&action=download" /></td></tr>
<tr bgcolor="#000000"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Introduction</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Salut les UTbohémiens,<br />
<br />
Avec l'AE les semaines passent mais ne se ressemblent pas ! Juste une petite
question comme ça, vos CV sont-ils prêts ? Non ? Alors au boulot ! Cette
semaine c'est le congrès : deux jours de conférences, deux jours autour de
votre future profession, à ne manquer sous aucun prétexte.
<br /><br />
Vous pourrez assister aux conférences sur le site de Sévenans ce mercredi 7
et 8 octobre, vous pourrez aussi rencontrer les 22 entreprises présentes et
participer à des simulations d'entretien.
<br /><br />
Vous avez compris le mot d'ordre, cette semaine, ne loupez pas le congrès !
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Sommaire</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px"><ul>
<li>AE - Le Grand Cocktail des 10ans !!!</li>
<li>AE - Decade Party</li>
<li>Le club babyfoot fait sa rentrée !!!</li>
<li>Club Welcome - Soirée Bowling</li>
<li>UTtoons - Projections de la semaine</li>
<li>Troll Penché - Repas Western</li>
<li>Com'ET - Nuit de la Cabine</li>
</ul>
</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">AE - Le Grand Cocktail des 10ans !!!</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">L'Association des Etudiants de l'UTBM est heureuse de vous inviter au
Cocktail organisé pour célébrer les 10 ans de l'association. Ce cocktail
aura lieu Jeudi 15 octobre à 20h au Foyer de la Maison des Elèves sur le
site de l'UTBM à Belfort (6 Boulevard Anatole France - 90000 BELFORT).
<br /><br />
Ce cocktail sera l'occasion de partager un moment convivial avec les
différents partenaires de l'AE, l'administration de l'UTBM ainsi que les
cotisants de l'association.
<br /><br />
Tenue correcte exigée<br />
Entrée sur présentation de l'invitation
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">AE - Decade Party</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Voilà 10 ans que l'AE existe, 10 ans de dur labeur, mais surtout 10 ans
d'activités, de joie et de souvenirs inoubliables. L'AE a participé à la
formation des ingénieurs que nous sommes et serons, mais aussi à la
rencontre de nos amis. Donc un seul mot à dire : Venez faire la fête le 15
octobre à 22h avec vos amis au Foyer de Belfort !
<br /><br />
La Decade party est là pour fêter les 10 ans d'aniv de l'AE dans un décor
clubber.
<br /><br />
Tenue correcte exigée<br />
Entrée Gratuite et réservée aux cotisants AE
<br /><br />
L'équipe AE
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Le club babyfoot fait sa rentrée !!!</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Salut à tous, le club babyfoot reprend du service autour des deux tables de
babyfoot présentes au foyer !
4 choses à savoir :
<br /><br />
lieu : foyer<br />
date : tous les mercredis soirs<br />
horaire : de 20h à 22h<br />
pour qui ? : tout cotisant AE ayant envie de jouer au babyfoot. tous les
niveaux sont acceptés.
<br /><br />
Venez nombreux, que l'on puisse tourner et ne pas s'ennuyer à jouer tout le
temps contre les mêmes !
<br /><br />
telect pour le baby
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Club Welcome - Soirée Bowling</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Le Club Welcome organise une soirée bowling le mardi 6 Octobre, au bowling
des 4 AS (Belfort). Une partie coûte 3 euros (location des chaussures
comprise) : vous pouvez cotiser auprès des responsables du Club mais aussi
sur e-boutic.
<br /><br />
Pour tous ceux qui ne sauraient pas où se trouve le bowling, départ prévu à
la ME à :
<p align="center"><b>19h30 devant le foyer</b></p>
Pour les autres, rendez-vous à :
<p align="center"><b>120h00 au bowling</b></p>

Attention à ne pas arriver en retard!!!
<br /><br />
A mardi,<br />
Yruana pour Welcome
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">UTtoons - Projections de la semaine</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Bonjour à vous,
<br /><br />
Nous vous proposons cette semaine de vous divertir avec Monstres contre
Aliens ainsi que Monstres et Compagnie. Rendez-vous-même heure, même lieu :
20h dans l'amphi A200.
<br /><br />
Monstres contre Aliens :
<br />
Le jour de son mariage, la jeune Susan Murphy reçoit sur la tête... une
météorite qui la transforme en un monstre de plus de 20 mètres. L'armée
entre promptement en action, neutralise la géante et l'incarcère dans une
prison top secrète. Rebaptisée Génormica, Susan fait connaissance avec ses
compagnons d'infortune : le brillant Dr Cafard, à tête d'insecte, l'hybride
macho de singe et de poisson appelé Maillon Manquant, l'indestructible et
gélatineux BOB et le gigantesque Insectosaure.
<br /><br />
Monstres et Compagnie :
<br />
Monstropolis est une petite ville peuplée de monstres dont la principale
source d'énergie provient des cris des enfants. Monstres & Cie est la plus
grande usine de traitement de cris de la ville. Grâce au nombre
impressionnant de portes de placards dont dispose l'usine, une équipe de
monstres d'élite pénètre dans le monde des humains pour terrifier durant la
nuit les enfants et récolter leurs hurlements.
<br /><br />
Bonne semaine à tous et sans doute à bientôt.
<br /><br />
Ticho et Otine
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Troll Penché - Repas Western</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Salut à tous, citoyennes et citoyens des environs d’After Eight !
<br /><br />
Un grand repas va être organisé par le bien (ou pas) aimé maire de notre
belle commune : John White.<br />
Il aura lieu au Saloon d’After Eight et toutes et tous pourront y prendre
part pour seulement 4$ !
<br /><br />
Comme vous l’aurez sans doute deviné, il s’agit là d’un repas clôturant le
Killer Western et se déroulant au Foyer.
Il n’est pas réservé aux participants donc tous les cotisants AE peuvent
venir.
<br /><br />
Pour la modique somme de 4€ (payable sur e-boutic), vous vous régalerez avec
un magnifique (bon d’accord, je pousse un peu ) chili con carne arrosé d’une
sangria.
<br /><br />
N’hésitez pas à venir déguisé et ramenez ceux qui trainent près de vous.
<br /><br />
Lien e-boutic : http://ae.utbm.fr/e-boutic/?id_produit=574
<br /><br />
El Gringo
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Com'ET - Nuit de la Cabine</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">La Nuit de la Cabine est la soirée de ce début de semestre à ne surtout pas
rater!! C'est une occasion pour tous les étudiants de l'UTBM de rencontrer
les autres étudiants de l'Aire Urbaine de Belfort-Montbéliard et découvrir
une autre ambiance de soirée!
<br /><br />
Rendez vous le 8 octobre de 21 à 4h au parc Airexpos d’Andelnans.
<br /><br />
Cette année le chanteur SINGUILA sera présent ainsi que N.J et le groupe
Ventolin. Avec également des danseuses « J.A.C alliance » et DJ ONE, et
surtout pleins animations et de surprises!!
<br /><br />
Des navettes seront mises en place.
<br /><br />
La place en pré-vente est à 7 euros et vous pouvez l'acheter au Foyer à
Belfort, à la MDE de Sevenans (mercredi et jeudi au bureau AE pour cause de
congrès) ou au bureau AE de Montbéliard.
<br /><br />
Plus d'informations <a href="http://ae.utbm.fr/forum2/?id_message=2156708#msg2156708">ici</a>
ou envoyez un mail à <a href="mailto:ae@utbm.fr">ae@utbm.fr</a>
<br /><br />
Jvémla pour Com'Et
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">La blague offerte et assumée par Gautier !</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">LA BONNE DU CURE.
<br /><br />
Une bonne dit au Curé : « Mr le Curé, notre vin de messe est arrivé ! »<br />
Le Curé répond : « Marie, ce n'est pas NOTRE vin de messe puisque tu n'as
pas le droit d'en boire, tu dois donc dire VOTRE vin de messe. » « Compris ?
»<br />
Le lendemain Marie dit au Curé : « Mr le Curé votre bois de chauffage est
arrivé ! »<br />
Le Curé dit :« Marie, tu dois dire NOTRE bois de chauffage puisque nous nous
en servons tous les 2 ! » « As-tu compris cette fois ? »<br />
« Oui», dit Marie.<br />
« Est-ce tout pour aujourd'hui Marie ? »<br />
« Non », répond la Bonne,« Mr le Curé, VOTRE braguette est ouverte, et NOTRE
pénis est sorti !»<br />
<br />
<b>Vous pouvez vous aussi fustiger <a href="mailto:gautier.risch@utbm.fr">Gautier</a></b>
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
