<?php
/**
 * @file Base de donnés externe : WEATHER.COM. 
 * Météo sur 3 jours.
 */

/* Copyright 2006
 * - Julien Etelain <julien CHEZ pmad POINT net>
 *
 * Ce fichier fait partie du site de l'Association des étudiants de
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
require_once("xml.inc.php");
 
/** Obtient la météo sur 3 jours depuis weather.com (flux rss)
 * @param $citycode Code de la ville (FRXX0012 pour belfort)
 * @return array(array("day"=>,"weather"=>,"max"=>,"min"=>)) Les temperatures sont en Celsius
 */
function weathercom_get_weather($citycode="FRXX0012") 
{
	$data = file_get_contents("http://rss.weather.com/weather/rss/local/$citycode?cm_ven=LWO&cm_cat=rss&par=LWO_rss");	
	$xml = new u007xml($data);
	$regs=null;
	foreach ( $xml->arrOutput[0]["childrens"][0]["childrens"] as $item )
	if ( is_array($item["childrens"]) )
	foreach ( $item["childrens"] as $element )
	if ( $element["nodename"] == "TITLE" && eregi("Your 10-Day Forecast for",$element["nodevalue"])   )
	foreach ( $item["childrens"] as $selement )
	if ( $selement["nodename"] == "DESCRIPTION" )
	{
		$data = explode("----",$selement["nodevalue"])	;
		$weather=array();	
		foreach($data as $info)
		{
			if ( ereg("([a-zA-Z]+): ([a-zA-Z \-]+)& High (([0-9\-]+)&deg;F|N/AF) / Low (([0-9\-]+)&deg;F|N/AF).",$info,$regs))
			{	
				$dinfo=array();
				$dinfo["day"]=$regs[1];
				$dinfo["weather"]=$regs[2];
				if ( $regs[3] =="N/AF" )
					$dinfo["max"]="N/A";
				else
					$dinfo["max"]=round(5*($regs[4]-32)/9);
					
				if ( $regs[4] =="N/AF" )
					$dinfo["min"]="N/A";
				else	
					$dinfo["min"]=round(5*($regs[6]-32)/9);
				$weather[]=$dinfo;
			}
			elseif ( ereg("([a-zA-Z]+): AM([a-zA-Z \-]+)/ PM([a-zA-Z \-]+)& High (([0-9\-]+)&deg;F|N/AF) / Low (([0-9\-]+)&deg;F|N/AF).",$info,$regs))
			{	
				
				$dinfo=array();
				$dinfo["day"]=$regs[1];
				$dinfo["weather"]=$regs[3];
				if ( $regs[3] =="N/AF" )
					$dinfo["max"]="N/A";
				else
					$dinfo["max"]=round(5*($regs[5]-32)/9);
					
				if ( $regs[4] =="N/AF" )
					$dinfo["min"]="N/A";
				else	
					$dinfo["min"]=round(5*($regs[7]-32)/9);

				$weather[]=$dinfo;
			}
		}			
		return $weather;
	}	
	return null;
}

/** Obtient la météo sur 3 jours depuis weather.com (flux rss) (utilise un cache)
 * @param $citycode Code de la ville (FRXX0012 pour belfort)
 * @return array(array("day"=>,"weather"=>,"max"=>,"min"=>)) Les temperatures sont en Celsius
 */
function cache_get_weather($citycode="FRXX0012") 
{
	global $topdir;
	
	$file = $topdir."cache/weather/".$citycode;
	
	if ( !file_exists($file) || time() > filemtime($file) + (1 * 60 * 60) )
	{
		$w = weathercom_get_weather($citycode);
		if ($f = fopen($file, 'w')) {
			fwrite($f, serialize($w));
			fclose($f);
		}
		return $w;
	}
	return unserialize(file_get_contents($file));
}

weathercom_get_weather();

?>
