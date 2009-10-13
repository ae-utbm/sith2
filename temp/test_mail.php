<?
//exit();
$topdir = '../';
require_once($topdir. "include/site.inc.php");
require_once($topdir."include/entities/files.inc.php");
require_once($topdir."include/entities/folder.inc.php");
require_once($topdir.'include/lib/mailer.inc.php');
$site = new site();

$mailer = new mailer('Association des Étudiants <ae@utbm.fr>',
                     'Weekmail du 12 au 18 octobre 2009');
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

Suite à la panne de Webmail de ce week-end, votre weekmail arrive avec un
peu de retard, mais il arrive !
Comme vous le savez peut-être, cette semaine est une semaine très importante
dans notre vie associative. En effet, notre chère association fêtes ses 10
ans d'existence !! Pour fêter dignement cette grande étape, l'AE convie tous
ses cotisants à une grande soirée, ce jeudi à partir de 20h au foyer de
Belfort pour un cocktail, qui se poursuivra à 22h par une soirée Electro
gratuite.
Entrée sur invitation, n'oubliez pas de récupérer la votre. A Sévenans, au
bureau AE ou bien à Belfort, au bureau AE ou au foyer. On compte sur vous !
Passons aux news...


Sommaire :

 * AE - Le Grand Cocktail des 10ans !!!
 * AE - Decade Party
 * Soirée "supersize me" au foyer mardi soir à 19h30
 * Le bigband de l'UTBM compte sur vous
 * AG promo 8
 * Solidar'UT - Réunion pour l'organisation du téléthon
 * Séance du Club Astro
 * Jardin'UT - Nouveau Club !


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
Soirée "supersize me" au foyer mardi soir à 19h30
----------------------------------------------------

Bonjour à tous !



L'été est maintenant loin, le froid arrive, il est temps de remplir les
reserves pour avoir chaud pendant les longues nuits d'hiver!

Alors arrête de cacher tes formes et apporte tes kilos, içi on malbouffe, et
on en est fier !

Si comme moi tu aimes la grosse bouffe, viens partager des bons hot dog et
hamburgers, "homemade" bien entendu devant de la bonne bière du foyer !

Rendez vous mardi 13 au foyer à 19h30 pour une "grasse" ripaille entre amis!

je compte sur toi!

La promo 07

----------------------------------------------------
Le bigband de l'UTBM compte sur vous
----------------------------------------------------

Ami UTBohéMien bonjour!
Le Bigband de l'UTBM est une formation de 15 à 20 musiciens (parmi lesquels
Saxophonistes, Trompettistes, Trombonistes, Clarinettistes, Guitaristes,
Bassistes, Pianistes, Batteurs, etc.) qui reprend tout type de style (du
jazz, mais aussi du funk, de la bossa, du shuffle, du boogie, du
rock'n'roll, et tant d'autres) et qui, pour la deuxième année, dispose d'un
compositeur attitré !

- Si tu es un musicien intéressé : Nos répétitions se déroulent le jeudi
soir de 20h à 22h à la MDE Sévenans, c'est ouvert à tous, nouveau, ancien,
bon niveau, débutant. L'important est de savoir lire une partition et de
disposer d'un instrument.

- Si tu es intéressé par le club : Nous cherchons activement un responsable
communication et un photographe/caméraman afin d'immortaliser nos moments
musicaux

- Si tu es intéressé par la musique, en tant que public : Nous te donnons
rendez-vous lors de nos concerts :
* Le 21 Novembre 2009, lors du Gala de l'UTBM, à 22h dans la salle P302 (à
côté de la salle des colonnes)
* Le 28 Novembre 2009, lors du TribUT organisé à l'UTT, et en partenariat
avec les Bigbands de l'UTT et de l'UTC!

De plus, toutes nos répétitions sont publiques, il t'est donc possible de
venir nous voir le jeudi soir à 20h à la MDE !

Pour nous contacter, une seule adresse : bigband@utbm.fr
N'hésite surtout pas à nous passer un petit coucou !

A très bientôt,
Gawel pour le Bigband

----------------------------------------------------
AG promo 8
----------------------------------------------------

L'AG de la promo ce déroulera le jeudi 15 octobre dans la salle Rantanplan à
partir de 18h30 jusqu'à 20h..

Seront discutés pendant l'AG :

1) Le thème du repas de Promo (le repas étant programmé pour le 5
novembre)
   Quelques thèmes proposés
      o Prêtres et petits nenfants
      o Modules déambulateurs
      o Glands
      o Frenchy : berret, tshirt rayé noir et blanc, foulard rouge, fromage qui
        put et vin rouge
      o sportif
      o cannibale
      o dictateur
      o Série TV

2) On discutera aussi la nécessité d'un objet de Promo.


Toute la promo compte sur toi !

Le bureau de la 8.

----------------------------------------------------
Solidar'UT - Réunion pour l'organisation du téléthon
----------------------------------------------------

