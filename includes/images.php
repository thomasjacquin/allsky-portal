<?php

function ListImages(){

$images = array();
$chosen_day = $_GET['day'];


if ($handle = opendir('/home/pi/allsky/images/'.$chosen_day)) {
    $blacklist = array('.', '..', 'somedir', 'somefile.php');
    while (false !== ($image = readdir($handle))) {
        if (!in_array($image, $blacklist)) {
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
	$ext = explode(".",$image);
	if($ext[1] != 'mp4')
		echo "<img src='/images/$chosen_day/$image' style='width: 100px;'/>";
	else
		echo "<img src='https://cdn2.iconfinder.com/data/icons/freecns-cumulus/16/519539-085_Movie-128.png' style='height: 76px;'/>";
	echo "</div>
		</a>";
}
?>
  </div>
  <?php 
}

?>
