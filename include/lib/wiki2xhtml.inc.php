<?php
// ***** BEGIN LICENSE BLOCK *****
// Version: MPL 1.1/GPL 2.0/LGPL 2.1
//
// The contents of this file are subject to the Mozilla Public License Version
// 1.1 (the "License"); you may not use this file except in compliance with
// the License. You may obtain a copy of the License at
// http://www.mozilla.org/MPL/
//
// Software distributed under the License is distributed on an "AS IS" basis,
// WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
// for the specific language governing rights and limitations under the
// License.
//
// The Original Code is Wiki2xhtml.
//
// The Initial Developer of the Original Code is
// Olivier Meunier.
// Portions created by the Initial Developer are Copyright (C) 2003
// the Initial Developer. All Rights Reserved.
//
// Contributor(s):
// Stephanie Booth
// Mathieu Pillard
// Christophe Bonijol
// Jean-Charles Bagneris
//
// Alternatively, the contents of this file may be used under the terms of
// either the GNU General Public License Version 2 or later (the "GPL"), or
// the GNU Lesser General Public License Version 2.1 or later (the "LGPL"),
// in which case the provisions of the GPL or the LGPL are applicable instead
// of those above. If you wish to allow use of your version of this file only
// under the terms of either the GPL or the LGPL, and not to allow others to
// use your version of this file under the terms of the MPL, indicate your
// decision by deleting the provisions above and replace them with the notice
// and other provisions required by the GPL or the LGPL. If you do not delete
// the provisions above, a recipient may use your version of this file under
// the terms of any one of the MPL, the GPL or the LGPL.
//
// ***** END LICENSE BLOCK *****
//
// Version : 3.1
// Release date : Monday 2004-03-29

// History :
// 3.1a
// Olivier
//			=> Bug du Call-time pass-by-reference
//
// 3.1
// Olivier
//			=> Ajout des macros «««..»»»
//			=> Ajout des blocs vides øøø
//			=> Ajout du niveau de titre paramétrable
//			=> Option de blocage du parseur dans les <pre>
//			=> Titres au format setext (experimental, désactivé)
//
// 3.0
// Olivier		=> Récriture du parseur inline, plus d'erreur XHTML
//			=> Ajout d'une vérification d'intégrité pour les listes
//			=> Les acronymes sont maintenant dans un fichier texte
//			=> Ajout d'un tag images ((..)), del --..-- et ins ++..++
//			=> Plus possible de faire des liens JS [lien|javascript:...]
//			=> Ajout des notes de bas de page §§...§§
//			=> Ajout des mots wiki
//
// 2.5
// Olivier		=> Récriture du code, plus besoin du saut de ligne entre blocs !=
//
// 2.0
// Stephanie	=> correction des PCRE et ajout de fonctionnalités
// Mathieu 	=> ajout du strip-tags, implementation des options, reconnaissance automatique d'url, etc.
// Olivier		=> chagement de active_link en active_urls
//			=> ajout des options pour les blocs
//			=> intégration de l'aide dans le code, avec les options
//			=> début de quelque chose pour la reconnaissance auto d'url (avec Mat)

// TODO :
// Mathieu	=> active_wiki_urls (modifier wikiParseUrl ?)
// 		=> active_auto_urls
//
// *		=> ajouter des options.
// 		=> trouver un meilleur nom pour active_link ? (pour le jour ou ca sera tellement une usine
//		   a gaz que on generera des tags <link> :)
//

// Wiki2xhtml

class wiki2xhtml
{
  var $__version__ = '3.1a';

  var $T;
  var $opt;
  var $line;
  var $foot_notes;

  var $tags;
  var $open_tags;
  var $close_tags;
  var $all_tags;
  var $tag_pattern;
  var $escape_table;
  var $allowed_inline = array();

  var $index;

