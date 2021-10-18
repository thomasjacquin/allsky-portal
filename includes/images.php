<?php

function ListImages(){

$images = array();
$chosen_day = $_GET['day'];
$num = 0;	// Keep track of count so we can tell user when no files exist.
$nav_images_max = 50;	// hide the navigation bar if more than this number of images are displayed.


if ($handle = opendir(ALLSKY_IMAGES . '/'.$chosen_day)) {
    $blacklist = array('.', '..', '*.mp4', 'startrails', 'keogram', 'thumbnails');
    while (false !== ($image = readdir($handle))) {
	$ext = explode(".",$image);
        if (!in_array($image, $blacklist) && $ext[1]!='mp4') {
            $images[] = $image;
	    $num += 1;
        }
    }
    closedir($handle);
}

if ($num > 0) asort($images);

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
		<?php if ($num > $nav_images_max) echo "navbar: 0,"; // if there are a lot of images it takes forever to display the navbar. ?>
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
	var date = new Date(year, month-1, day, hour, minute, seconds, 0);
	return date.toDateString() + " @ " + hour + ":"+minute + ":"+seconds;
}
</script>

<?php
echo "<h2>$chosen_day</h2>
  <div class='row'>";

echo "<div id='images'>";
if ($num == 0) {
	echo "<span class='alert-warning'>There are no images for this day.</span>";
} else {
	foreach ($images as $image) {
		echo "<div style='float: left'>";
		if(file_exists(ALLSKY_IMAGES . "/$chosen_day/thumbnails/$image"))
			// "/images" is an alias for ALLSKY_IMAGES in lighttpd
			echo "<img src='/images/$chosen_day/thumbnails/$image' style='width: 100px;' title='$image' class='thumb'/>";
		else
			echo "<img src='/images/$chosen_day/$image' style='width: 100px;'/>";
		echo "</a>";
		echo "</div>";
	}
}
?>
	</div>
  </div>
  <?php 
}
?>
