<?php

/** @file
 *
 * @brief Classe de traduction dokuwiki -> html
 * 
 */

/* Copyright 2007
 *
 * - Simon Lopez <simon POINT lopez AT ayolo POINT org>
 *
 * Ce fichier fait partie du site de l'Association des Étudiants de
 * l'UTBM, http://ae.utbm.fr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA
 * 02111-1307, USA.
 */

/**
 * La fonction qui fait tout, tu lui file de la syntaxe wiki
 * elle te pond du xhtml, c'est de la magie en boite de concerve
 * @param $text le texte que tu veux qu'il resorte cossu :p
 */
function doku2xhtml($text)
{
  global $parser;
  $table   = array();
  $hltable = array();

  //preparse
  $text = preparse($text,$hltable);

  //fix : je sais pas comment changer le vin en eau dsl
  $text  = "\n".$text."\n";

  // last revision
  $text = str_replace("«««site-rev»»»", get_rev(), $text);

  /*les liens (à la base y'en a plein de suportés j'ai fait le ménage
   * ex : telnet, gopher, irc, ...
   */
  $urls = '(https?|file|ftp)';
  $ltrs = '\w';
  $gunk = '/\#~:.?+=&%@!\-';
  $punc = '.:?\-';
  $host = $ltrs.$punc;
  $any  = $ltrs.$gunk.$punc;

  /* first pass */

  // textes préformatés
  firstpass($table,$text,"#<nowiki>(.*?)</nowiki>#se","preformat('\\1','nowiki')");
  firstpass($table,$text,"#%%(.*?)%%#se","preformat('\\1','nowiki')");
  firstpass($table,$text,"#<code( (\w+))?>(.*?)</code>#se","preformat('\\3','code','\\2')");
  firstpass($table,$text,"#<file>(.*?)</file>#se","preformat('\\1','file')");

  // je sais pas si ça servira mais bon ...
  firstpass($table,$text,"#<html>(.*?)</html>#se","preformat('\\1','html')");
  firstpass($table,$text,"#<php>(.*?)</php>#se","preformat('\\1','php')");

  // block de code
  firstpass($table,$text,"/(\n( {2,}|\t)[^\*\-\n ][^\n]+)(\n( {2,}|\t)[^\n]*)*/se","preformat('\\0','block')","\n");

  //check if toc is wanted
  if(!isset($parser['toc'])){
    if(strpos($text,'~~NOTOC~~')!== false)
    {
      $text = str_replace('~~NOTOC~~','',$text);
      $parser['toc']  = false;
    }
    else
      $parser['toc']  = true;
  }
  if(!isset($parser['secedit'])) $parser['secedit'] = true;


  //headlines
  format_headlines($table,$hltable,$text);

  // links
  firstpass($table,$text,"#\[\[([^\]]+?)\]\]#ie","linkformat('\\1')");

  // media
  firstpass($table,$text,"#\{\{([^\}]+?)\}\}#ie","mediaformat('\\1')");

  // cherche les url complètes
  firstpass($table,$text,"#(\b)($urls:[$any]+?)([$punc]*[^$any])#ie","linkformat('\\2')",'\1','\4');

  // url www version courte
  firstpass($table,$text,"#(\b)(www\.[$host]+?\.[$host]+?[$any]+?)([$punc]*[^$any])#ie","linkformat('http://\\2')",'\1','\3');

  // windows shares 
  firstpass($table,$text,"#([$gunk$punc\s])(\\\\\\\\[$host]+?\\\\[$any]+?)([$punc]*[^$any])#ie","linkformat('\\2')",'\1','\3');

  // url ftp version courtes 
  firstpass($table,$text,"#(\b)(ftp\.[$host]+?\.[$host]+?[$any]+?)([$punc]*[^$any])#ie","linkformat('ftp://\\2')",'\1','\3');

  // les n'emails
  firstpass($table,$text,"#<([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)>#ie", "linkformat('\\1@\\2')");

  $text = htmlspecialchars($text);

  //citation
  $text = str_replace( CHR(10), "__slash_n__" , $text );
  while( preg_match("/\[quote=(.*?)\](.*?)\[\/quote\]/i",$text) )
  {
    $text = preg_replace("/\[quote=(.*?)\](.*?)\[\/quote\]/",
                         "<div style=\"margin: 10px 4px 10px 30px; padding: 4px;\">
  <b>Citation de $1 :</b>
  <div style=\"border: 1px #374a70 solid;
  margin-top:2px;
  padding: 4px;
  text-aling: justify;
  background-color: #ecf4fe;\">$2___</div>___</div>___",
           $text);
  }
  while( preg_match("/\[quote\](.*?)\[\/quote\]/i",$text) )
  {
    $text = preg_replace("/\[quote\](.*?)\[\/quote\]/",
                         "<div style=\"margin: 10px 4px 10px 30px; padding: 4px;\">
  <b>Citation :</b>
  <div style=\"border: 1px #374a70 solid;
  margin-top:2px;
  padding: 4px;
  text-aling: justify;
  background-color: #ecf4fe;\">$1___</div>___</div>___",
                        $text);
  }
  $text= str_replace('___</div>___</div>___','</div></div>'.CHR(10),$text);
  $text= str_replace('__slash_n__',CHR(10),$text);

  /* deuxième pass pour les formatages simples */
  $text = simpleformat($text);
  
  /* troisième pass - insert les trucs de la première pass */
  reset($table);
  while (list($key, $val) = each($table))
    $text = str_replace($key,$val,$text);

  $text = trim($text);
  return $text;
}