  function wiki2xhtml()
  {
    // Mise en place des options
    $this->setOpt('active_title',1);		// Activation des titres !!!
    $this->setOpt('active_setext_title',0);	// Activation des titres setext (EXPERIMENTAL)
    $this->setOpt('active_hr',1);			// Activation des <hr />
    $this->setOpt('active_lists',1);		// Activation des listes
    $this->setOpt('active_quote',1);		// Activation du <blockquote>
    $this->setOpt('active_pre',1);		// Activation du <pre>
    $this->setOpt('active_empty',0);		// Activation du bloc vide øøø
    $this->setOpt('active_auto_urls',0);	// Activation de la reconnaissance d'url
    $this->setOpt('active_urls',1);		// Activation des liens []
    $this->setOpt('active_auto_img',0);	// Activation des images automatiques dans les liens []
    $this->setOpt('active_img',1);		// Activation des images (())
    $this->setOpt('active_anchor',1);		// Activation des ancres ~...~
    $this->setOpt('active_em',1);			// Activation du <em> ''...''
    $this->setOpt('active_strong',1);		// Activation du <strong> __...__
    $this->setOpt('active_br',1);			// Activation du <br /> %%%
    $this->setOpt('active_q',1);			// Activation du <q> {{...}}
    $this->setOpt('active_code',1);		// Activation du <code> @@...@@
    $this->setOpt('active_acronym',1); 	// Activation des acronymes
    $this->setOpt('active_ins',1);		// Activation des ins ++..++
    $this->setOpt('active_del',1);		// Activation des del --..--
    $this->setOpt('active_footnotes',1);	// Activation des notes de bas de page
    $this->setOpt('active_wikiwords',0);	// Activation des mots wiki
    $this->setOpt('active_macros',1);		// Activation des macros «««..»»»

    $this->setOpt('parse_pre',1);			// Parser l'intérieur de blocs <pre> ?

    $this->setOpt('active_fix_word_entities',1); // Fixe les caractères MS
    $this->setOpt('active_fr_syntax',1);	// Corrections syntaxe FR

    $this->setOpt('first_title_level',1);	// Premier niveau de titre <h..>

    $this->setOpt('note_prefix','wiki-footnote');
    $this->setOpt('note_str','<div class="footnotes"><h4>Notes</h4>%s</div>');
    $this->setOpt('words_pattern','((?<![A-Za-z0-9µÀ-ÖØ-öø-ÿ])([A-ZÀ-ÖØ-Þ][a-zµß-öø-ÿ]+){2,}(?![A-Za-z0-9µÀ-ÖØ-öø-ÿ]))');

    $this->acro_table = $this->__getAcronyms();
    $this->foot_notes = array();
  }

  function setOpt($option, $value)
  {
    $this->opt[$option] = $value;
  }

  function getOpt($option)
  {
    return (!empty($this->opt[$option])) ? $this->opt[$option] : false;
  }

  function transform($in)
  {
    // Initialisation des tags
    $this->__initTags();
    $this->foot_notes = array();

    // Vérification du niveau de titre
    if ($this->getOpt('first_title_level') > 4) {
      $this->setOpt('first_title_level',4);
    }

    $res = str_replace("\r", '', $in);

    $escape_pattern = array();

    // traitement des titres à la setext
    if ($this->getOpt('active_setext_title') && $this->getOpt('active_title')) {
      $res = preg_replace('/^(.*)\n[=]{5,}$/m','!!!$1',$res);
      $res = preg_replace('/^(.*)\n[-]{5,}$/m','!!$1',$res);
    }

    // Transformation des mots Wiki
    if ($this->getOpt('active_wikiwords') && $this->getOpt('words_pattern')) {
      $res = preg_replace('/'.$this->getOpt('words_pattern').'/ms','¶¶¶$1¶¶¶',$res);
    }

    $this->T = explode("\n",$res);
    $this->T[] = '';

    // Parse les blocs
    $res = $this->__parseBlocks();

    // Line break
    if ($this->getOpt('active_br')) {
      $res = preg_replace('/(?<!\\\)%%%/', '<br />', $res);
      $escape_pattern[] = '%%%';
    }

    // Correction des caractères faits par certains traitement
    // de texte comme Word
    if ($this->getOpt('active_fix_word_entities')) {
      $wR = array(
		  '' => '&#8218;',
		  '' => '&#402;',
		  '' => '&#8222;',
		  '' => '&#8230;',
		  '' => '&#8224;',
		  '' => '&#8225;',
		  '' => '&#710;',
		  '' => '&#8240;',
		  '' => '&#352;',
		  '' => '&#8249;',
		  '' => '&#338;',
		  '' => '&#8216;',
		  '' => '&#8217;',
		  '' => '&#8220;',
		  '' => '&#8221;',
		  '' => '&#8226;',
		  '' => '&#8211;',
		  '' => '&#8212;',
		  '' => '&#732;',
		  '' => '&#8482;',
		  '' => '&#353;',
		  '' => '&#8250;',
		  '' => '&#339;',
		  '' => '&#376;',
		  '' => '&#8364;');

      $res = str_replace(array_keys($wR),array_values($wR),$res);
    }

    // Nettoyage des \s en trop
    $res = preg_replace('/([\s]+)(<\/p>|<\/li>|<\/pre>)/', '$2', $res);
    $res = preg_replace('/(<li>)([\s]+)/', '$1', $res);

    // On vire les escapes
    $res = preg_replace('/\\\('.implode('|',$escape_pattern).')/','$1',$res);

    // On vire les ¶¶¶MotWiki¶¶¶ qui sont resté (dans les url...)
    if ($this->getOpt('active_wikiwords') && $this->getOpt('words_pattern')) {
      $res = preg_replace('/¶¶¶'.$this->getOpt('words_pattern').'¶¶¶/msU','$1',$res);
    }

    // On ajoute les notes
    if (count($this->foot_notes) > 0)
      {
	$res_notes = '';
	$i = 1;
	foreach ($this->foot_notes as $k => $v) {
	  $res_notes .= "\n".'<p>[<a href="#rev-'.$k.'" name="'.$k.'">'.$i.'</a>] '.$v.'</p>';
	  $i++;
	}
	$res .= sprintf("\n".$this->getOpt('note_str')."\n",$res_notes);
      }

    return $res;
  }

