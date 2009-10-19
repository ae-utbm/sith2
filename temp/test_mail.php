<?
exit();
$topdir = '../';
require_once($topdir. "include/site.inc.php");
require_once($topdir."include/entities/files.inc.php");
require_once($topdir."include/entities/folder.inc.php");
require_once($topdir.'include/lib/mailer.inc.php');
$site = new site();

$mailer = new mailer('Association des Étudiants <ae@utbm.fr>',
                     'Weekmail du 19 au 25 octobre 2009');
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

On attaque la dernière semaine avant les vacances ! Petite pensée pour tous
les TC qui passent leurs médians cette semaine. Les activités continuent,
tenez vous au chaud, l'hiver belfortain arrive à grands pas !

Note pour les responsables de clubs :
Le weekmail évolue, pour proposer des articles dans le weekmail, il faudra
désormais passer par la section outils de vos clubs accessible directement
depuis le menu "Gestion assos/clubs" sur le site AE. Le lien sera activé
dès mercredi soir.

Sommaire :

 * L'AE évolue!
 * Soirée étudiante gratuite à Montbéliard
 * Electrochoc - à vous les platines !
 * AE - Formation Com'
 * Le Seven'Art présente Un Prophète
 * UTtoons - programme de la semaine
 * Club Astro - Pas de séance
 * L'Arrêt Dessin participe à l'exposition "alliages"
 * Troll Penché - séances de jeu d'échecs


----------------------------------------------------
L'AE évolue!
----------------------------------------------------

A toi cher cotisant AE,