/**
 * On préparse le texte ligne par ligne.
 */
function preparse($text,&$hltable)
{
  $lines = split("\n",$text);


  for ($l=0; $l<count($lines); $l++)
  {
    $line = $lines[$l];

    // on cherche la fin des trucs à ne pas parser qui sont sur plusieurs lignes
    if($noparse){
      if(preg_match("#^.*?$noparse#",$line))
      {
        $noparse = '';
        $line = preg_replace("#^.*?$noparse#",$line,1);
      }
      else
        continue;
    }

    if(!$noparse)
    {
      // abat les indentations \o/
      if(preg_match('#^(  |\t)#',$line)) continue;
      // on enlève les blocs à pas parser qui sont inline
      $line = preg_replace("#<nowiki>(.*?)</nowiki>#","",$line);
      $line = preg_replace("#%%(.*?)%%#","",$line);
      $line = preg_replace("#<code>(.*?)</code>#","",$line);
      $line = preg_replace("#<file>(.*?)</file>#","",$line);
      $line = preg_replace("#<html>(.*?)</html>#","",$line);
      $line = preg_replace("#<php>(.*?)</php>#","",$line);
      // on cherche le début des block "noparse" multilignes
      if(preg_match('#^.*?<(nowiki|code|php|html|file)( (\w+))?>#',$line,$matches))
      {
         list($noparse) = split(" ",$matches[1]); //on vire les options
        $noparse = '</'.$noparse.'>';
        continue;
      }
      elseif(preg_match('#^.*?%%#',$line))
      {
        $noparse = '%%';
        continue;
      }
    }

    //headlines
    if(preg_match('/^(\s)*(==+)(.+?)(==+)(\s*)$/',$lines[$l],$matches))
    {
      $tk = tokenize_headline($hltable,$matches[2],$matches[3],$l);
      $lines[$l] = $tk;
    }

  }

  return join("\n",$lines);
}

/**
 * Cette fonction ajoute quelques infos à propos du "headline" qui lui est passé
 * comme ça on pourra faire de la marmelade après (un sommaire)
 */
function tokenize_headline(&$hltable,$pre,$hline,$lno)
{
  switch (strlen($pre))
  {
    case 2:
      $lvl = 5;
      break;
    case 3:
      $lvl = 4;
      break;
    case 4:
      $lvl = 3;
      break;
    case 5:
      $lvl = 2;
      break;
    default:
      $lvl = 1;
      break;
  }
  $token = mkToken();
  $hltable[] = array( 'name'  => htmlspecialchars(trim($hline)),
                      'level' => $lvl,
                      'line'  => $lno,
                      'token' => $token );
  return $token;
}

