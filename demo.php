<?php
require_once('./parseGpx.php');

$gpxStr = file_get_contents('visugpx_exemple.gpx');
print_r (parseGpx($gpxStr));

?>