<?
exit();
$topdir = '../';
require_once($topdir. "include/site.inc.php");
require_once($topdir."include/entities/files.inc.php");
require_once($topdir."include/entities/folder.inc.php");
require_once($topdir.'include/lib/mailer.inc.php');
$site = new site();

$mailer = new mailer('Association des Étudiants <ae@utbm.fr>',
                     'Weekmail du 25 au 31 Mai 2009');
$mailer->add_dest(array('etudiants@utbm.fr',
                        'enseignants@utbm.fr',
                        'iatoss@utbm.fr',
                        'aude.petit@utbm.fr'));
//$mailer->add_dest("simon.lopez@utbm.fr");
$file = new dfile($site->db);
$file->load_by_id(4328);
$mailer->add_img($file);
$plain = 'Salut les UTbohémiens,
Premier Weekmail de l\'année ! Quelque peu vide, certes ! Mais bel et bien le
premier. L\'intégration suis son cours, les cours commencent à être suivis,
et la grippe A attends sagement l\'heure du week end d\'intégration pour tous
nous zigouiller :)

Les clubs de l\'AE vont reprendre petit à petit leurs activités, ce mail que
vous recevrez chaque semaine (en principe le lundi en début de journée) vous
permettra de vous y retrouver dans toutes les activités et événement de
notre chère association. Surveillez donc le bien, lisez le régulièrement
pour être au courant de tout ce que votre association a à vous proposer !

Petite information supplémentaire pour les clubs, afin de ne pas spammer les
boites mail de l\'ensemble des étudiants, vous êtes priés de nous transmettre
vos informations avant chaque dimanche soir 20h dernier délai, sur la boite
ae : ae@utbm.fr avec pour objet [Weekmail].

Sommaire :

 * AE - Commissions de pôle
 * Seven\'Art : Là-haut (16/09/2009)
 * Club Welcome - Welcome need U !

----------------------------------------------------
AE - Commissions de pôle
----------------------------------------------------

Bonjour à tous,

Avec la fin de l\'intégration, arrive la reprise des clubs de l\'AE, si tu te
sens l\'âme \'un responsable de club, si tu souhaites créer une nouvelle activité,
ou en réactiver un, le moment est venu de te faire connaître ! En effet, la
semaine prochaine auront lieu les commissions de pôles, où seront discutés
les projets du semestre ainsi que les budgets.

Si l\'aventure de responsable de club te tente, rendez-vous :

- Lundi 21/09/09 à 20 heures: Pôle Technique
- Lundi 21/09/09 à 21 heures: Pôle Culturel
- Mardi 22/09/09 à 20 heures: Grandes Activités
- Mercredi 23/09/09 à 20 heures: Pôle Entraide et Humanitaire
- Mercredi 24/09/09 à 21 heures: Pôle Artistique

Si vous êtes intéressés par l\'aventure, pensez à un budget prévisionnel, et
si vous avez des questions, rendez vous sur le forum de l\'AE

à bientôt

L\'équipe AE

----------------------------------------------------
Seven\'Art : Là-haut (16/09/2009)
----------------------------------------------------

Le Seven\'Art vous propose comme chaque mercredi soir, à Sévenans une
projection à prix sympa dans un amphi P108 transformé ! Cette semaine, le
club vous propose là-Haut, dernière production des studios Pixar à ne rater
sous aucun prétexte !

Synopsis :

Quand Carl, un grincheux de 78 ans, décide de réaliser le rêve de sa vie en
attachant des milliers de ballons à sa maison pour s’envoler vers l’Amérique
du Sud, il ne s’attendait pas à embarquer avec lui Russell, un jeune
explorateur de 9 ans, toujours très enthousiaste et assez envahissant… Ce
duo totalement imprévisible et improbable va vivre une aventure délirante
qui les plongera dans un voyage dépassant l’imagination.

Entrée : 2,50€ (cotisants AE) / 3,50€ (non cotisants) (Possibilité de payer
par carte AE)

----------------------------------------------------
Club Welcome - Welcome need U !
----------------------------------------------------

