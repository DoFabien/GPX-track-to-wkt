<?php

// return wkt, 'distance', 'd_plus', 'd_moins', 'denivele'
function parseGpx($gpxString){
function distance($lat1, $lon1, $lat2, $lon2, $alt1, $alt2)  //calcul la distance en km entre 2 points GPS
	{
		$r = 6366;
		$lat1 = deg2rad($lat1);
		$lat2 = deg2rad($lat2);
		$lon1 = deg2rad($lon1);
		$lon2 = deg2rad($lon2);
		$alt1 = $alt1/1000;
		$alt2 = $alt2/1000;
		$dp= 2 * asin(sqrt(pow (sin(($lat1-$lat2)/2) , 2) + cos($lat1)*cos($lat2)* pow( sin(($lon1-$lon2)/2) , 2)));
		$d = $dp * $r;
		 $h = sqrt(pow($d,2)+pow($alt2-$alt1,2));
		return $h;
	}

$gpx = simplexml_load_string($gpxString);

$coords_gpx = $gpx->trk->trkseg->trkpt; //=> liste des points du track //  https://fr.wikipedia.org/wiki/GPX_(format_de_fichier) 

$array_pre_wkt = array(); // tableau vide qu'on va remplir avec : lng , lat  (format de coordonnée du wkt)
$distance = 0;
$d_plus = 0;
$d_moins = 0;


for ($i = 0; $i< count($coords_gpx); $i++){ 
    $pt = $coords_gpx[$i];
    if ($i > 0){
        $pt_prec = $coords_gpx[$i - 1];
        $lat1 = (float) $pt_prec['lat'];
        $lon1 = (float) $pt_prec['lon'];
        $alt1 = (float) $pt_prec->ele;
        
        $lat2 = (float) $pt['lat'];
        $lon2 = (float) $pt['lon'];
        $alt2 = (float) $pt->ele;
        
        $distance = $distance + distance($lat1, $lon1, $lat2, $lon2, $alt1, $alt2);    
        //d+ et d-
        if ($alt1 < $alt2){
            $d_plus = $d_plus + ($alt2 - $alt1);
        }  
        else {
            $d_moins = $d_moins + ($alt2 - $alt1);
        }    
    }
    
	$lon = (string) $pt['lon']; 
	$lat = (string) $pt['lat'];
	$ele = (string) $pt->ele; //elevation que nous n'utilisons pas
	$coords_wkt = $lon . ' ' . $lat;
	array_push($array_pre_wkt, $coords_wkt);
}


$denivele = ((float)$coords_gpx[count($coords_gpx)-1]->ele ) -  ((float)$coords_gpx[0]->ele );
$wkt_str = 'LINESTRING(' . implode(',',$array_pre_wkt) . ')'; //// le WKT à pousser dans la base

$obj = (object) array('wkt' => $wkt_str, 'distance' => $distance, 'd_plus' => $d_plus, 'd_moins' => $d_moins, 'denivele'=>$denivele );
 return $obj;
}

?>