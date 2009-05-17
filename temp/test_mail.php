<?
require_once('../include/lib/mailer.inc.php');
$mailer = new mailer('Association des Étudiants <ae@utbm.fr>',
                     '[WEEKMAIL] de test');
$mailer->add_dest(array('simon.lopez@utbm.fr',
                        'simon.lopez@ayolo.org'));
$mailer->add_dest('m.simon.lopez@gmail.com');
$mailer->add_img('../themes/default2/images/important.png');
$mailer->set_plain('vive le html ?');
$html = '<table width="200px">
<tr><td><img src="../themes/default2/images/important.png" /></td></tr>
</table>';
$mailer->set_html($html);
$mailer->send();

?>
