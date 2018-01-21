<?php

function ListVideos(){

	$chosen_day = $_GET['day'];

	$videos = array();
	foreach (glob("/home/pi/allsky/images/$chosen_day/*.mp4") as $video) {
	  $videos[] = $video;
	}


	echo "<h2>Videos - $chosen_day</h2>
	  <div class='row'>";

        foreach ($videos as $video) {
		$video_name = basename($video);
                echo "<a href='/images/$chosen_day/$video_name'>
                        <div style='float: left'>
                        <img src='../img/video-icon.png' style='height: 76px;'/> 
                        </div></a>";
        }
  	echo "</div>";
}
?>

