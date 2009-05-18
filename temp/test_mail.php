<?
$topdir = '../';
require_once($topdir. "include/site.inc.php");
require_once($topdir."include/entities/files.inc.php");
require_once($topdir."include/entities/folder.inc.php");
require_once($topdir.'include/lib/mailer.inc.php');
$site = new site();

$mailer = new mailer('Association des Étudiants <ae@utbm.fr>',
                     'Weekmail du 18 au 24 Mai 2009');
/*$mailer->add_dest(array('etudiants@utbm.fr',
                        'enseignants@utbm.fr',
                        'iatoss@utbm.fr',
                        'aude.petit@utbm.fr'));*/
$mailer->add_dest('ae.info@utbm.fr');
$file = new dfile($site->db);
$file->load_by_id(3957);
$mailer->add_img($file);
$plain = 'Salut les UTbohémiens,

Cette semaine vous avez rendez-vous avec le Festival du Film d\'Un
Jour. A partir de mercredi, 14 équipes de toute la France et
d\'ailleurs se réunissent sur le site de Sévenans et auront 50 heures
pour faire un film. Vous êtes donc conviés à assister à la diffusion
de l\'ensemble de leurs productions ce Samedi 23 Mai, à 20h30 au
Mégarama d\'Audincourt. Cette diffusion sera bien entendu GRATUITE et
sera suivie par une cérémonie de remise des prix.

Donc pensez-y, SAMEDI C\'EST FF1J !!


Sommaire :

  * Troll penché - Murder
  * Le Seven\'Art présente Fast & Furious 4
  * Mat\'Matronch
  * Club astro : observation mercredi soir
  * Promo07 - Père 500



----------------------------------------------------
Troll penché - Murder
----------------------------------------------------

Yop!

Eh oui je viens encore vous embêter pour vous rappeler de vous
inscrire à la Murder organisé par le Troll Penché le dimanche
prochain, c\'est-à-dire le 24 Mai.
La participation est de 5? (repas compris) cependant l\'inscription est
obligatoire auprès de la CAPM ( au 03-81-31-88-88 ).
Je rappelle le principe si des fois vous le savez pas encore (si c\'est
le cas je vous conspue. Energiquement même) : une murder c\'est grosso
modo un cluedo mais en plus cossu puisque en quelque sorte on marche
sur le plateau. Et là le plateau ce n\'est ni plus ni moins qu\'un fort
de la fin du XIVeme siècle.

Par ailleurs, j\'en profite pour signaler qu\'il n\'y aura pas de séance
troll à Sevenans ce jeudi pour cause de long weekend. Mais bien sûr
elles reprendront dès la semaine d\'après.

Garroj, scribe Troll

----------------------------------------------------
Le Seven\'Art présente Fast & Furious 4
----------------------------------------------------

Bonjour,

Cette semaine le Seven\'Art propose Fast & Furious 4 le mercredi 20 mai
en P108 à 20h30.
Un film de Justin Lin avec Vin Diesel, Paul Walker, Jordana Brewster

Synopsis :
Un meurtre oblige Don Toretto, un ancien taulard en cavale, et l\'agent
Brian O\'Conner à revenir à L.A. où leur querelle se rallume. Mais
confrontés à un ennemi commun, ils sont contraints à former une
alliance incertaine s\'ils espèrent parvenir à déjouer ses plans. De
l\'attaque de convoi aux glissades de précision qui les mèneront hors
de leurs propres frontières, les deux hommes trouveront le meilleur
moyen de prendre leur revanche : en poussant les limites de ce qui est
faisable au volant d\'un bolide.

Deux affiches du film seront à gagner par tirage au sort durant la séance.

Cotisants AE : 2,50 euros | Autres : 3,50 euros (Possibilité de payer
par carte AE)

Entrée par le parking du bas.


Cinéphilement,
L\'Equipe Seven\'Art

----------------------------------------------------
Mat\'Matronch
----------------------------------------------------

Mais qui est-il, mais de qui parlez-vous, comment puis-je le contacter ???
Heureusement, Monsieur Mat\'Matronch est là pour éviter au maximum ces
situations?

Hé oui, le Mat\'Matronch est la seule, l\'unique bible indispensable de
tout étudiant consciencieux, en tant qu\'annuaire des étudiants de
l\'école.

Toute la population de l\'UTBM est recensée, le Mat\'Matronch donne
enfin un nom à un visage, le numéro de téléphone du binôme
introuvable, l\'adresse de cette personne que l\'on recherche tant
depuis cette fameuse soirée et tout autre renseignement aussi sérieux
administrativement parlant que fantaisiste.

Mais voila pour que celui ci prenne vie, on a besoin de vous!

