<?
require_once('../include/lib/mailer.inc.php');
require_once("../include/globals.inc.php");
$mailer = new mailer('Association des Étudiants <ae@utbm.fr>',
                     '[WEEKMAIL] de test');
$mailer->add_dest(array('simon.lopez@utbm.fr',
                        'simon.lopez@ayolo.org'));
$mailer->add_dest('m.simon.lopez@gmail.com');
$mailer->add_img('../themes/default2/images/important.png');
$mailer->set_plain('vive le html ?');
$html = '<html><body><table width="200px">
<tr><td>bleh : <img src="../themes/default2/images/important.png" /></td></tr>
<tr><td>bleh : <img src="http://ae.utbm.fr/themes/default2/images/important.png" /></td></tr>
</table></body></html>';
$mailer->set_html($html);
$mailer->send();

?>
