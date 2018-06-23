<?php

function ListImages(){

$images = array();
$chosen_day = $_GET['day'];


if ($handle = opendir('/home/pi/allsky/images/'.$chosen_day)) {
    $blacklist = array('.', '..', '*.mp4', 'startrails', 'keogram', 'thumbnails');
    while (false !== ($image = readdir($handle))) {
	$ext = explode(".",$image);
        if (!in_array($image, $blacklist) && $ext[1]!='mp4') {
            $images[] = $image;
        }
    }
    closedir($handle);
}

asort($images);

?>

<?php
echo "<h2>$chosen_day</h2>
  <div class='row'>";

foreach ($images as $image) {
	echo "<a href='/images/$chosen_day/$image'>
			<div style='float: left'>";
	if(file_exists("/home/pi/allsky/images/$chosen_day/thumbnails/$image"))
		echo "<img src='/images/$chosen_day/thumbnails/$image' style='width: 100px;'/>";
	else
		echo "<img src='/images/$chosen_day/$image' style='width: 100px;'/>";
	echo "</div>
		</a>";
}
?>
  </div>
  <?php 
}

?>
