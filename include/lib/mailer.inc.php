<?
class mailer
{
  private $to       = array();
  private $img      = array();
  private $from     = 'ae@utbm.fr';
  private $subject  = '';
  private $plaintxt = "Ceci est un message au format MIME 1.0 multipart/mixed.\n";
  private $htmltext = '';
  public function mailer($from,$subject)
  {
    $this->from    = $from;
    $this->subject = $subject;
  }

  public function add_dest($to)
  {
    if(is_array($to))
      foreach($to as $dest)
        $this->to[]=$dest;
    else
      $this->to[]=$to;
  }

  public function add_img($img)
  {
    $this->img[] = $img;
  }

  public function set_plain($txt)
  {
    $this->plaintxt = $txt;
  }

  public function set_html($html)
  {
    $this->htmltext = $html;
  }

  public function send()
  {
    $boundary = "-----=".md5(uniqid(rand()));
    $header   = "MIME-Version: 1.0\r\n";
    $header  .= "Content-Type: multipart/Alternative;\r\nboundary=\"$boundary\"\r\n";
    $header  .= "\r\n";
    $msg      = "Ceci est un message au format MIME 1.0 multipart/mixed.\r\n";
    $msg     .= "--$boundary\n";
    $msg     .= "Content-Type: Text/Plain;\n  charset=\"UTF-8\"\r\n";
    $msg     .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
    $msg     .= eregi_replace("\\\'","'",$this->plaintxt)."\r\n";
    $msg     .= "--$boundary\r\n";
    $msg     .= "Content-Type: Text/HTML;\n  charset=\"UTF-8\"\r\n";
    $msg     .= "Content-Transfer-Encoding: quoted-printable\r\n";
    if(!empty($this->img))
    {
      $attach = '';
      foreach($this->img as $img)
      {
        if($fp = fopen($img, "rb"))
        {
          $attachment     = fread($fp, filesize($img));
          fclose($fp);
          $uid            = gen_uid();
          $this->htmltext = str_replace($img,"cid:".$uid,$this->htmltext);
          $attach        .= "--$boundary\r\n";
          $mime           = mime_content_type($img);
          $attach        .= "Content-Type: ".$mime."; name=\"".$image."\"\r\n";
          $attach        .= "Content-Transfer-Encoding: base64\r\n";
          $attach        .= "Content-ID: <".$uid.">\r\n\r\n";
          $attach        .= chunk_split(base64_encode($attachment))."\r\n\r\n\r\n";
        }
      }
      $msg   .= eregi_replace("\\\'","'",str_replace('=','=3D', $this->htmltext))."\r\n";
      $msg   .= $attach;
      unset($attach);
    }
    else
      $msg   .= eregi_replace("\\\'","'",str_replace('=','=3D', $this->htmltext))."\r\n";
    $msg     .= "--$boundary--\r\n";
    mail(implode(', ',$this->to),
         $this->subject,
         $msg,
         "Reply-to: ".$this->from."\r\nFrom: ".$this->from."\r\n".$header);
    unset($msg);
  }
}
?>
