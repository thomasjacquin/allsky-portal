<?php

function DisplayLiveView(){

  $camera_settings_str = file_get_contents(RASPI_CAMERA_SETTINGS, true);
  $camera_settings_array = json_decode($camera_settings_str, true);

  $status = new StatusMessages();
  ?>

  <div class="row">
	<div id="live_container" style="background-color: black;">
      	<img id="current" class="current" src="current/<?php echo $camera_settings_array['filename'] ?>" style="width:100%">
  	</div>
  </div>
  <?php 
}

?>
