<?php  
include("thumbnail.php");
  
$fileName = (isset($_GET['file'])) ? $_GET['file'] : null;

//$tn_image = new Thumbnail("../$fileName", 200, 200, 0);
$tn_image = new Thumbnail("$fileName", 200, 200, 0);
$tn_image->show(); 
?>