  /* PRIVATE
	--------------------------------------------------- */
  function __parseIndex($_index)
  {
  	$this->index=array("title"=>null,"childs"=>array());
  	$prev_level=0;
  	$ups=array();
  	
  	$ups[0] = &$this->index;
  	
  	$l=0;

  	foreach ( $_index as $entry )
  	{
  		
  		list($level,$title) = $entry;
  		
  		$l++;
  		
  		if ( $level-$prev_level > 1 )
  		{
  			for($i=$prev_level;$i<$level;$i++)
  			{
  				if (!isset($ups[$i]))	
  					$ups[$i] = &$this->index;
  			}
  		}

  		$ups[$level-1]["childs"][] = array("title"=>$title,"ancre"=>"ttl$l","childs"=>array());
  		$ups[$level] = & $ups[$level-1]["childs"][count($ups[$level-1]["childs"])-1];
  		
  	}

  }

  function __initTags()
  {
    $this->tags = array(
			'em' => array("''","''"),
			'strong' => array('__','__'),
			'acronym' => array('??','??'),
			'a' => array('[',']'),
			'img' => array('((','))'),
			'q' => array('{{','}}'),
			'code' => array('@@','@@'),
			'anchor' => array('~','~'),
			'del' => array('--','--'),
			'ins' => array('++','++'),
			'note' => array('$$','$$'),
			'word' => array('¶¶¶','¶¶¶'),
			'macro' => array('«««','»»»')
			);

    // Suppression des tags selon les options
    if (!$this->getOpt('active_urls')) {
      unset($this->tags['a']);
    }
    if (!$this->getOpt('active_img')) {
      unset($this->tags['img']);
    }
    if (!$this->getOpt('active_anchor')) {
      unset($this->tags['anchor']);
    }
    if (!$this->getOpt('active_em')) {
      unset($this->tags['em']);
    }
    if (!$this->getOpt('active_strong')) {
      unset($this->tags['strong']);
    }
    if (!$this->getOpt('active_q')) {
      unset($this->tags['q']);
    }
    if (!$this->getOpt('active_code')) {
      unset($this->tags['code']);
    }
    if (!$this->getOpt('active_acronym')) {
      unset($this->tags['acronym']);
    }
    if (!$this->getOpt('active_ins')) {
      unset($this->tags['ins']);
    }
    if (!$this->getOpt('active_del')) {
      unset($this->tags['del']);
    }
    if (!$this->getOpt('active_footnotes')) {
      unset($this->tags['note']);
    }
    if (!$this->getOpt('active_wikiwords')) {
      unset($this->tags['word']);
    }
    if (!$this->getOpt('active_macros')) {
      unset($this->tags['macro']);
    }

    $this->open_tags = $this->__getTags();
    $this->close_tags = $this->__getTags(false);
    $this->all_tags = $this->__getAllTags();
    $this->tag_pattern = $this->__getTagsPattern();

    $this->escape_table = $this->all_tags;
    array_walk($this->escape_table,create_function('&$a','$a = \'\\\\\'.$a;'));
  }

  function __getTags($open=true)
  {
    $res = array();
    foreach ($this->tags as $k => $v) {
      $res[$k] = ($open) ? $v[0] : $v[1];
    }
    return $res;
  }

  function __getAllTags()
  {
    $res = array();
    foreach ($this->tags as $v) {
      $res[] = $v[0];
      $res[] = $v[1];
    }
    return array_values(array_unique($res));
  }

  function __getTagsPattern($escape=false)
  {
    $res = $this->all_tags;
    array_walk($res,create_function('&$a','$a = preg_quote($a,"/");'));

    if (!$escape) {
      return '/(?<!\\\)('.implode('|',$res).')/';
    } else {
      return '('.implode('|',$res).')';
    }
  }