function format_headlines(&$table,&$hltable,&$text)
{
  global $parser;
  global $conf;
  global $lang;
  global $ID;

  $lang = 'fr'; // bein quoi ?

  $last = 0;
  $cnt  = 0;
  foreach($hltable as $hl)
  {
    $cnt++;
    $headline   = '';
    if($cnt - 1) $headline .= '</div>';
    $headline  .= '<a name="'.($cnt).'"></a>';
    $headline  .= '<h'.$hl['level'].'>';
    $headline  .= $hl['name'];
    $headline  .= '</h'.$hl['level'].'>';
    $headline  .= '<div class="level'.$hl['level'].'">';

    if($hl['level'] <= $conf['maxtoclevel'])
    {
      $content[]  = array('id'    => $cnt,
                          'name'  => $hl['name'],
                          'level' => $hl['level']);
    }

    if( $parser['secedit'] &&
       ($hl['level'] <= $conf['maxseclevel']) &&
       ($hl['line'] - $last > 2))
   {
      $secedit  = '<div class="secedit">[<a href="';
      $secedit .= wl($ID,"do=edit,lines=$last-".($hl['line'] - 1));
      $secedit .= '" class="secedit">';
      $secedit .= $lang['secedit'];
      $secedit .= '</a>]</div>';
      $headline = $secedit.$headline;
      $last = $hl['line'];
    }

    $table[$hl['token']] = $headline;
  }

  if ($cnt)
  {
    $token = mktoken();
    $text .= $token;
    $table[$token] = '</div>';
  }

  if($parser['secedit'] && $last)
  {
    $secedit  = '<div class="secedit">[<a href="';
    $secedit .= wl($ID,"do=edit,lines=$last-");
    $secedit .= '" class="secedit">';
    $secedit .= $lang['secedit'];
    $secedit .= '</a>]</div>';
    $token    = mktoken();
    $text    .= $token;
    $table[$token] = $secedit; 
  }

  if ($parser['toc'] && count($content) > 2)
  {
    $token = mktoken();
    $text  = $token.$text;
    $table[$token] = html_toc($content);
  }
}

function linkformat($match)
{
  global $conf;
  global $topdir;
  $ret = '';
  $match = str_replace('\\"','"',$match);

  list($link,$name) = split('\|',$match,2);
  $link   = trim($link);
  $name   = trim($name);
  $class  = '';
  $target = '';
  $style  = '';
  $pre    = '';
  $post   = '';
  $more   = '';

  $realname = $name;

  // email
  if(preg_match('#([a-z0-9\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i',$link))
    format_link_email($link,$name,$class,$target,$style,$pre,$post,$more);
  // liens
  else
    format_link($link,$name,$class,$target,$style,$pre,$post,$more);
    
  //les dfiles://
  $link = preg_replace("/dfile:\/\/([0-9]*)\/preview/i",$topdir."d.php?action=download&download=preview&id_file=$1",$link);
  $link = preg_replace("/dfile:\/\/([0-9]*)\/thumb/i",$topdir."d.php?action=download&download=thumb&id_file=$1",$link);
  $link = preg_replace("/dfile:\/\/([0-9]*)/i",$topdir."d.php?action=download&id_file=$1",$link);

  //les article://
  $link = preg_replace("/article:\/\//i",$topdir."article.php?name=",$link);
  
  $ret .= $pre;
  $ret .= '<a href="'.$link.'"';
  if($class)  $ret .= ' class="'.$class.'"';
  if($target) $ret .= ' target="'.$target.'"';
  if($style)  $ret .= ' style="'.$style.'"';
  if($more)   $ret .= ' '.$more;
  $ret .= '>';
  $ret .= $name;
  $ret .= '</a>';
  $ret .= $post;

  return $ret;
}

/**
 * les trucs simples et pas chiant c'est ici
 */
