<?php

function ListKeograms(){

	$chosen_day = $_GET['day'];

	$keograms = array();
	foreach (glob("/home/pi/allsky/images/$chosen_day/keogram-$chosen_day.jpg") as $keogram) {
	  $keograms[] = $keogram;
	}


	echo "<h2>Keogram - $chosen_day</h2>
	  <div class='row'>";

        foreach ($keograms as $keogram) {
		$keogram_name = basename($keogram);
                echo "<a href='/images/$chosen_day/$keogram_name'>
                        <div style='float: left'>
                        <img src='/images/$chosen_day/$keogram_name' style='width: 100%;'/> 
                        </div></a>";
        }
  	echo "</div>";
}
?>

