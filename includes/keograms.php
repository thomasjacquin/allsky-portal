<?php

function ListKeograms(){

	$chosen_day = $_GET['day'];

	if ($chosen_day === 'all'){

		if ($handle = opendir('/home/pi/allsky/images/')) {
		    $blacklist = array('.', '..', 'somedir', 'somefile.php');
		    while (false !== ($day = readdir($handle))) {
		        if (!in_array($day, $blacklist)) {
		            $days[] = $day;
		        }
		    }
		    closedir($handle);
		}

		rsort($days);

		echo "<h2>Keogram - $chosen_day</h2>
                  <div class='row'>";

		foreach ($days as $day) {
			$keograms = array();
			foreach (glob("/home/pi/allsky/images/$day/keogram/keogram-$day.*") as $keogram) {
				$keograms[] = $keogram;
			}
		        foreach ($keograms as $keogram) {
		                $keogram_name = basename($keogram);
		                echo "<a href='/images/$day/keogram/$keogram_name'>
		                        <div style='float: left; width: 100%'>
					<label>$day</label>
		                        <img src='/images/$day/keogram/$keogram_name' style='margin-left: 10px; max-width: 50%; max-height:100px'/>
		                        </div></a>";
		        }
		}
	        echo "</div>";

	} else {
		foreach (glob("/home/pi/allsky/images/$chosen_day/keogram/keogram-$chosen_day.*") as $keogram) {
			  $keograms[] = $keogram;
		}
		echo "<h2>Keogram - $chosen_day</h2>
	          <div class='row'>";
        	foreach ($keograms as $keogram) {
	                $keogram_name = basename($keogram);
	                echo "<a href='/images/$chosen_day/keogram/$keogram_name'>
	                        <div style='float: left'>
	                        <img src='/images/$chosen_day/keogram/$keogram_name' style='max-width: 100%;max-height:400px'/>
	                        </div></a>";
	        }
	        echo "</div>";

	}

}
?>