Bonjour à tous,

Une expérience à l\'international vous tente ? Par simple curiosité ou ayant
déjà un projet de mobilité bien défini, le club Welcome propose à vous,
étudiants de l\'UTBM, de nouer des liens dans un environnement interculturel
et convivial ! Venez à la rencontre des étudiants étrangers provenant des
quatre coins du monde pour étudier à l\'UTBM et découvrir la culture
Française !

La richesse des échanges interculturels, ayant aussi bien lieu à Belfort
qu\'à l\'étranger, est souvent insoupçonnée! L\'enrichissement personnel est
indéniable; par ailleurs créer des liens avec des étudiants désireux de
franchir le pont culturel peut vous ouvrir de nouveaux horizons, créer de
nouvelles opportunités.

Au moment de l\'intégration et de la reprise des cours, le club Welcome fait
également sa rentrée, et vous convie à une réunion d\'information pour vous
faire découvrir les activités qui vont se dérouler tout au long de ce
semestre ! Tout le monde peut participer, Ancien comme Nouveau, Tronc commun
autant que Branche !

Alors n\'hésitez plus, et venez nous rencontrer le jeudi 17 septembre à 16h
en salle Rantanplan (bâtiment des Dalton de la Maison des Eleves à Belfort).

D\'ici là, nous vous donnons rendez-vous sur le site web du club Welcome:
http://ae.utbm.fr/welcome/

A très bientôt !!

L\'équipe du club Welcome

----------------------------------------------------
La blague !
----------------------------------------------------

Deux militaires discutent :
- "Pourquoi t\'es dans l\'armée toi ?"
- "Parce que je suis célibataire et que j\'aime la guerre, et toi ?"
- "Parce que je suis marié et que je voulais la paix !"