function simpleformat($text)
{
  global $conf;

  $text = preg_replace('#&lt;del&gt;(.*?)&lt;/del&gt;#is','<s>\1</s>',$text); //del
  $text = preg_replace('/__([^_]+?)__/s','<u>\1</u>',$text);  //underline
  $text = preg_replace('/\/\/([^_]+?)\/\//s','<em>\1</em>',$text);  //emphasize
  $text = preg_replace('/\*\*([^*]+?)\*\*/s','<strong>\1</strong>',$text);  //bold
  $text = preg_replace('/\'\'([^\']+?)\'\'/s','<code>\1</code>',$text);  //code
  $text = preg_replace('/^(\s)*----+(\s*)$/m','<hr noshade="noshade" size="1" />',$text); //hr

  $text = preg_replace('#&lt;sub&gt;(.*?)&lt;/sub&gt;#is','<sub>\1</sub>',$text);
  $text = preg_replace('#&lt;sup&gt;(.*?)&lt;/sup&gt;#is','<sup>\1</sup>',$text);
 
  $text = preg_replace("/\n((&gt;)[^\n]*?\n)+/se","'\n'.quoteformat('\\0').'\n'",$text);
  
  $text = preg_replace('/([^-])--([^-])/s','\1&#8211;\2',$text);
  $text = preg_replace('/([^-])---([^-])/s','\1&#8212;\2',$text);
  $text = preg_replace('/&quot;([^\"]+?)&quot;/s','&#8220;\1&#8221;',$text);
  $text = preg_replace('/(\s)\'(\S)/m','\1&#8216;\2',$text);
  $text = preg_replace('/(\S)\'/','\1&#8217;',$text);
  $text = preg_replace('/\.\.\./','\1&#8230;\2',$text);
  $text = preg_replace('/(\d+)x(\d+)/i','\1&#215;\2',$text);

  $text = preg_replace('/&gt;&gt;/i','&raquo;',$text);
  $text = preg_replace('/&lt;&lt;/i','&laquo;',$text);

  $text = preg_replace('/&lt;-&gt;/i','&#8596;',$text);
  $text = preg_replace('/&lt;-/i','&#8592;',$text);
  $text = preg_replace('/-&gt;/i','&#8594;',$text);

  $text = preg_replace('/&lt;=&gt;/i','&#8660;',$text);
  $text = preg_replace('/&lt;=/i','&#8656;',$text);
  $text = preg_replace('/=&gt;/i','&#8658;',$text);

  //retours à la ligne forcés
  $text = preg_replace('#\\\\\\\\(\s)#',"<br />\\1",$text);

  // dos2unix
  $text = str_replace("\r\n","\n",$text);
  $text = str_replace("\n\r","\n",$text);
  $text = str_replace("\r","\n",$text);

  // lists (blocks leftover after blockformat)
  $text = preg_replace("/(\n( {2,}|\t)[\*\-][^\n]+)(\n( {2,}|\t)[^\n]*)*/se","\"\\n\".listformat('\\0')",$text);

  // tableaux
  $text = preg_replace("/\n(([\|\^][^\n]*?)+[\|\^]\n)+/se","\"\\n\".tableformat('\\0')",$text);

  //smileys
  $text = smileys($text);

  // footnotes
  $text = footnotes($text);

  // double sauts de ligne = nouveau paragraphe
  $text = str_replace("\n\n","<p />",$text);

  return $text;
}

/**
 * les footnotes
 */
function footnotes($text)
{
  $num = 0;
  while (preg_match('/\(\((.+?)\)\)/s',$text,$match))
  {
    $num++;
    $fn    = $match[1];
    $linkt = '<a href="#fn'.$num.'" name="fnt'.$num.'" class="fn_top">'.$num.')</a>';
    $linkb = '<a href="#fnt'.$num.'" name="fn'.$num.'" class="fn_bot">'.$num.')</a>';
    $text  = preg_replace('/ ?\(\((.+?)\)\)/s',$linkt,$text,1);
    if($num == 1) $text .= '<div class="footnotes">';
    $text .= '<div class="fn">'.$linkb.' '.$fn.'</div>';
  }

  if($num) $text .= '</div>';
  return $text;
}

/**
 * on remplace les smileys :)
 */
