<?php

function ListVideos(){

	$chosen_day = $_GET['day'];

	$videos = array();
	foreach (glob("/home/pi/allsky/images/$chosen_day/*.mp4") as $video) {
	  $videos[] = $video;
	}


	echo "<h2>Timelapse - $chosen_day</h2>
	  <div class='row'>";

        foreach ($videos as $video) {
		$video_name = basename($video);
		echo "<video width='640' height='480' controls>
  			<source src='/images/$chosen_day/$video_name' type='video/mp4'>
  			<source src='movie.ogg' type='video/ogg'>
  			Your browser does not support the video tag.
		     </video>";
        }
  	echo "</div>";
}
?>