  /* Blocs
	--------------------------------------------------- */
  function __parseBlocks()
  {
  	$_index=array();
  	
    $mode = $type = NULL;
    $res = '';
    $max = count($this->T);

    for ($i=0; $i<$max; $i++)
      {
	$pre_mode = $mode;
	$pre_type = $type;
	$end = ($i+1 == $max);

	$line = $this->__getLine($i,$type,$mode);

    if ( $type == 'title' )
    {
    		if ( $type != $pre_type || $mode != $pre_mode )
    		{
    			$_index[count($_index)] = array($mode,$line);
    			$line = "<a name=\"ttl".count($_index)."\"></a>".$this->__inlineWalk($line);
    		}
    		else
    		{
    			$_index[count($_index)-1][1] .= $line;
    			$line = $this->__inlineWalk($line);
    		}	
    }
	elseif ( $type == 'table' ) {
	  $line = $this->__tableRow($line);
	}
	elseif ($type != 'pre' || $this->getOpt('parse_pre')) {
	  $line = $this->__inlineWalk($line);
	}

	$res .= $this->__closeLine($type,$mode,$pre_type,$pre_mode);
	$res .= $this->__openLine($type,$mode,$pre_type,$pre_mode);

	// P dans les blockquotes
	if ($type == 'blockquote' && trim($line) == '' && $pre_type == $type) {
	  $res .= "</p>\n<p>";
	}

	// Correction de la syntaxe FR dans tous sauf pre et hr
	// Sur idée de Christophe Bonijol
	if ($this->getOpt('active_fr_syntax') && $type != NULL && $type != 'pre' && $type != 'hr') {
	  $line = preg_replace('/ ([:?!;](\s|$))/','&#160;$1',$line);
	}

	$res .= $line;
      }
    $this->__parseIndex($_index);
    return trim($res);
  }

  function __getLine($i,&$type,&$mode)
  {
    $pre_type = $type;
    $pre_mode = $mode;
    $type = $mode = NULL;

    if (empty($this->T[$i])) {
      return false;
    }

    $line = htmlspecialchars($this->T[$i],ENT_NOQUOTES);

    // Ligne vide
    if (empty($line))
      {
	$type = NULL;
      }
    elseif ($this->getOpt('active_empty') && preg_match('/^øøø(.*)$/',$line,$cap))
      {
	$type = NULL;
	$line = trim($cap[1]);
      }
    // Titre
    elseif ($this->getOpt('active_title') && preg_match('/^([!]{1,4})(.*)$/',$line,$cap))
      {
	$type = 'title';
	$mode = strlen($cap[1]);
	$line = trim($cap[2]);
      }
    // Ligne centrée
    elseif ( preg_match('/^([~]{1})(.*)$/',$line,$cap))
      {
	$type = 'center';
	$line = trim($cap[2]);
      }      
    // Ligne HR
    elseif ($this->getOpt('active_hr') && preg_match('/^[-]{4}[- ]*$/',$line))
      {
	$type = 'hr';
	$line = NULL;
      }
    // Blockquote
    elseif ($this->getOpt('active_quote') && preg_match('/^(&gt;|;:)(.*)$/',$line,$cap))
      {
	$type = 'blockquote';
	$line = trim($cap[2]);
      }
    // Liste
    elseif ($this->getOpt('active_lists') && preg_match('/^([*#\-]+)(.*)$/',$line,$cap))
      {
	$type = 'list';
	$mode = ($cap[1]);
	$valid = true;

	// Vérification d'intégrité
	$dl = (($type != $pre_type) ? 0 : strlen($pre_mode));
	$d = strlen($mode);
	$delta = $d-$dl;

	if ($delta < 0 && strpos($pre_mode,$mode) !== 0) {
	  $valid = false;
	}
	if ($delta > 0 && $type == $pre_type && strpos($mode,$pre_mode) !== 0) {
	  $valid = false;
	}
	if ($delta == 0 && $mode != $pre_mode) {
	  $valid = false;
	}
	if ($delta > 1) {
	  $valid = false;
	}

	if (!$valid) {
	  $type = 'p';
	  $mode = NULL;
	  $line = '<br />'.$line;
	} else {
	  $line = trim($cap[2]);
	}
      }
    /* # Préformaté */
    elseif ($this->getOpt('active_pre') && preg_match('/^[ ]{1}(.*)$/',$line,$cap))
      {
	$type = 'pre';
	$line = $cap[1];
      }
    elseif (preg_match('/^\|{2}(.*)$/',$line,$cap))
      {
	$type = 'table';
	$line = $cap[1];
	
	  if (preg_match('/^\@([a-z]*)@(.*)$/',$line,$cap))
	    {
	  $mode=$cap[1];
	  $line=$cap[2];	
	    }	
	
		
      }      
      
      
    elseif (preg_match('/^={4}$/', $line))
      {
	$type = 'clear';
	$line = NULL;
      }
    // Paragraphe
    else {
      $type = 'p';
      $line = trim($line);
    }

    return $line;
  }