function smileys($text)
{
  global $topdir;
  $smileys = array(":-)"=>"smile.png",
                   ":)"=>"smile.png",
                   "^_^"=>"happy.png",
                   "^^"=>"happy.png",
                   ";)"=>"wink.png",
                   ";-)"=>"wink.png",
                   ":-/"=>"confused.png",
                   ":/"=>"confused.png",
                   ":-|"=>"neutral.png",
                   ":|"=>"neutral.png",
                   ":-D"=>"lol.png",
                   ":D"=>"lol.png",
                   ":-o"=>"omg.png",
                   ":-O"=>"omg.png",
                   ":o"=>"omg.png",
                   ":O"=>"omg.png",
                   "8-O"=>"omg.png",
                   "Oo"=>"dizzy.png",
                   "O_o"=>"dizzy.png",
                   "O_O"=>"dizzy.png",
                   "o_o"=>"dizzy.png",
                   "o_O"=>"dizzy.png",
                   ":'("=>"cry.png",
                   ";-("=>"cry.png",
                   ";("=>"cry.png",
                   ":-p"=>"tongue.png",
                   ":-P"=>"tongue.png",
                   ":p"=>"tongue.png",
                   ":P"=>"tongue.png"
                   );
  $smPath = $topdir."/images/forum/smilies/";
  foreach($smileys as $tag => $img)
  {
    if ( file_exists($smPath . "/" . $img) )
    {
      $tag = preg_replace('!\]!i', '\]', $tag);
      $tag = preg_replace('!\[!i', '\[', $tag);
      $tag = preg_replace('!\)!i', '\)', $tag);
      $tag = preg_replace('!\(!i', '\(', $tag);
      $tag = preg_replace('!\!!i', '\!', $tag);
      $tag = preg_replace('!\^!i', '\^', $tag);
      $tag = preg_replace('!\$!i', '\$', $tag);
      $tag = preg_replace('!\{!i', '\}', $tag);
      $tag = preg_replace('!\}!i', '\{', $tag);
      $tag = preg_replace('!\?!i', '\?', $tag);
      $tag = preg_replace('!\+!i', '\+', $tag);
      $tag = preg_replace('!\*!i', '\*', $tag);
      $tag = preg_replace('!\.!i', '\.', $tag);
      $tag = preg_replace('!\|!i', '\|', $tag);
      $text = preg_replace('! '.$tag.' !i', ' <img src="'.$smPath.$img.'" alt="" /> ', $text);
      $text = preg_replace('!\n'.$tag.' !i', "\n<img src=\"".$smPath.$img."\" alt=\"\" /> ", $text);
      $text = preg_replace('!\n'.$tag.'\n!i', "\n<img src=\"".$smPath.$img."\" alt=\"\" />\n", $text);
      $text = preg_replace('!^'.$tag.' !i', '<img src="'.$smPath.$img.'" alt="" /> ', $text);
      $text = preg_replace('!\n'.$tag.'$!i', "\n<img src=\"".$smPath.$img."\" alt=\"\" />", $text);
      $text = preg_replace('!^'.$tag.'$!i', '<img src="'.$smPath.$img.'" alt="" />', $text);
			$text = preg_replace('! '.$tag.'$!i', ' <img src="'.$smPath.$img.'" alt="" />', $text);
			$text = preg_replace('! '.$tag.'\n!i', " <img src="'.$smPath.$img.'" alt="" />\n", $text);
    }
  }
  return $text;
}

function firstpass(&$table,&$text,$regexp,$replace,$lpad='',$rpad='')
{
  $ext='';
  if(substr($regexp,-1) == 'e')
  {
    $ext='e';
    $regexp = substr($regexp,0,-1);
  }

  while(preg_match($regexp,$text,$matches))
  {
    $token = mkToken();
    $match = $matches[0];
    $text  = preg_replace($regexp,$lpad.$token.$rpad,$text,1);
    $table[$token] = preg_replace($regexp.$ext,$replace,$match);
  }
}

function mkToken()
{
  return '~'.md5(uniqid(rand(), true)).'~';
}

/**
 * quote quote quodec !
 */
function quoteformat($block)
{
  $block = trim($block);
  $lines = split("\n",$block);

  $lvl = 0;
  $ret = "";
  foreach ($lines as $line)
  {
    $cnt = 0;
    while(substr($line,0,4) == '&gt;')
    {
      $line = substr($line,4);
      $cnt++;
    }

    if($cnt > $lvl)
      for ($i=0; $i< $cnt - $lvl; $i++)
        $ret .= '<div class="quote">';
    elseif($cnt < $lvl)
      for ($i=0; $i< $lvl - $cnt; $i++)
        $ret .= "</div>\n";

    $ret .= ltrim($line)."\n";
    $lvl = $cnt;
  }

  for ($i=0; $i< $lvl; $i++)
    $ret .= "</div>\n";

  return $ret;
}

