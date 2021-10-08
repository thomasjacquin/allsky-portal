<?php

function DisplayLiveView($image_name){

  $camera_settings_str = file_get_contents(RASPI_CAMERA_SETTINGS, true);
  $camera_settings_array = json_decode($camera_settings_str, true);

  // Determine if it's day or night so we know which delay to use.
  // $delay is the old variable name.  If it exists, assume the new variables don't exist.
  if (!  isset($camera_settings_array["delay"]))
  {
	// using new variables
	$delay_set = false;
	// The time between daylight exposures is (daydelay + dayexposure),
	// but dayexposure is very small during the day so just use the delay.
	$daydelay = $camera_settings_array["daydelay"];

	// The time between night exposures is (nightdelay + nightmaxexposure).
	// Both can be large numbers so use both.
	if (isset($camera_settings_array['nightmaxexposure']))	// not defined for RPiHQ cameras
		$x = $camera_settings_array['nightmaxexposure'];
	else
		$x = $camera_settings_array['nightexposure'];
	$nightdelay = $camera_settings_array["nightdelay"] + $x;

	$lat = $camera_settings_array['latitude'];
	$lon = $camera_settings_array['longitude'];
	$angle = $camera_settings_array['angle'];
	exec("sunwait poll exit set angle " . $angle . " " . $lat . " " . $lon, $return);
	if ($return == 'DAY') {
		$delay = $daydelay;
	} else {
		$delay = $nightdelay;
	}
	// Note that if liveview is left open during a day/night transition, the delay will become wrong.
	// For example, if liveview is started during the day we use "daydelay" but then at night we're still
	// using "daydelay" but should be using "nightdelay".  The user can fix this by reloading the web page.
  } else {
	$delay_set = true;
  }

  $status = new StatusMessages();

  if ( $camera_settings_array['darkframe'] === '1'){
	$status->addMessage('Currently capturing dark frames. You can turn this off on the camera settings page.');
  } else {
	if (! $delay_set)
		$status->addMessage("<center>Daytime images updated every " . $daydelay / 1000 . " seconds, nighttime every " . $nightdelay / 1000 ." seconds</center>", "message", true);
	else
		$status->addMessage("<center>Image updated every " . $d / 1000 . " seconds", "message", true);
  }
  ?>
  <script>
        setInterval(function () {
            getImage();
        }, <?php echo $delay / 2 // Divide by 2 to lessen the delay between a new picture and when we check.  ?>);
  </script>

  <div class="row">
	<p><?php $status->showMessages(); ?></p>
	<div id="live_container" style="background-color: black; margin-bottom: 15px;">
      	<img id="current" class="current" src="<?php echo $image_name ?>" style="width:100%">
  	</div>
  </div>
  <?php 
}

?>
