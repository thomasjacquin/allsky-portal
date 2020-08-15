<?php

function DisplayLiveView(){

  $camera_settings_str = file_get_contents(RASPI_CAMERA_SETTINGS, true);
  $camera_settings_array = json_decode($camera_settings_str, true);

  $status = new StatusMessages();

  if ( $camera_settings_array['darkframe'] === '1'){
	$status->addMessage('Currently capturing dark frame. You can turn this off on the camera settings page.');
  }

  ?>
  <script>
        setInterval(function () {
            getImage();
        }, <?php echo $camera_settings_array["exposure"] ?>);
  </script>

  <div class="row">
	<p><?php $status->showMessages(); ?></p>
	<div id="live_container" style="background-color: black; margin-bottom: 15px;">
      	<img id="current" class="current" src="current/liveview-<?php echo $camera_settings_array['filename'] ?>" style="width:100%">
  	</div>
  </div>
  <?php 
}

?>