Salut! Tu connais le téléthon? Non? Visite ce site alors:
http://www.afm-telethon.fr/
Ca te tente de participer à cet événement national pour la recherche contre
les maladies génétiques? Dans ce cas là viens à la réunion, jeudi 15 à 14h
en salle Rantanplan.
On y définira les activités à faire (théâtre, lan, vente de peluches, stand
dans la rue piétonne de Belfort avec Barbes à Papa, vin chaud, etc...) et on
définira qui fait quoi.
N'ai pas peur de te lancer dans l'aventure: c'est extrêmement intéressant et
enrichissant et on sera là pour t'aider.

@ jeudi Prochain

Périeu pour Solidar'UT

----------------------------------------------------
Séance du Club Astro
----------------------------------------------------

Jeudi 15 octobre à partir de 20 heures dans la salle d'activité.
Cette semaine, le météo devrait être bonne, donc petite observation hors de
Belfort. Au programme, Jupiter à la webcam, et ciel profond.
Prévoir une tenue chaude !
Au cas ou, il y aura une projection de documentaire.
A jeudi

----------------------------------------------------
Jardin'UT - Nouveau Club !
----------------------------------------------------

Salut à tous !

Vous en avez peut-être entendu parler, un club de jardinage a été ouvert. Le
but dans un premier temps est d’entretenir la partie de la ME vers le
barbecue.Par entretenir, on veut dire, ramasser les fruits pourris, nettoyer
le barbecue et éventuellement débroussailler un peu là où c’est
nécessaire.Alors si vous vous sentez l’âme verte ou si vous avez juste envie
de vous bouger un peu pour quelque chose d’utile, n’hésitez plus, inscrivez
vous au club jardin’UT.

La première session aura lieu ce samedi 17 octobre. On se donne rendez vous
devant le Foyer vers 14h. Pensez à prendre des vêtements qui ne craignent
pas la boue, le reste du matériel qui pourra être nécessaire sera fourni.

Bonne semaine à tous et en espérant vous voire nombreux samedi.

2trèfle, secrétaire jardin’UT



P.S : un peu de pluie n’a jamais tué personne, donc si il y a quelques
averses, prenez un imper et ça sera bon ;)

----------------------------------------------------
La blague
----------------------------------------------------

Un homme est dans un bar en train de boire son café quand soudain lui prend
une envie pressante, ne sachant que faire, il met un post-it à côté du café
avec écrit : "J'ai craché dedans.". Il revient après s'être soulagé quelques
minutes après et trouve un autre post-it à côté du café où il est écrit :
"Moi aussi :)"

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
<tr><td width="601"><img src="http://ae.utbm.fr/d.php?id_file=4652&action=download" /></td></tr>
<tr bgcolor="#000000"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Introduction</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Salut les UTbohémiens,<br />
<br />
Suite à la panne de Webmail de ce week-end, votre weekmail arrive avec un
peu de retard, mais il arrive !<br />
Comme vous le savez peut-être, cette semaine est une semaine très importante
dans notre vie associative. En effet, notre chère association fêtes ses 10
ans d'existence !! Pour fêter dignement cette grande étape, l'AE convie tous
ses cotisants à une grande soirée, ce jeudi à partir de 20h au foyer de
Belfort pour un cocktail, qui se poursuivra à 22h par une soirée Electro
gratuite.<br />
Entrée sur invitation, n'oubliez pas de récupérer la votre. A Sévenans, au
bureau AE ou bien à Belfort, au bureau AE ou au foyer. On compte sur vous !
Passons aux news...
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Sommaire</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px"><ul>
<li>AE - Le Grand Cocktail des 10ans !!!</li>
<li>AE - Decade Party</li>
<li>Soirée "supersize me" au foyer mardi soir à 19h30</li>
<li>Le bigband de l'UTBM compte sur vous</li>
<li>AG promo 8</li>
<li>Solidar'UT - Réunion pour l'organisation du téléthon</li>
<li>Séance du Club Astro</li>
<li>Jardin'UT - Nouveau Club !</li>
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
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Soirée "supersize me" au foyer mardi soir à 19h30</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Bonjour à tous !
<br /><br />
L'été est maintenant loin, le froid arrive, il est temps de remplir les
reserves pour avoir chaud pendant les longues nuits d'hiver!
<br /><br />
Alors arrête de cacher tes formes et apporte tes kilos, içi on malbouffe, et
on en est fier !
<br /><br />
Si comme moi tu aimes la grosse bouffe, viens partager des bons hot dog et
hamburgers, "homemade" bien entendu devant de la bonne bière du foyer !
<br /><br />
Rendez vous mardi 13 au foyer à 19h30 pour une "grasse" ripaille entre amis!
<br /><br />
je compte sur toi!
<br />
La promo 07
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Le bigband de l'UTBM compte sur vous</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Ami UTBohéMien bonjour!
<br /><br />
Le Bigband de l'UTBM est une formation de 15 à 20 musiciens (parmi lesquels
Saxophonistes, Trompettistes, Trombonistes, Clarinettistes, Guitaristes,
Bassistes, Pianistes, Batteurs, etc.) qui reprend tout type de style (du
jazz, mais aussi du funk, de la bossa, du shuffle, du boogie, du
rock'n'roll, et tant d'autres) et qui, pour la deuxième année, dispose d'un
compositeur attitré !<li>
<ul>
<li>Si tu es un musicien intéressé : Nos répétitions se déroulent le jeudi
soir de 20h à 22h à la MDE Sévenans, c'est ouvert à tous, nouveau, ancien,
bon niveau, débutant. L'important est de savoir lire une partition et de
disposer d'un instrument.</li>
<li>Si tu es intéressé par le club : Nous cherchons activement un responsable
communication et un photographe/caméraman afin d'immortaliser nos moments
musicaux</li>
<li>Si tu es intéressé par la musique, en tant que public : Nous te donnons
rendez-vous lors de nos concerts :
<ul>
<li>Le 21 Novembre 2009, lors du Gala de l'UTBM, à 22h dans la salle P302 (à
côté de la salle des colonnes)</li>
<li>Le 28 Novembre 2009, lors du TribUT organisé à l'UTT, et en partenariat
avec les Bigbands de l'UTT et de l'UTC!</li>
</ul>
</ul>
<br /><br />
De plus, toutes nos répétitions sont publiques, il t'est donc possible de
venir nous voir le jeudi soir à 20h à la MDE !
<br /><br />
Pour nous contacter, une seule adresse : bigband@utbm.fr<br />
N'hésite surtout pas à nous passer un petit coucou !
<br /><br />
A très bientôt,<br />
Gawel pour le Bigband
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">AG promo 8</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Bonjour à vous,
<br /><br />
L'AG de la promo ce déroulera le jeudi 15 octobre dans la salle Rantanplan à
partir de 18h30 jusqu'à 20h..
<br /><br />
Seront discutés pendant l'AG :

