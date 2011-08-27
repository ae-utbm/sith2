<?php

define("CACHE_DIR",$topdir."var/cache/contents/");


/**
 * Permet de mettre le rendu HTML d'un stdcontents en cache.
 *
 * L'expiration des données mises en cache doit se faire par appel de expire().
 *
 */
class cachedcontents extends stdcontents
{
  protected $uid;


  public function cachedcontents ( $uid )
  {
    $this->uid = $uid;
  }

  public function expire ( )
  {
    if ( $this->is_cached() )
      unlink(CACHE_DIR.$this->uid);
  }

  public function is_cached()
  {
    return file_exists(CACHE_DIR.$this->uid) && !isset($_GET["__nocache"]);
  }

  public function is_cached_since($timestamp)
  {
    return file_exists(CACHE_DIR.$this->uid) && filemtime(CACHE_DIR.$this->uid) > $timestamp && !isset($_GET["__nocache"]);
  }

  public function get_cache()
  {
    if ( !$this->is_cached() )
      return null;

    $data = file_get_contents(CACHE_DIR.$this->uid);
    $p1 = strpos($data,"\n");

    $this->title  = substr($data,0,$p1);
    $this->buffer = substr($data,$p1+1);

    unset($data);

    return $this;
  }

  public function set_contents ( &$contents )
  {
    if ( !file_exists(CACHE_DIR) && is_writable(CACHE_DIR."../") )
      mkdir(CACHE_DIR);

    $this->title = $contents->title;
    $this->buffer =
      "<!-- C".date ("d/m/Y H:i:s")." -->".$contents->html_render();
    if ( is_writable(CACHE_DIR) )
      file_put_contents(CACHE_DIR.$this->uid,$this->title."\n".$this->buffer);
    return $this;
  }

  /**
   * Mise en cache automatique d'un stdcontent générant son code HTML uniquement
   * lors de l'appel à html_render().
   * C'est aussi un bon exemple de l'usage de cachedcontents.
   * @param $uid Identifiant Unique du contenu
   * @param $cts Contents
   */
  public static function autocache ( $uid, $cts )
  {
    $cache = new cachedcontents($uid);
    if ( $cache->is_cached() )
      return $cache->get_cache();
    $cache->set_contents($cts);
    return $cache;
  }

}

?>