function tableformat($block)
{
  $block = trim($block);
  
  if(preg_match("/@(.+?)@/",$block))
  {
    preg_match("/@(.+?)@/s",$block,$match);
    $class = str_replace('@', '', $match[0]);
    $block = preg_replace('/@(.+?)@/s', '', $block);
  }
  
  $lines = split("\n",$block);
  $ret = "";
  $rows = array();
  for($r=0; $r < count($lines); $r++)
  {
    $line = $lines[$r];
    $line = preg_replace('/[\|\^]\s*$/', '', $line);
    $c = -1;
    for($chr=0; $chr < strlen($line); $chr++)
    {
      if($line[$chr] == '^')
      {
        $c++;
        $rows[$r][$c]['head'] = true;
        $rows[$r][$c]['data'] = '';
      }
      elseif($line[$chr] == '|')
      {
        $c++;
        $rows[$r][$c]['head'] = false;
        $rows[$r][$c]['data'] = '';
      }
      else
        $rows[$r][$c]['data'].= $line[$chr];
    }
  }

  // et là les tables de la loi furent !
  if(isset($class))
    $ret .= "<table class=\"".$class."\">\n";
  else
    $ret .= "<table class=\"inline dokutable\">\n";
  for($r=0; $r < count($rows); $r++)
  {
    $ret .= "  <tr>\n";

    for ($c=0; $c < count($rows[$r]); $c++)
    {
      $cspan=1;
      $format=alignment($rows[$r][$c]['data']);
      $format=$format['align'];
      $data = trim($rows[$r][$c]['data']);
      $data = smileys($data);
      $head = $rows[$r][$c]['head'];

      while($c < count($rows[$r])-1 && $rows[$r][$c+1]['data'] == '')
      {
        $c++;
        $cspan++;
      }
      if($cspan > 1)
        $cspan = 'colspan="'.$cspan.'"';
      else
        $cspan = '';

      if ($head)
        $ret .= "    <th class=\"inline $format\" $cspan>$data</th>\n";
      else
        $ret .= "    <td class=\"inline $format\" $cspan>$data</td>\n";
    }
    $ret .= "  </tr>\n";
  }
  $ret .= "</table>\n\n";

  return $ret;
}

function listformat($block)
{
  $block = substr($block,1);
  $text = str_replace('\\"','"',$text);

  $ret='';
  $lst=0;
  $lvl=0;
  $enc=0;
  $lines = split("\n",$block);

  $cnt=0;
  $items = array();
  foreach ($lines as $line)
  {
    $lvl  = 0;
    $lvl += floor(strspn($line,' ')/2);
    $lvl += strspn($line,"\t");
    $line = preg_replace('/^[ \t]+/','',$line);
    (substr($line,0,1) == '-') ? $type='ol' : $type='ul';
    $line = preg_replace('/^[*\-]\s*/','',$line);
    $line = smileys($line);
    $items[$cnt]['level'] = $lvl;
    $items[$cnt]['type']  = $type;
    $items[$cnt]['text']  = $line;
    $cnt++;
  }

  $current['level'] = 0;
  $current['type']  = '';

  $level = 0;
  $opens = array();

  foreach ($items as $item)
  {

    if( $item['level'] > $level )
    {
      $ret .= "\n<".$item['type'].">\n";
      array_push($opens,$item['type']);
    }
    elseif( $item['level'] < $level )
    {
      $ret .= "</li>\n";
      for ($i=0; $i<($level - $item['level']); $i++)
        $ret .= '</'.array_pop($opens).">\n</li>\n";
    }
    else
      $ret .= "</li>\n";

    $level = $item['level'];

    $ret .= '<li class="level'.$item['level'].'">';
    $ret .= '<span class="li">'.$item['text'].'</span>';
  }

  while ($open = array_pop($opens))
  {
    $ret .= "</li>\n";
    $ret .= '</'.$open.">\n";
  }
  return $ret;
}