<ul>
<li>Le thème du repas de Promo (le repas étant programmé pour le 5 novembre)<br />
Quelques thèmes proposés :</li>
<ul>
<li>Prêtres et petits nenfants</li>
<li>Modules déambulateurs</li>
<li>Glands</li>
<li>Frenchy : berret, tshirt rayé noir et blanc, foulard rouge, fromage qui put et vin rouge</li>
<li>sportif</li>
<li>cannibale</li>
<li>dictateur</li>
<li>Série TV</li>
</ul>
<li>On discutera aussi la nécessité d'un objet de Promo.</li>
</ul>
Toute la promo compte sur toi !
<br /><br />
Le bureau de la 8.
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Solidar'UT - Réunion pour l'organisation du téléthon</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Salut!<br />
Tu connais le téléthon? Non? Visite ce <a href="http://www.afm-telethon.fr/">site</a>.
<br /><br />
Ca te tente de participer à cet événement national pour la recherche contre
les maladies génétiques? Dans ce cas là viens à la réunion, jeudi 15 à 14h
en salle Rantanplan.<br />
On y définira les activités à faire (théâtre, lan, vente de peluches, stand
dans la rue piétonne de Belfort avec Barbes à Papa, vin chaud, etc...) et on
définira qui fait quoi.<br />
N'ai pas peur de te lancer dans l'aventure: c'est extrêmement intéressant et
enrichissant et on sera là pour t'aider.
<br /><br />
@ jeudi Prochain
<br /><br />
Périeu pour Solidar'UT
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Séance du Club Astro</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Jeudi 15 octobre à partir de 20 heures dans la salle d'activité.<br />
Cette semaine, le météo devrait être bonne, donc petite observation hors de
Belfort. Au programme, Jupiter à la webcam, et ciel profond.<br />
Prévoir une tenue chaude !<br />
Au cas ou, il y aura une projection de documentaire.<br />
A jeudi
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">Jardin'UT - Nouveau Club !</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Salut à tous !
<br /><br />
Vous en avez peut-être entendu parler, un club de jardinage a été ouvert. Le
but dans un premier temps est d’entretenir la partie de la ME vers le
barbecue.Par entretenir, on veut dire, ramasser les fruits pourris, nettoyer
le barbecue et éventuellement débroussailler un peu là où c’est
nécessaire.Alors si vous vous sentez l’âme verte ou si vous avez juste envie
de vous bouger un peu pour quelque chose d’utile, n’hésitez plus, inscrivez
vous au club jardin’UT.
<br /><br />
La première session aura lieu ce samedi 17 octobre. On se donne rendez vous
devant le Foyer vers 14h. Pensez à prendre des vêtements qui ne craignent
pas la boue, le reste du matériel qui pourra être nécessaire sera fourni.
<br /><br />
Bonne semaine à tous et en espérant vous voire nombreux samedi.
<br /><br />
2trèfle, secrétaire jardin’UT
<br /><br />
P.S : un peu de pluie n’a jamais tué personne, donc si il y a quelques
averses, prenez un imper et ça sera bon ;)
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td style="padding:2px 5px 2px 5px"><font color="#ffffff">La blague !</font></td></tr>
<tr><td style="padding:2px 5px 2px 5px">Un homme est dans un bar en train de boire son café quand soudain lui prend
une envie pressante, ne sachant que faire, il met un post-it à côté du café
avec écrit : "J'ai craché dedans.". Il revient après s'être soulagé quelques
minutes après et trouve un autre post-it à côté du café où il est écrit :
"Moi aussi :)"
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
