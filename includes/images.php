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

<link  href="css/viewer.min.css" rel="stylesheet">
<script src="js/viewer.min.js"></script>
<script src="js/jquery-viewer.min.js"></script>

<script>
$( document ).ready(function() {
        $('#images').viewer({
		url(image) {
                	return image.src.replace('/thumbnails', '/');
        	},
		transition: false
	});
        $('.thumb').each(function(){		
			this.title=getTimeStamp(this.src);
	});
});

function getTimeStamp(url)
{
	var filename = url.substring(url.lastIndexOf('/')+1);
	var timeStamp = filename.substring(6);
	var year = timeStamp.substring(0, 4);
	var month = timeStamp.substring(4, 6);
	var day = timeStamp.substring(6, 8);
	var hour = timeStamp.substring(8, 10);
	var minute = timeStamp.substring(10, 12);
	var seconds = timeStamp.substring(12, 14);
	var date = new Date(year, month, day, hour, minute, seconds, 0);
	return date.toDateString() + " @ " + date.getHours() + ":"+date.getMinutes() + ":"+date.getSeconds();
}
</script>

<?php
echo "<h2>$chosen_day</h2>
  <div class='row'>";

echo "<div id='images'>";
foreach ($images as $image) {
	echo "<div style='float: left'>";
	if(file_exists("/home/pi/allsky/images/$chosen_day/thumbnails/$image"))
		echo "<img src='/images/$chosen_day/thumbnails/$image' style='width: 100px;' title='$image' class='thumb'/>";
	else
		echo "<img src='/images/$chosen_day/$image' style='width: 100px;'/>";
	echo "</div>";
}
?>
	</div>
  </div>
  <?php 
}
?>