function preformat($text,$type,$option='')
{
  global $conf;
  $text = str_replace('\\"','"',$text);
  
  if($type == 'php' && !$conf['phpok']) $type='file';
  if($type == 'html' && !$conf['htmlok']) $type='file';
  
  switch ($type)
  {
    case 'php':
        ob_start();
        eval($text);
        $text = ob_get_contents();
        ob_end_clean();
      break;
    case 'html':
      break;
    case 'nowiki':
      $text = htmlspecialchars($text);
      break;
    case 'file':
      $text = htmlspecialchars($text);
      $text = '<pre class="file">'.$text.'</pre>';
      break;
    case 'code':
      $text = htmlspecialchars($text);
      $text = '<pre class="code">'.$text.'</pre>';
      break;
    case 'block':
      $text  = substr($text,1);
      $lines = split("\n",$text);
      $text  = '';
      foreach($lines as $line)
        $text .= substr($line,2)."\n";
      $text = htmlspecialchars($text);
      $text = '<pre class="pre">'.$text.'</pre>';
      break;
  }
  return $text;
}

function mediaformat($text)
{
  global $conf;
  global $topdir;
  $name = str_replace('\\"','"',$text);
  $ret .= $pre;
  $format=alignment($name);
  list($img,$name) = split('\|',$format['src'],2);
  $img=trim($img);
  list($img,$sizes) = split('\?',$img,2);
  list($width,$height) = split('x',$sizes,2);
  $name=trim($name);
  //les dfiles://
  $img = preg_replace("/dfile:\/\/([0-9]*)\/preview/i",$topdir."d.php?action=download&download=preview&id_file=$1",$img);
  $img = preg_replace("/dfile:\/\/([0-9]*)\/thumb/i",$topdir."d.php?action=download&download=thumb&id_file=$1",$img);
  $img = preg_replace("/dfile:\/\//i",$topdir."d.php?action=download&id_file=",$img);
  $ret .= '<img src="'.$img.'"';
  $ret .= ' class="media'.$format['align'].'"';
  $ret .= ' width="'.$width.'"';
  $ret .= ' height="'.$height.'"';
  $ret .= ' alt="'.$name.'" />';
  return $ret;
}

function alignment($texte)
{
  $r=false;
  $l=false;
  $left=rtrim($texte);
  $right=ltrim($texte);
  if($texte != $right)
    $r=true;
  if($texte != $left)
    $l=true;
  
  if ($l && $r)
    return array('src'=>$texte, 'align'=>"center");
  elseif($r)
    return array('src'=>$texte, 'align'=>"right");
  elseif($l)
    return array('src'=>$texte, 'align'=>"left");
  else
    return array('src'=>$texte, 'align'=>"");
}

/**
 * formatage des liens
 *
 * $link   URL pour le href=""
 * $name
 * $class  CSS class du lien
 * $target la cible (blank) pour la fenetre courante
 * $style  style aditionnels style=""
 * $pre
 * $post
 * $more
 *
 */

function format_link(&$link,&$name,&$class,&$target,&$style,&$pre,&$post,&$more)
{
  $class  = 'urlextern';
  $target = $conf['target']['extern'];
  $pre    = '';
  $post   = '';
  $style  = '';
  $more   = '';
  $link   = $link;
  if(!$name)
    $name = htmlspecialchars($link);
  else
  {
    if(preg_match("#\{\{([^\}]+?)\}\}#ie",$name))
    {
      $name=preg_replace("/\{\{/s","",$name);
      $name=preg_replace("/\}\}/s","",$name);
      $name=str_replace($name, mediaformat($name), $name);
    }
  }
}

function format_link_email(&$link,&$name,&$class,&$target,&$style,&$pre,&$post,&$more)
{
  $class  = 'mail';
  $target = '';
  $pre    = '';
  $post   = '';
  $style  = '';
  $more   = '';

  $name   = htmlspecialchars($name);
  
  if($conf['mailguard']=='visible')
  {
    $link = str_replace('@',' [at] ',$link);
    $link = str_replace('.',' [dot] ',$link);
    $link = str_replace('-',' [dash] ',$link);
  }
  for ($x=0; $x < strlen($link); $x++)
      $encode .= '&#x' . bin2hex($link[$x]).';';
  $link = $encode;
  
  if(!$name)
    $name = $link;
  else
  {
    if(preg_match("#\{\{([^\}]+?)\}\}#ie",$name))
    {
      $name=preg_replace("/\{\{/s","",$name);
      $name=preg_replace("/\}\}/s","",$name);
      $name=str_replace($name, mediaformat($name), $name);
    }
  }
  $link   = "mailto:$link";
}

?>