En effet, pour que l\'annuaire puisse être éditer:
1°) il faut que vos fiches Mat\'matronch soit complétée et à jour, pour
cela, une seule adresse : http://ae.utbm.fr
2°) et encore plus important il nous faut "des bras", c\'est pour cela
que j\'invite les curieux qui souhaite participer à venir à notre
réunion d\'information mercredi soir à 19h en salle rantanplan. (à coté
du bureau AE de Belfort)

Pour plus d\'info, n\'hésitez à vous rendre à cette adresse
http://ae.utbm.fr/asso.php?id_asso=27 ou alors à nous contacter à
matmatronch@utbm.fr

A bientôt

Gon pour le Mat\'matronch

----------------------------------------------------
Club astro : observation mercredi soir
----------------------------------------------------

La météo pour mercredi soir s\'annonce plutôt bien. Nous vous proposons
donc une soirée d\'observation mercredi puisque ensuite nous seront
tous en weekend.
Rendez-vous 20h30 à la salle d\'activité.

* Date et heure : mercredi 20 mai à 20 heures 30
* Lieu : salle d\'activité (Jolly Jumper) à la ME (Belfort)
* Contact : club.astro@utbm.fr

----------------------------------------------------
Promo07 - Père 500
----------------------------------------------------

Bonjour à tous,

La Promo 07 paye son apéro lors du traditionnel Père 500 qui aura lieu
le Mercredi 20 Mai 2009 à partir de 20h au Foyer! Alors avant de
rentrer chez papa-maman, venez en force pour en profiter !
A 23h sera lancée l\'Édition 2009 du très attendu concours
\'Inter-picole\'! Alors montez votre équipe de promo (5 personnes) et
venez prouver l\'efficacité de votre descente de Kro!

Nous vous attendons nombreux!

La Promo 07

----------------------------------------------------
La blague, offerte et assumée par Foif !
----------------------------------------------------

Le père Noël arrive en Somalie sur son joli traîneau en compagnie de
son meilleur lutin (celui qui fait les plus gros cadeaux ^^).
En voyant les petits Somaliens jouer dehors, il s\'exclame :
- Oh, mais pourquoi ces enfants sont-ils si maigres ?
Alors au lutin de répondre :
- Mais parce qu\'ils ne mangent pas...
- QUOI ? Ils ne mangent pas ? Eh bien pas de cadeaux !!