  function __openLine($type,$mode,$pre_type,$pre_mode)
  {
    $open = ($type != $pre_type);

    if ($open && $type == 'p')
      {
	return "\n<p>";
      }
    elseif ($open && $type == 'center')
      {
	return "\n<p class=\"center\">";
      }      
    elseif ($open && $type == 'blockquote')
      {
	return "\n<blockquote><p>";
      }
    elseif (($open || $mode != $pre_mode) && $type == 'title')
      {
	$fl = $this->getOpt('first_title_level');
	$fl = $fl+3;
	$l = $mode;
	return "\n<h".($l).'>';
      }
    elseif ($open && $type == 'pre')
      {
	return "\n<pre>";
      }
    elseif ($open && $type == 'clear')
      {
	return "\n<div style=\"clear: both;\"></div>";
      }
    elseif ($open && $type == 'hr')
      {
	return "\n<hr />";
      }
    elseif ($open && $type == 'table')
      {
      	
    if ( $mode != "" )  	
      return "<table class=\"$mode\">\n";
    else  	
      return "<table>\n";
      }  
    elseif ($type == 'list')
      {
	$dl = ($open) ? 0 : strlen($pre_mode);
	$d = strlen($mode);
	$delta = $d-$dl;
	$res = '';

	if($delta > 0) {
	  if(substr($mode, -1, 1) == '*' || substr($mode, -1, 1) == '-' ) {
	    $res .= "<ul>\n";
	  } else {
	    $res .= "<ol>\n";
	  }
	} elseif ($delta < 0) {
	  $res .= "</li>\n";
	  for($j = 0; $j < abs($delta); $j++) {
	    if (substr($pre_mode,(0 - $j - 1), 1) == '*' || substr($pre_mode,(0 - $j - 1), 1) == '-') {
	      $res .= "</ul>\n</li>\n";
	    } else {
	      $res .= "</ol>\n</li>\n";
	    }
	  }
	} else {
	  $res .= "</li>\n";
	}

	return $res."<li>";
      }
    else
      {
	return NULL;
      }
  }

  function __closeLine($type,$mode,$pre_type,$pre_mode)
  {
    $close = ($type != $pre_type);

    if ($close && $pre_type == 'p')
      {
	return "</p>\n";
      }
    elseif ($close && $pre_type == 'blockquote')
      {
	return "</p></blockquote>\n";
      }
    elseif ($close && $pre_type == 'center')
      {
	return "</p>\n";
      }      
    elseif (($close || $mode != $pre_mode) && $pre_type == 'title')
      {
	$fl = $this->getOpt('first_title_level');
	$fl = $fl+3;
	$l = $pre_mode;
	return '</h'.($l).">\n";
      }
    elseif ($close && $pre_type == 'pre')
      {
	return "</pre>\n";
      }
    elseif ($close && $pre_type == 'table')
      {
    return "</table>\n";
      }
    elseif ($close && $pre_type == 'list')
      {
	$res = '';
	for($j = 0; $j < strlen($pre_mode); $j++) {
	  if(substr($pre_mode,(0 - $j - 1), 1) == '*' || substr($pre_mode,(0 - $j - 1), 1) == '-') {
	    $res .= "</li>\n</ul>";
	  } else {
	    $res .= "</li>\n</ol>";
	  }
	}
	return $res;
      }
    else
      {
	return "\n";
      }
  }
  
  function __tableRow($row) {
    $cells = explode("||",$row);
    foreach($cells as $val)
    {
    		$val = trim($val);
    		$cap=null;
    		if (preg_match('/^([0-9]*)&gt;&gt;(.*)$/',$val,$cap) )
	    {
    			$span = $cap[1];
    			$val = $cap[2];
	    }
	    if ( $span > 1 )
	    		$r .= "<td colspan=\"$span\">".$this->__inlineWalk($val)."</td>";
	    else
        		$r .= "<td>".$this->__inlineWalk($val)."</td>";
    
    }
    return "<tr>".$r."</tr>";
  }

  /* Inline
	--------------------------------------------------- */
  function __inlineWalk($str,$allow_only=NULL)
  {
    $tree = preg_split($this->tag_pattern,$str,-1,PREG_SPLIT_DELIM_CAPTURE);

    $res = '';
    for ($i=0; $i<count($tree); $i++)
      {
	$attr = '';

	if (in_array($tree[$i],array_values($this->open_tags)) &&
	    ($allow_only == NULL || in_array(array_search($tree[$i],$this->open_tags),$allow_only)))
	  {
	    $tag = array_search($tree[$i],$this->open_tags);
	    $tag_type = 'open';

	    if (($tidy = $this->__makeTag($tree,$tag,$i,$i,$attr,$tag_type)) !== false)
	      {
		if ($tag != '') {
		  $res .= '<'.$tag.$attr;
		  $res .= ($tag_type == 'open') ? '>' : ' />';
		}
		$res .= $tidy;
	      }
	    else
	      {
		$res .= $tree[$i];
	      }
	  }
	else
	  {
	    $res .= $tree[$i];
	  }
      }

    // Suppression des echappements
    $res = str_replace($this->escape_table,$this->all_tags,$res);

    return $res;
  }

