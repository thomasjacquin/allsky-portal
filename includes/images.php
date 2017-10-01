<?php

function ListImages(){

$images = array();
$chosen_day = $_GET['day'];


if ($handle = opendir('/home/thomas/dev/allsky/images/'.$chosen_day)) {
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
			<div style='float: left'>
				<img src='/images/$chosen_day/$image' style='width: 100px;'/>
			</div>
		</a>";
}
?>
  </div>
  <?php 
}

?>
