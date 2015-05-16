<?php

/**
 * Permet de mettre le rendu HTML d'un stdcontents en cache.
 *
 * L'expiration des données mises en cache doit se faire par appel de expire().
 *
 * Cette classe utilise l'instance Redis pour stocker ses valeurs, l'uid devenant la clé
 * Dans notre cas, toutes les données cachées sont stockés dans la base 1
 */

require_once ($topdir.'include/redis.inc.php');

class cachedcontents extends stdcontents
{
    protected $uid;
    private $redis;


    public function cachedcontents ( $uid )
    {
        $this->uid = strval($uid);
        $this->redis = $this->get_redis_instance ();
    }

    public function expire ( )
    {
        $this->redis->del ($this->uid);
    }

    public function is_cached()
    {
	if($this->redis != NULL)
            return $this->redis->exists($this->uid) && !isset($_GET["__nocache"]);
	return false;
    }

    public function get_cache()
    {
        $data = $this->redis->get ($this->uid);

        if ($data == null || $data == '')
            return null;

        $p1 = strpos($data,"\n");

        $this->title  = substr($data,0,$p1);
        $this->buffer = substr($data,$p1+1);

        unset($data);

        return $this;
    }

    public function set_contents ( &$contents )
    {
        $this->title = $contents->title;
        $this->buffer = "<!-- C".date ("d/m/Y H:i:s")." -->".$contents->html_render();

	if($contents->is_cachable() && $this->redis != NULL)
        	$this->redis->set ($this->uid, $this->title."\n".$this->buffer);
        return $this;
    }

    public function set_contents_timeout ( &$contents, $timestamp )
    {
	if($this->redis != NULL) {
            $this->set_contents ($contents);
            $this->redis->expireAt ($this->uid, $timestamp);

            return $this;
	} else {
	    return false;
        }
    }

    public function set_contents_until ( &$contents, $seconds )
    {
	if($this->redis != NULL) {
            $this->set_contents ($contents);
            $this->redis->expire ($this->uid, $seconds);

            return $this;
	} else {
	    return false;
        }
    }

    private function get_redis_instance ()
    {
        $redis = redis_open_connection ();
	if($redis != NULL)
            $redis->select (1);
        return $redis;    
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
