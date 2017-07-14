<?php

function DisplayLiveView(){

  $ini_array = parse_ini_file(RASPI_CAMERA_CONFIG);

  $status = new StatusMessages();
  ?>

  <div class="row">
	<div id="live_container" style="background-color: black;">
      	<img id="current" class="current" src="current/<?php echo $ini_array['filename'] ?>" style="width:100%">
  	</div>
  </div>
  <?php 
}

?>
