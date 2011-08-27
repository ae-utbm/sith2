<?php

/**
 * Permet de mettre le rendu HTML d'un stdcontents en cache.
 *
 * L'expiration des données mises en cache doit se faire par appel de expire().
 *
 * Cette classe utilise l'instance Redis pour stocker ses valeurs, l'uid devenant la clé
 * Dans notre cas, toutes les données cachées sont stockés dans la base 1
 */
class cachedcontents extends stdcontents
{
    protected $uid;

    public function cachedcontents ( $uid )
    {
        $this->uid = strval($uid);
    }

    public function expire ( )
    {
        $redis = $this->get_redis_instance ();
        $redis->del ($this->uid);
    }

    public function is_cached()
    {
        $redis = $this->get_redis_instance ();
        return $redis->exists($this->uid) && !isset($_GET["__nocache"]);
    }

    public function get_cache()
    {
        $redis = $this->get_redis_instance ();
        $data = $redis->get ($this->uid);

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

        $redis = $this->get_redis_instance ();
        $redis->set ($this->uid, $this->title."\n".$this->buffer);
        return $this;
    }

    public function set_contents_timeout ( &$contents, $timestamp )
    {
        $this->set_contents ($contents);
        $redis = $this->get_redis_instance ();
        $redis->expireAt ($this->uid, $timestamp);

        return $this;
    }

    private function get_redis_instance ()
    {
        $redis = new Redis ();
        $redis->popen ('127.0.0.1');
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