La rentrée de septembre a été une nouvelle étape dans ma vie en tant
qu'Association Etudiante de l'UTBM, plus connue sous le nom d'AE!
Tout d'abord, bon nombre de nouveaux étudiants sont venus me rejoindre et
sont donc devenus COTISANT AE!!!
Ensuite, fin septembre, les clubs ont présenté leur projet et budget lors
des Commissions de pôle (http://ae.utbm.fr/d.php?id_folder=1540) et ils ont
été voté lors du Conseil d'administration (
http://ae.utbm.fr/d.php?id_file=4625&action=download). De plus, j'ai eu
droit à une mise à jour de mes statuts (
http://ae.utbm.fr/d.php?id_file=2343&action=download) et de mon règlement
intérieur (http://ae.utbm.fr/d.php?id_file=2344&action=download)!
Puis, j'ai eu droit à ma fête d'anniversaire la semaine dernière où beaucoup
d'entre vous sont venus!! Merci!!
Enfin, je rappelle que toutes les semaines, l'équipe qui s'active autour de
moi ce réunit pour parler de mon fonctionnement et qu'on peut retrouver le
compte-rendu (sérieux ou pas) de cette réunion sur :
http://ae.utbm.fr/forum2/?id_sujet=3585&spage=11 !

L'AE qui t'aime!

----------------------------------------------------
Soirée étudiante gratuite à Montbéliard
----------------------------------------------------

En l'honneur des étudiants, la Communauté d'Agglomération du Pays de
Montbéliard organise une nocturne le 22 octobre 09 à Montbéliard! L'occasion
de se rencontrer autour de jeux vidéos, musées, et d'une soirée!

Programme:

- Le tournoi de jeux vidéos aura lieu aux établissements publics numériques,
ce sont des jeux vidéos sur console Wii tels que Guitar Heroe. Des lots
seront à gagner (chèques cadeaux).

- L'accueil des étudiants se fera à 20h00 à la Roselière de Montbéliard, le
bracelet pass leur sera remis sur présentation de leur carte étudiante. De
20 heures à 23 heures, ils pourront visiter gratuitement les musées de
Montbéliard.
- La soirée festive commencera à partir de 23H00, uniquement les personnes
porteuses du bracelet pass pourront y assister. L'AE et le BDF gère les
boissons, la lumière et le son.

- Une tombola sera organisée, plusieurs lots seront mis en jeu : 2 places
pour le concert de TRYO à l'Axone, des places de concerts pour la Mals,
L'Allan, les 4 saisons du Palot, des chèques cadeaux, des places de foot,
des cartes avantages jeunes...

Venez nombreux!

L'équipe AE

----------------------------------------------------
Electrochoc - à vous les platines !
----------------------------------------------------

Salut à tous !

Comme vous l'avez peut-être entendu au détour d'une conversation, la soirée
Electrochoc, grande soirée autour des musiques électroniques fait son grand
retour ce semestre au début du mois de décembre.

Petite nouveauté, ce semestre, l'équipe d'organisation vous propose de mixer
sur un créneau de 30 minutes durant la soirée.

Si tu es donc DJ averti, membre ou pas du club mix, libre à toi de nous
transmettre une maquette mixée par tes soins d'une durée maximale de 30
minutes. Attention cependant, cette maquette devra avoir été mixée sur
platines CDs et transmise à club.mix@utbm.fr avant le 08 novembre 2009.

A vous de jouer !
A6, pour Electrochoc

----------------------------------------------------
AE - Formation Com'
----------------------------------------------------

En temps que responsable com' de l'AE je propose aux cotisants de l'AE une
formation Com'/PAO, le Jeudi 22 octobre à 15h. Cette formation portera sur
plusieurs sujet, tel que les méthodes de com' et le respect de la charte
AE-UTBM ainsi qu'une initiation ou formation à la PAO pour vous donner des
astuces pour vos support de communications.
Merci de m'envoyer un mail si vous voulez y participer (
guillaume.nys@utbm.fr). Envoyez moi aussi vos attentes ainsi je pourrais
alors orienter cette formation dans les domaines qui vous intéressent le
plus.

mage

Responsable Communication Association des Etudiants de l'Université de
Technologie de Belfort-Montbéliard

----------------------------------------------------
Le Seven'Art présente Un Prophète
----------------------------------------------------

Bonjour,

Cette semaine le Seven'Art propose Un Prophète le mercredi 21 octobre en
P108 à 20h30. Un film de Jacques Audiard avec Tahar Rahim, Niels Arestrup,
Adel Bencherif.

Synopsis :
Condamné à six ans de prison, Malik El Djebena ne sait ni lire, ni écrire. A
son arrivée en Centrale, seul au monde, il paraît plus jeune, plus fragile
que les autres détenus. Il a 19 ans.
D'emblée, il tombe sous la coupe d'un groupe de prisonniers corses qui fait
régner sa loi dans la prison. Le jeune homme apprend vite. Au fil des "
missions ", il s'endurcit et gagne la confiance des Corses.
Mais, très vite, Malik utilise toute son intelligence pour développer
discrètement son propre réseau...


Deux affiches du film seront à gagner par tirage au sort durant la séance.

Cotisants AE : 2,50 euros | Autres : 3,50 euros (Possibilité de payer par
carte AE)

Entrée par le parking du bas.

Cinéphilement,
L'Equipe Seven'Art

----------------------------------------------------
UTtoons - programme de la semaine
----------------------------------------------------

Bonjour à tous,

Cette semaine, c'est avec plaisir que nous allons vous présenter et peut
être faire découvrir Lilo et Stitch et ensuite Origine. Rendez-vous lundi 19
à 20h dans l'amphi A200.


Lilo et stitch

A l'autre bout de l'univers, un savant quelque peu dérangé a donné naissance
à Stitch, la créature la plus intelligente et la plus destructrice qui ait
jamais existé. Conscientes de son exceptionnel potentiel dévastateur, les
autorités de sa planète s'apprêtent à l'arrêter, mais le petit monstre prend
la poudre d'escampette à bord de son vaisseau spatial.
Stitch échoue sur Terre, en plein Pacifique, sur l'île d'Hawaii.

Origine

300 ans après notre ère, la Terre vit meurtrie des blessures causées par
l'inconscience de l?homme. Le monde est désormais dominé par la toute
puissance des esprits de la forêt qui infligent à l'humanité leur colère
pour les souffrances passées. Dans ce nouveau monde, co-existent deux cités
: Ragna, qui oeuvre pour le retour de la civilisation, et la Cité Neutre,
qui prône l'harmonie avec la forêt. Mais le destin s'en mêle lorsque le
jeune Agito réveille par hasard Toola, une jeune fille du temps passé,
conservée mystérieusement dans un sanctuaire interdit.

En espérant vous voir nombreux,
Ticho & Otine

----------------------------------------------------
Club Astro - Pas de séance
----------------------------------------------------

Pas de séance prévue pour cette semaine en raison des vacances.
A la rentrée.

----------------------------------------------------
L'Arrêt Dessin participe à l'exposition "alliages"
----------------------------------------------------

Bonjour à tous!

Vous avez tous pu le lire sur le site de l'AE, ou de l'UTBM ainsi que dans
vos mails, le club Arrêt Dessin doit participer à l'exposition "alliages" à
l'UTBM.

Pour l'occasion, une artiste nous propose de faire des dessins, le mardi et
le mercredi de cette semaine à Sévenans. Je ne suis pas encore dans la
possibilité de vous donner les horaires exactes des séances de dessin. Si
vous êtes intéressés par rencontrer une artiste et faire un peu de croquis
et de dessin, contactez-moi!

Je vous invite tous au vernissage le Jeudi 22 Octobre à Sévenans de 18h à
20h. J'espère vous voir malgré la soirée à Montbéliard et les médians de TC.

Soir

----------------------------------------------------
Troll Penché - séances de jeu d'échecs
----------------------------------------------------

Salutations !

Tu aimes le jeu d'échecs ou tu aimerais apprendre à y jouer ?
Le troll penché organise chaque jeudi à partir de 14h au foyer de Belfort,
des séances de jeu d'échecs.

Si tu veux t'initier, te perfectionner ou simplement jouer quelques parties,
que tu sois débutant ou maitre, tu es le bienvenu =)

Trusion, responsable des séances échecs

----------------------------------------------------
La blague offerte et assumée par Dahu !
----------------------------------------------------

C'est une blonde qui retrouve une de ses copines, blonde aussi.
Holalalalalala, j'ai encore raté le permis...
Qu'est ce qui s'est passé ?
Je suis arrivée près d'un rond-point et là comme l'indique le panneau 30,
j'ai fait 30 fois le tour du rond-point.
Et tu t'es trompée de combien de tours ?

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
<tr><td width="601"><img src="http://ae.utbm.fr/d.php?id_file=4693&action=download" /></td></tr>
<tr bgcolor="#000000"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Introduction</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Salut les UTbohémiens,<br />
<br />
On attaque la dernière semaine avant les vacances ! Petite pensée pour tous
les TC qui passent leurs médians cette semaine. Les activités continuent,
tenez vous au chaud, l'hiver belfortain arrive à grands pas !
<br /><br />
Note pour les responsables de clubs :<br />
Le weekmail évolue, pour proposer des articles dans le weekmail, il faudra
désormais passer par la section outils de vos clubs accessible directement
depuis le menu "Gestion assos/clubs" sur le site AE. Le lien sera activé dès mercredi soir.
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Sommaire</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px"><ul>
<li>L'AE évolue !</li>
<li>Soirée étudiante gratuite à Montbéliard</li>
<li>Electrochoc - à vous les platines !</li>
<li>AE - Formation Com'</li>
<li>Le Seven'Art présente Un Prophète</li>
<li>UTtoons - programme de la semaine</li>
<li>Club Astro - Pas de séance</li>
<li>L'Arrêt Dessin participe à l'exposition "alliages"</li>
<li>Troll Penché - séances de jeu d'échecs</li>
</ul>
</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">L'AE évolue !</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">
A toi cher cotisant AE,
<br /><br />
La rentrée de septembre a été une nouvelle étape dans ma vie en tant
qu'Association Etudiante de l'UTBM, plus connue sous le nom d'AE!
Tout d'abord, bon nombre de nouveaux étudiants sont venus me rejoindre et
sont donc devenus COTISANT AE!!!
<br /><br />
Ensuite, fin septembre, les clubs ont présenté leur projet et budget lors
des Commissions de pôle (<a href="http://ae.utbm.fr/d.php?id_folder=1540">plus d'infos</a>) et ils ont
été voté lors du Conseil d'administration (
<a href="http://ae.utbm.fr/d.php?id_file=4625&action=download">Compte rendu</a>). De plus, j'ai eu
droit à une mise à jour de mes <a href="http://ae.utbm.fr/d.php?id_file=2343&action=download">statuts</a>) et de mon
<a href="http://ae.utbm.fr/d.php?id_file=2344&action=download">règlement intérieur</a>!
Puis, j'ai eu droit à ma fête d'anniversaire la semaine dernière où beaucoup
d'entre vous sont venus!! Merci!!
<br /><br />
Enfin, je rappelle que toutes les semaines, l'équipe qui s'active autour de
moi ce réunit pour parler de mon fonctionnement et qu'on peut retrouver le
compte-rendu (sérieux ou pas) de cette réunion sur :
<a href="http://ae.utbm.fr/forum2/?id_sujet=3585&spage=11">ici</a> !
<br /><br />
L'AE qui t'aime!
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Soirée étudiante gratuite à Montbéliard</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">
En l'honneur des étudiants, la Communauté d'Agglomération du Pays de
Montbéliard organise une nocturne le 22 octobre 09 à Montbéliard! L'occasion
de se rencontrer autour de jeux vidéos, musées, et d'une soirée!
<br /><br />
Programme:
<ul>
<li>Le tournoi de jeux vidéos aura lieu aux établissements publics numériques,
ce sont des jeux vidéos sur console Wii tels que Guitar Heroe. Des lots
seront à gagner (chèques cadeaux).</li>
<li>L'accueil des étudiants se fera à 20h00 à la Roselière de Montbéliard, le
bracelet pass leur sera remis sur présentation de leur carte étudiante. De
20 heures à 23 heures, ils pourront visiter gratuitement les musées de
Montbéliard</li>
<li>La soirée festive commencera à partir de 23H00, uniquement les personnes
porteuses du bracelet pass pourront y assister. L'AE et le BDF gère les
boissons, la lumière et le son</li>
<li>Une tombola sera organisée, plusieurs lots seront mis en jeu : 2 places
pour le concert de TRYO à l'Axone, des places de concerts pour la Mals,
L'Allan, les 4 saisons du Palot, des chèques cadeaux, des places de foot,
des cartes avantages jeunes...</li>
<br /><br />
Venez nombreux!
<br /><br />
L'équipe AE
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Electrochoc - à vous les platines !</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">
Salut à tous !
<br /><br />
Comme vous l'avez peut-être entendu au détour d'une conversation, la soirée
Electrochoc, grande soirée autour des musiques électroniques fait son grand
retour ce semestre au début du mois de décembre.
<br /><br />
Petite nouveauté, ce semestre, l'équipe d'organisation vous propose de mixer
sur un créneau de 30 minutes durant la soirée.
<br /><br />
Si tu es donc DJ averti, membre ou pas du club mix, libre à toi de nous
transmettre une maquette mixée par tes soins d'une durée maximale de 30
minutes. Attention cependant, cette maquette devra avoir été mixée sur
platines CDs et transmise à club.mix@utbm.fr avant le 08 novembre 2009.
<br /><br />
A vous de jouer !
A6, pour Electrochoc
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">AE - Formation Com'</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">
En temps que responsable com' de l'AE je propose aux cotisants de l'AE une
formation Com'/PAO, le Jeudi 22 octobre à 15h. Cette formation portera sur
plusieurs sujet, tel que les méthodes de com' et le respect de la charte
AE-UTBM ainsi qu'une initiation ou formation à la PAO pour vous donner des
astuces pour vos support de communications.<br />
Merci de m'envoyer un mail si vous voulez y participer (<a href="mailto:guillaume.nys@utbm.fr">guillaume.nys@utbm.fr</a>).
Envoyez moi aussi vos attentes ainsi je pourrais alors orienter cette
formation dans les domaines qui vous intéressent le plus.
<br /><br />
mage
<br /><br />
Responsable Communication Association des Etudiants de l'Université de
Technologie de Belfort-Montbéliard
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Le Seven'Art présente Un Prophète</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">
Bonjour,
<br /><br />
Cette semaine le Seven'Art propose Un Prophète le mercredi 21 octobre en
P108 à 20h30. Un film de Jacques Audiard avec Tahar Rahim, Niels Arestrup,
Adel Bencherif.
<br /><br />
Synopsis :<br />
Condamné à six ans de prison, Malik El Djebena ne sait ni lire, ni écrire. A
son arrivée en Centrale, seul au monde, il paraît plus jeune, plus fragile
que les autres détenus. Il a 19 ans.<br />
D'emblée, il tombe sous la coupe d'un groupe de prisonniers corses qui fait
régner sa loi dans la prison. Le jeune homme apprend vite. Au fil des "
missions ", il s'endurcit et gagne la confiance des Corses.<br />
Mais, très vite, Malik utilise toute son intelligence pour développer
discrètement son propre réseau...
<br /><br />
Deux affiches du film seront à gagner par tirage au sort durant la séance.
<br /><br />
Cotisants AE : 2,50 euros | Autres : 3,50 euros (Possibilité de payer par
carte AE)
<br /><br />
Entrée par le parking du bas.
<br /><br />
Cinéphilement,<br />
L'Equipe Seven'Art
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">UTtoons - programme de la semaine</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">
Bonjour à tous,
<br /><br />
Cette semaine, c'est avec plaisir que nous allons vous présenter et peut
être faire découvrir Lilo et Stitch et ensuite Origine. Rendez-vous lundi 19
à 20h dans l'amphi A200.
<br /><br />
Lilo et stitch<br />
A l'autre bout de l'univers, un savant quelque peu dérangé a donné naissance
à Stitch, la créature la plus intelligente et la plus destructrice qui ait
jamais existé. Conscientes de son exceptionnel potentiel dévastateur, les
autorités de sa planète s'apprêtent à l'arrêter, mais le petit monstre prend
la poudre d'escampette à bord de son vaisseau spatial.
Stitch échoue sur Terre, en plein Pacifique, sur l'île d'Hawaii.
<br /><br />
Origine :<br />
300 ans après notre ère, la Terre vit meurtrie des blessures causées par
l'inconscience de l?homme. Le monde est désormais dominé par la toute
puissance des esprits de la forêt qui infligent à l'humanité leur colère
pour les souffrances passées. Dans ce nouveau monde, co-existent deux cités
: Ragna, qui oeuvre pour le retour de la civilisation, et la Cité Neutre,
qui prône l'harmonie avec la forêt. Mais le destin s'en mêle lorsque le
jeune Agito réveille par hasard Toola, une jeune fille du temps passé,
conservée mystérieusement dans un sanctuaire interdit.
<br /><br />
En espérant vous voir nombreux,
Ticho & Otine
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Club Astro - Pas de séance</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">
Pas de séance prévue pour cette semaine en raison des vacances.<br />
A la rentrée.
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">L'Arrêt Dessin participe à l'exposition "alliages"</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">
Bonjour à tous!
<br /><br />
Vous avez tous pu le lire sur le site de l'AE, ou de l'UTBM ainsi que dans
vos mails, le club Arrêt Dessin doit participer à l'exposition "alliages" à
l'UTBM.
<br /><br />
Pour l'occasion, une artiste nous propose de faire des dessins, le mardi et
le mercredi de cette semaine à Sévenans. Je ne suis pas encore dans la
possibilité de vous donner les horaires exactes des séances de dessin. Si
vous êtes intéressés par rencontrer une artiste et faire un peu de croquis
et de dessin, contactez-moi!
<br /><br />
Je vous invite tous au vernissage le Jeudi 22 Octobre à Sévenans de 18h à
20h. J'espère vous voir malgré la soirée à Montbéliard et les médians de TC.
<br /><br />
Soir
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Troll Penché - séances de jeu d'échecs</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">
Salutations !
<br /><br />
Tu aimes le jeu d'échecs ou tu aimerais apprendre à y jouer ?
Le troll penché organise chaque jeudi à partir de 14h au foyer de Belfort,
des séances de jeu d'échecs.
<br /><br />
Si tu veux t'initier, te perfectionner ou simplement jouer quelques parties,
que tu sois débutant ou maitre, tu es le bienvenu =)
<br /><br />
Trusion, responsable des séances échecs
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">La blague !</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">
C'est une blonde qui retrouve une de ses copines, blonde aussi.
Holalalalalala, j'ai encore raté le permis...
Qu'est ce qui s'est passé ?
Je suis arrivée près d'un rond-point et là comme l'indique le panneau 30,
j'ai fait 30 fois le tour du rond-point.
Et tu t'es trompée de combien de tours ?
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