--
à la semaine prochaine !
A6';
$mailer->set_plain($plain);
$html = '<html>
<body bgcolor="#333333" width="700px">
<table bgcolor="#333333" width="700px">
<tr><td align="center">
<table bgcolor="#ffffff" width="600" border="0" cellspacing="0" cellpadding="0" align="center">
<tr><td width="600" height="241"><img src="http://ae.utbm.fr/d.php?id_file=4328&action=download" /></td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">Introduction</font></td></tr>
<tr><td>Salut les UTbohémiens,<br />
<br />
Premier Weekmail de l\'année ! Quelque peu vide, certes ! Mais bel et bien le
premier. L\'intégration suis son cours, les cours commencent à être suivis,
et la grippe A attends sagement l\'heure du week end d\'intégration pour tous
nous zigouiller :)<br /></br />
Les clubs de l\'AE vont reprendre petit à petit leurs activités, ce mail que
vous recevrez chaque semaine (en principe le lundi en début de journée) vous
permettra de vous y retrouver dans toutes les activités et événement de
notre chère association. Surveillez donc le bien, lisez le régulièrement
pour être au courant de tout ce que votre association a à vous proposer !
<br /></br />
Petite information supplémentaire pour les clubs, afin de ne pas spammer les
boites mail de l\'ensemble des étudiants, vous êtes priés de nous transmettre
vos informations avant chaque dimanche soir 20h dernier délai, sur la boite
ae : ae@utbm.fr avec pour objet [Weekmail].
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">Sommaire</font></td></tr>
<tr><td><ul><li>AE - Commissions de pôle</li>
<li>Seven\'Art : Là-haut (16/09/2009)</li>
<li>Club Welcome - Welcome need U !</li>
</ul>
</td></tr>
<tr bgcolor="#00BBFF"><td><font color="#ffffff">AE - Commissions de pôle</font></td></tr>
<tr><td>Bonjour à tous,<br />
<br />
Avec la fin de l\'intégration, arrive la reprise des clubs de l\'AE, si tu te
sens l\'âme \'un responsable de club, si tu souhaites créer une nouvelle activité,
ou en réactiver un, le moment est venu de te faire connaître ! En effet, la
semaine prochaine auront lieu les commissions de pôles, où seront discutés
les projets du semestre ainsi que les budgets.<br />
<br />
Si l\'aventure de responsable de club te tente, rendez-vous :
<ul>
<li>Lundi 21/09/09 à 20 heures: Pôle Technique</li>
<li>Lundi 21/09/09 à 21 heures: Pôle Culturel</li>
<li>Mardi 22/09/09 à 20 heures: Grandes Activités</li>
<li>Mercredi 23/09/09 à 20 heures: Pôle Entraide et Humanitaire</li>
<li>Mercredi 24/09/09 à 21 heures: Pôle Artistique</li>
</ul>
Si vous êtes intéressés par l\'aventure, pensez à un budget prévisionnel, et
si vous avez des questions, rendez vous sur le forum de l\'AE<br />
<br />
à bientôt<br />
L\'équipe AE
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td><font color="#ffffff">Seven\'Art : Là-haut (16/09/2009)</font></td></tr>
<tr><td>Le Seven\'Art vous propose comme chaque mercredi soir, à Sévenans une
projection à prix sympa dans un amphi P108 transformé ! Cette semaine, le
club vous propose là-Haut, dernière production des studios Pixar à ne rater
sous aucun prétexte !<br />
<br />
Synopsis :<br />
Quand Carl, un grincheux de 78 ans, décide de réaliser le rêve de sa vie en
attachant des milliers de ballons à sa maison pour s’envoler vers l’Amérique
du Sud, il ne s’attendait pas à embarquer avec lui Russell, un jeune
explorateur de 9 ans, toujours très enthousiaste et assez envahissant… Ce
duo totalement imprévisible et improbable va vivre une aventure délirante
qui les plongera dans un voyage dépassant l’imagination.<br />
<br />
Entrée : 2,50€ (cotisants AE) / 3,50€ (non cotisants) (Possibilité de payer
par carte AE)
<br />&nbsp;</td></tr>
<tr bgcolor="#00BBFF"><td><font color="#ffffff">Club Welcome - Welcome need U !</font></td></tr>
<tr><td>
Bonjour à tous,
<br /></br />
Une expérience à l\'international vous tente ? Par simple curiosité ou ayant
déjà un projet de mobilité bien défini, le club Welcome propose à vous,
étudiants de l\'UTBM, de nouer des liens dans un environnement interculturel
et convivial ! Venez à la rencontre des étudiants étrangers provenant des
quatre coins du monde pour étudier à l\'UTBM et découvrir la culture
Française !
<br /></br />
La richesse des échanges interculturels, ayant aussi bien lieu à Belfort
qu\'à l\'étranger, est souvent insoupçonnée! L\'enrichissement personnel est
indéniable; par ailleurs créer des liens avec des étudiants désireux de
franchir le pont culturel peut vous ouvrir de nouveaux horizons, créer de
nouvelles opportunités.
<br /></br />
Au moment de l\'intégration et de la reprise des cours, le club Welcome fait
également sa rentrée, et vous convie à une réunion d\'information pour vous
faire découvrir les activités qui vont se dérouler tout au long de ce
semestre ! Tout le monde peut participer, Ancien comme Nouveau, Tronc commun
autant que Branche !
<br /></br />
Alors n\'hésitez plus, et venez nous rencontrer le jeudi 17 septembre à 16h
en salle Rantanplan (bâtiment des Dalton de la Maison des Eleves à Belfort).
<br /></br />
D\'ici là, nous vous donnons rendez-vous sur le site web du club Welcome:
http://ae.utbm.fr/welcome/
<br /></br />
A très bientôt !!
<br /></br />
L\'équipe du club Welcome
<br />&nbsp;</td></tr>
<tr bgcolor="#000000"><td><font color="#ffffff">La blague !</font></td></tr>
<tr><td>Deux militaires discutent :
- "Pourquoi t\'es dans l\'armée toi ?"
- "Parce que je suis célibataire et que j\'aime la guerre, et toi ?"
- "Parce que je suis marié et que je voulais la paix !"
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