  function __makeTag(&$tree,&$tag,$position,&$j,&$attr,&$type)
  {
    $res = '';
    $closed = false;

    $itag = $this->close_tags[$tag];

    // Recherche fermeture
    for ($i=$position+1;$i<count($tree);$i++)
      {
	if ($tree[$i] == $itag)
	  {
	    $closed = true;
	    break;
	  }
      }

    // Résultat
    if ($closed)
      {
	for ($i=$position+1;$i<count($tree);$i++)
	  {
	    if ($tree[$i] != $itag)
	      {
		$res .= $tree[$i];
	      }
	    else
	      {
		switch ($tag)
		  {
		  case 'a':
		    $res = $this->__parseLink($res,$tag,$attr,$type);
		    break;
		  case 'img':
		    $type = 'close';
		    $res = $this->__parseImg($res,$attr);
		    break;
		  case 'acronym':
		    $res = $this->__parseAcronym($res,$attr);
		    break;
		  case 'q':
		    $res = $this->__parseQ($res,$attr);
		    break;
		  case 'anchor':
		    $tag = 'a';
		    $res = $this->__parseAnchor($res,$attr);
		    break;
		  case 'note':
		    $tag = '';
		    $res = $this->__parseNote($res);
		    break;
		  case 'word':
		    $res = $this->parseWikiWord($res,$tag,$attr,$type);
		    break;
		  case 'macro':
		    $res = $this->parseMacro($res,$tag,$attr,$type);
		    break;
		  default :
		    $res = $this->__inlineWalk($res);
		    break;
		  }

		if ($type == 'open' && $tag != '') {
		  $res .= '</'.$tag.'>';
		}
		$j = $i;
		break;
	      }
	  }

	return $res;
      }
    else
      {
	return false;
      }
  }

  function __parseLink($str,&$tag,&$attr,&$type)
  {
    $n_str = $this->__inlineWalk($str,array('acronym','img'));
    $data = explode('|',$n_str);
    $no_image = false;

    if (count($data) == 1)
      {
	$url = trim($str);
	$content = $str;
	$lang = '';
	$title = '';
      }
    elseif (count($data) > 1)
      {
	$url = trim($data[1]);
	$content = $data[0];
	$lang = (!empty($data[2])) ? $this->protectAttr($data[2],true) : '';
	$title = (!empty($data[3])) ? $data[3] : '';
	$no_image = (!empty($data[4])) ? (boolean) $data[4] : false;
      }

    $array_url = $this->__specialUrls();
    $url = preg_replace(array_flip($array_url),$array_url,$url);

    // On vire les &nbsp; dans l'url
    $url = str_replace('&nbsp;',' ',$url);

    if (ereg('^(.+)[.](gif|jpg|jpeg|png)$', $url) && !$no_image && $this->getOpt('active_auto_img'))
      {
	// On ajoute les dimensions de l'image si locale
	// Idée de Stephanie
	$img_size = NULL;
	if (!ereg('[a-zA-Z]+://', $url)) {
	  if (ereg('^/',$url)) {
	    $path_img = $_SERVER['DOCUMENT_ROOT'] . $url;
	  } else {
	    $path_img = $url;
	  }

	  $img_size = @getimagesize($path_img);
	}

	$attr = ' src="'.$this->protectAttr($this->protectUrls($url)).'"'.
	  $attr .= (count($data) > 1) ? ' alt="'.$this->protectAttr($content).'"' : ' alt=""';
	$attr .= ($lang) ? ' lang="'.$lang.'"' : '';
	$attr .= ($title) ? ' title="'.$this->protectAttr($title).'"' : '';
	$attr .= (is_array($img_size)) ? ' '.$img_size[3] : '';

	$tag = 'img';
	$type = 'close';
	return NULL;
      }
    else
      {
	$attr = ' href="'.$this->protectAttr($this->protectUrls($url)).'"';
	$attr .= ($lang) ? ' hreflang="'.$lang.'"' : '';
	$attr .= ($title) ? ' title="'.$this->protectAttr($title).'"' : '';

	return $content;
      }
  }

  function __specialUrls()
  {
  	global $topdir;
    $res['#^google://(.*)$#'] = 'http://www.google.com/search?q=$1&amp;start=0&amp;start=0';
    $res['#^wikipedia://(.*)$#'] = 'http://en.wikipedia.org/wiki/$1';
    $res['#^article://(.*)$#'] = $topdir.'article.php?name=$1';
    $res['#^dfile://([0-9]*)$#'] = $topdir.'d.php?id_file=$1&amp;action=download';
    $res['#^dfile://([0-9]*)/preview$#'] = $topdir.'d.php?id_file=$1&amp;action=download&amp;download=preview';
    $res['#^dfile://([0-9]*)/thumb$#'] = $topdir.'d.php?id_file=$1&amp;action=download&amp;download=thumb';
    $res['#^dfolder://([0-9]*)$#'] = $topdir.'d.php?id_folder=$1';
    $res['#^mailto:(.*)$#'] = 'mailto:$1';

    return $res;
  }