--
à la semaine prochaine !
A6';
$mailer->set_plain($plain);
$html = '<html>
<body bgcolor="#333333" width="700px">
<table bgcolor="#333333" width="700px">
<tr><td align="center">
<table bgcolor="#ffffff" width="600" border="0" cellspacing="0" cellpadding="0" align="center">
<tr><td width="600" height="157" background="http://ae.utbm.fr/d.php?id_file=3957&action=download"><img src="dfile://3957" /></td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">Introduction</font></td></tr>
<tr><td>Salut les UTbohémiens,<br />
<br />
Cette semaine vous avez rendez-vous avec le Festival du Film d\'Un Jour.
A partir de mercredi, 14 équipes de toute la France et d\'ailleurs se
réunissent sur le site de Sévenans et auront 50 heures pour faire un film.
Vous êtes donc conviés à assister à la diffusion de l\'ensemble de leurs
productions ce Samedi 23 Mai, à 20h30 au Mégarama d\'Audincourt. Cette
diffusion sera bien entendu GRATUITE et sera suivie par une cérémonie de
remise des prix.<br />
<br />
Donc pensez-y, SAMEDI C\'EST FF1J !!<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">Sommaire</font></td></tr>
<tr><td><ul><li>Troll penché - Murder</li>
<li>Le Seven\'Art présente Fast & Furious 4</li>
<li>Mat\'Matronch</li>
<li>Club astro : observation mercredi soir</li>
<li>Promo07 - Père 500</li>
</ul>
</td></tr>
<tr bgcolor="#00BBFF"><td><font color="#ffffff">Troll penché - Murder</font></td></tr>
<tr><td>Yop!<br />
<br />
Eh oui je viens encore vous embêter pour vous rappeler de vous
inscrire à la Murder organisé par le Troll Penché le dimanche
prochain, c\'est-à-dire le 24 Mai.<br />
La participation est de 5? (repas compris) cependant l\'inscription est
obligatoire auprès de la CAPM ( au 03-81-31-88-88 ).<br />
Je rappelle le principe si des fois vous le savez pas encore (si c\'est
le cas je vous conspue. Energiquement même) : une murder c\'est grosso
modo un cluedo mais en plus cossu puisque en quelque sorte on marche
sur le plateau. Et là le plateau ce n\'est ni plus ni moins qu\'un fort
de la fin du XIVeme siècle.<br />
<br />
Par ailleurs, j\'en profite pour signaler qu\'il n\'y aura pas de séance
troll à Sevenans ce jeudi pour cause de long weekend. Mais bien sûr
elles reprendront dès la semaine d\'après.<br />
<br />
Garroj, scribe Troll<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td><font color="#ffffff">Le Seven\'Art présente Fast & Furious 4</font></td></tr>
<tr><td>Bonjour,<br />
<br />
Cette semaine le Seven\'Art propose Fast & Furious 4 le mercredi 20 mai
en P108 à 20h30.<br />
Un film de Justin Lin avec Vin Diesel, Paul Walker, Jordana Brewster<br />
<br />
Synopsis :<br />
Un meurtre oblige Don Toretto, un ancien taulard en cavale, et l\'agent
Brian O\'Conner à revenir à L.A. où leur querelle se rallume. Mais
confrontés à un ennemi commun, ils sont contraints à former une
alliance incertaine s\'ils espèrent parvenir à déjouer ses plans. De
l\'attaque de convoi aux glissades de précision qui les mèneront hors
de leurs propres frontières, les deux hommes trouveront le meilleur
moyen de prendre leur revanche : en poussant les limites de ce qui est
faisable au volant d\'un bolide.<br />
<br />
Deux affiches du film seront à gagner par tirage au sort durant la séance.<br />
<br />
Cotisants AE : 2,50 euros | Autres : 3,50 euros (Possibilité de payer
par carte AE)<br />
<br />
Entrée par le parking du bas.<br />
<br />
Cinéphilement,<br />
L\'Equipe Seven\'Art <br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td><font color="#ffffff">Mat\'Matronch</font></td></tr>
<tr><td>Mais qui est-il, mais de qui parlez-vous, comment puis-je le contacter ???<br />
Heureusement, Monsieur Mat\'Matronch est là pour éviter au maximum ces  <br />
situations?<br />
<br />
Hé oui, le Mat\'Matronch est la seule, l\'unique bible indispensable de
tout étudiant consciencieux, en tant qu\'annuaire des étudiants de
l\'école.<br />
<br />
Toute la population de l\'UTBM est recensée, le Mat\'Matronch donne
enfin un nom à un visage, le numéro de téléphone du binôme
introuvable, l\'adresse de cette personne que l\'on recherche tant
depuis cette fameuse soirée et tout autre renseignement aussi sérieux
administrativement parlant que fantaisiste.<br />
<br />
Mais voila pour que celui ci prenne vie, on a besoin de vous!<br />
<br />
En effet, pour que l\'annuaire puisse être éditer:<br />
1°) il faut que vos fiches Mat\'matronch soit complétée et à jour, pour
cela, une seule adresse : http://ae.utbm.fr<br />
2°) et encore plus important il nous faut "des bras", c\'est pour cela
que j\'invite les curieux qui souhaite participer à venir à notre
réunion d\'information mercredi soir à 19h en salle rantanplan. (à coté
du bureau AE de Belfort)<br />
<br />
Pour plus d\'info, n\'hésitez à vous rendre à cette adresse
http://ae.utbm.fr/asso.php?id_asso=27 ou alors à nous contacter à
matmatronch@utbm.fr<br />
<br />
A bientôt<br />
<br />
Gon pour le Mat\'matronch<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td><font color="#ffffff">Club astro : observation mercredi soir</font></td></tr>
<tr><td>La météo pour mercredi soir s\'annonce plutôt bien. Nous vous proposons
donc une soirée d\'observation mercredi puisque ensuite nous seront
tous en weekend.<br />
Rendez-vous 20h30 à la salle d\'activité.<br />
<ul>
<li>Date et heure : mercredi 20 mai à 20 heures 30</li>
<li>Lieu : salle d\'activité (Jolly Jumper) à la ME (Belfort)</li>
<li>Contact : club.astro@utbm.fr</li>
</ul><br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td><font color="#ffffff">Promo07 - Père 500</font></td></tr>
<tr><td>Bonjour à tous,<br />
<br />
La Promo 07 paye son apéro lors du traditionnel Père 500 qui aura lieu
le Mercredi 20 Mai 2009 à partir de 20h au Foyer! Alors avant de
rentrer chez papa-maman, venez en force pour en profiter !<br />
<br />
A 23h sera lancée l\'Édition 2009 du très attendu concours
\'Inter-picole\'! Alors montez votre équipe de promo (5 personnes) et
venez prouver l\'efficacité de votre descente de Kro!<br />
<br />
Nous vous attendons nombreux!<br />
<br />
La Promo 07<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">La blague, offerte et assumée par Foif !</font></td></tr>
<tr><td>Le père Noël arrive en Somalie sur son joli traîneau en compagnie de
son meilleur lutin (celui qui fait les plus gros cadeaux ^^).<br />
En voyant les petits Somaliens jouer dehors, il s\'exclame :<br />
- Oh, mais pourquoi ces enfants sont-ils si maigres ?<br />
Alors au lutin de répondre :<br />
- Mais parce qu\'ils ne mangent pas...<br />
- QUOI ? Ils ne mangent pas ? Eh bien pas de cadeaux !!<br />&nbsp;</td></tr>
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
