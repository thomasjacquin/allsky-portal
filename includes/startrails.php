<?php

function ListStartrails(){

	$chosen_day = $_GET['day'];

	$startrails = array();
	foreach (glob("/home/pi/allsky/images/$chosen_day/startrails/startrails-$chosen_day.jpg") as $startrail) {
	  $startrails[] = $startrail;
	}


	echo "<h2>Startrails - $chosen_day</h2>
	  <div class='row'>";

        foreach ($startrails as $startrail) {
		$startrail_name = basename($startrail);
                echo "<a href='/images/$chosen_day/startrails/$startrail_name'>
                        <div style='float: left'>
                        <img src='/images/$chosen_day/startrails/$startrail_name' style='width: 100%;'/> 
                        </div></a>";
        }
  	echo "</div>";
}
?>