  function __parseImg($str,&$attr)
  {
    $data = explode('|',$str);

    $alt = '';
    $url = $data[0];
    if (!empty($data[1])) {
      $alt = $data[1];
    }
    
	$array_url = $this->__specialUrls();
    $url = preg_replace(array_flip($array_url),$array_url,$url);
    
    $attr = ' src="'.$this->protectAttr($this->protectUrls($url)).'"';
    $attr .= ' alt="'.$this->protectAttr($alt).'"';

    if (!empty($data[2])) {
      if ($data[2] == 'G' || $data[2] == 'L') {
	$attr .= ' class="imgleft"';
      } elseif ($data[2] == 'D' || $data[2] == 'R') {
	$attr .= ' class="imgright"';
      }
    }

    if (!empty($data[3])) {
      $attr .= ' longdesc="'.$this->protectAttr($data[3]).'"';
    }

    return NULL;
  }

  function __parseQ($str,&$attr)
  {
    $str = $this->__inlineWalk($str);
    $data = explode('|',$str);

    $content = $data[0];
    $lang = (!empty($data[1])) ? $this->protectAttr($data[1],true) : '';

    $attr .= (!empty($lang)) ? ' lang="'.$lang.'"' : '';
    $attr .= (!empty($data[2])) ? ' cite="'.$this->protectAttr($data[2]).'"' : '';

    return $content;
  }

  function __parseAnchor($str,&$attr)
  {
    $name = $this->protectAttr($str,true);

    if ($name != '') {
      $attr = ' name="'.$name.'"';
    }
    return null;
  }

  function __parseNote($str)
  {
    $i = count($this->foot_notes)+1;
    $id = $this->getOpt('note_prefix').'-'.$i;
    $this->foot_notes[$id] = $this->__inlineWalk($str);
    return '<sup>\[<a href="#'.$id.'" name="rev-'.$id.'">'.$i.'</a>\]</sup>';
  }

  // Obtenir un acronyme
  function __parseAcronym($str,&$attr)
  {
    $data = explode('|',$str);

    $acronym = $data[0];
    $title = $lang = '';

    if (count($data) > 1)
      {
	$title = $data[1];
	$lang = (!empty($data[2])) ? $this->protectAttr($data[2],true) : '';
      }

    if ($title == '' && !empty($this->acro_table[$acronym])) {
      $title = $this->acro_table[$acronym];
    }

    $attr = ($title) ? ' title="'.$this->protectAttr($title).'"' : '';
    $attr .= ($lang) ? ' lang="'.$lang.'"' : '';

    return $acronym;
  }

  // Définition des acronymes, dans le fichier acronyms.txt
  function __getAcronyms()
  {
    $file = dirname(__FILE__).'/acronyms.txt';
    $res = array();

    if (file_exists($file))
      {
	if (($fc = @file($file)) !== false)
	  {
	    foreach ($fc as $v)
	      {
		$v = trim($v);
		if ($v != '')
		  {
		    $p = strpos($v,':');
		    $K = (string) trim(substr($v,0,$p));
		    $V = (string) trim(substr($v,($p+1)));

		    if ($K) {
		      $res[$K] = $V;
		    }
		  }
	      }
	  }
      }

    return $res;
  }

  // Mots wiki (pour héritage)
  function parseWikiWord($str,&$tag,&$attr,&$type)
  {
    $tag = $attr = '';
    return $str;
  }

  // Macros (pour héritage)
  function parseMacro($str,&$tag,&$attr,&$type)
  {
    $tag = $attr = '';
    
    if ( $str == "site-rev" ) return get_rev();
    
    return $str;
  }

  /* Protection des attributs */
  function protectAttr($str,$name=false)
  {
    if ($name && !preg_match('/^[A-Za-z][A-Za-z0-9_:.-]*$/',$str)) {
      return '';
    }

    return str_replace(array("'",'"'),array('&#039;','&quot'),$str);
  }

  /* Protection des urls */
  function protectUrls($str)
  {
    if (preg_match('/^javascript:/',$str)) {
      $str = '#';
    }

    return $str;
  }

