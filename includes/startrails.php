<?php

function ListStartrails(){

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

		echo "<h2>Startrails - $chosen_day</h2>
                  <div class='row'>";

		foreach ($days as $day) {
			$startrails = array();
			foreach (glob("/home/pi/allsky/images/$day/startrails/startrails-$day.jpg") as $startrail) {
				$startrails[] = $startrail;
			}
		        foreach ($startrails as $startrail) {
		                $startrail_name = basename($startrail);
		                echo "<a href='/images/$day/startrails/$startrail_name'>
		                        <div style='float: left; width: 100%'>
					<label>$day</label>
		                        <img src='/images/$day/startrails/$startrail_name' style='margin-left: 10px; max-width: 50%; max-height:100px'/>
		                        </div></a>";
		        }
		}
	        echo "</div>";

	} else {
		foreach (glob("/home/pi/allsky/images/$chosen_day/startrails/startrails-$chosen_day.jpg") as $startrail) {
			  $startrails[] = $startrail;
		}
		echo "<h2>Startrails - $chosen_day</h2>
	          <div class='row'>";
        	foreach ($startrails as $startrail) {
	                $startrail_name = basename($startrail);
	                echo "<a href='/images/$chosen_day/startrails/$startrail_name'>
	                        <div style='float: left'>
	                        <img src='/images/$chosen_day/startrails/$startrail_name' style='max-width: 100%;max-height:400px'/>
	                        </div></a>";
	        }
	        echo "</div>";

	}

}
?>