  /* Aide et debug
	--------------------------------------------------- */
  function help()
  {
    $help['b'] = array();
    $help['i'] = array();

    $help['b'][] = 'Laisser une ligne vide entre chaque bloc <em>de même nature</em>.';
    $help['b'][] = '<strong>Paragraphe</strong> : du texte et une ligne vide';

    if ($this->getOpt('active_title')) {
      $help['b'][] = '<strong>Titre</strong> : <code>!!!</code>, <code>!!</code>, '.
	'<code>!</code> pour des titres plus ou moins importants';
    }

    if ($this->getOpt('active_hr')) {
      $help['b'][] = '<strong>Trait horizontal</strong> : <code>----</code>';
    }

    if ($this->getOpt('active_lists')) {
      $help['b'][] = '<strong>Liste</strong> : ligne débutant par <code>*</code> ou '.
	'<code>#</code>. Il est possible de mélanger les listes '.
	'(<code>*#*</code>) pour faire des listes de plusieurs niveaux. '.
	'Respecter le style de chaque niveau';
    }

    if ($this->getOpt('active_pre')) {
      $help['b'][] = '<strong>Texte préformaté</strong> : espace devant chaque ligne de texte';
    }

    if ($this->getOpt('active_quote')) {
      $help['b'][] = '<strong>Bloc de citation</strong> : <code>&gt;</code> ou '.
	'<code>;:</code> devant chaque ligne de texte';
    }

    if ($this->getOpt('active_fr_syntax')) {
      $help['i'][] = 'La correction de ponctuation est active. Un espace '.
	'insécable remplacera automatiquement tout espace '.
	'précédant les marque ";","?",":" et "!".';
    }

    if ($this->getOpt('active_em')) {
      $help['i'][] = '<strong>Emphase</strong> : deux apostrophes <code>\'\'texte\'\'</code>';
    }

    if ($this->getOpt('active_strong')) {
      $help['i'][] = '<strong>Forte emphase</strong> : deux soulignés <code>__texte__</code>';
    }

    if ($this->getOpt('active_br')) {
      $help['i'][] = '<strong>Retour forcé à la ligne</strong> : <code>%%%</code>';
    }

    if ($this->getOpt('active_ins')) {
      $help['i'][] = '<strong>Insertion</strong> : deux plus <code>++texte++</code>';
    }

    if ($this->getOpt('active_del')) {
      $help['i'][] = '<strong>Suppression</strong> : deux moins <code>--texte--</code>';
    }

    if ($this->getOpt('active_urls')) {
      $help['i'][] = '<strong>Lien</strong> : <code>[url]</code>, <code>[nom|url]</code>, '.
	'<code>[nom|url|langue]</code> ou <code>[nom|url|langue|titre]</code>.';

    }

    if ($this->getOpt('active_img')) {
      $help['i'][] = '<strong>Image</strong> : '.
	'<code>((url|texte alternatif))</code>, '.
	'<code>((url|texte alternatif|position))</code> ou '.
	'<code>((url|texte alternatif|position|description longue))</code>. '.
	'<br />La position peut prendre les valeur L ou G (gauche) ou R ou D (droite).';
    }

    if ($this->getOpt('active_anchor')) {
      $help['i'][] = '<strong>Ancre</strong> : <code>~ancre~</code>';
    }

    if ($this->getOpt('active_acronym')) {
      $help['i'][] = '<strong>Acronyme</strong> : <code>??acronyme??</code> ou '.
	'<code>??acronyme|titre??</code>';
    }

    if ($this->getOpt('active_q')) {
      $help['i'][] = '<strong>Citation</strong> : <code>{{citation}}</code>, '.
	'<code>{{citation|langue}}</code> ou <code>{{citation|langue|url}}</code>';
    }

    if ($this->getOpt('active_code')) {
      $help['i'][] = '<strong>Code</strong> : <code>@@code ici@@</code>';
    }

    if ($this->getOpt('active_footnotes')) {
      $help['i'][] = '<strong>Note de bas de page</strong> : <code>$$Corps de la note$$</code>';
    }

    $res .= '<h2>Blocs</h2>';
    if (count($help['b']) > 0)
      {
	$res .= '<ul><li>';
	$res .= implode('&nbsp;;</li><li>', $help['b']);
	$res .= '.</li></ul>';
      }

    $res .= '<h2>Éléments en ligne</h2>';
    if (count($help['i']) > 0)
      {
	$res .= '<ul><li>';
	$res .= implode('&nbsp;;</li><li>', $help['i']);
	$res .= '.</li></ul>';
      }

    return $res;
  }

  /*
	function debug()
	{
		$mode = $type = NULL;
		$max = count($this->T);

		$res =
		'<table border="1">'.
		'<tr><th>p-mode</th><th>p-type</th><th>mode</th><th>type</th><th>chaine</th></tr>';

		for ($i=0; $i<$max; $i++)
		{
			$pre_mode = $mode;
			$pre_type = $type;

			$line = $this->__getLine($i,$type,$mode);

			$res .=
			'<tr><td>'.$pre_mode.'</td><td>'.$pre_type.'</td>'.
			'<td>'.$mode.'</td><td>'.$type.'</td><td>'.$line.'</td></tr>';

		}
		$res .= '</table>';

		return $res;
	}
  //*/
}

?>