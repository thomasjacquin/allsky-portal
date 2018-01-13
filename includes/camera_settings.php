<?php

include_once( 'includes/status_messages.php' );

function DisplayCameraConfig(){

  $camera_options_str = file_get_contents(RASPI_CAMERA_OPTIONS, true);
  $camera_options_array = json_decode($camera_options_str, true);

  $text_options = array();
   foreach($camera_options_array as $option)
   {
      if ( $option['type'] === 'text' && !in_array($option['name'], ["filename", "fontcolor"])){
	$text_options[] = $option['name'];
      } 
   }

  $status = new StatusMessages();
  if (isset($_POST['save_camera_options'])) {
    if (CSRFValidate()) {
	  if ($camera_settings_file = fopen(RASPI_CAMERA_SETTINGS, 'w')) {
		$settings = array();
	    foreach ($_POST as $key => $value){
			// We look into POST data to only select camera settings
			if (!in_array($key, ["csrf_token", "save_camera_options", "reset_camera_options"])){
				$settings[$key] = $value;
			}
	    }
		fwrite($camera_settings_file, json_encode($settings));
		fclose($camera_settings_file);
	    $status->addMessage('Camera configuration saved');
//	    shell_exec("sudo ./restartCapture.sh");
	    shell_exec("sudo /sbin/shutdown -h now");
	  } else {
	    $status->addMessage('Failed to save camera configuration', 'danger');
	  }
    } else {
      error_log('CSRF violation');
    }
  }

  if (isset($_POST['reset_camera_options'])) {
    if (CSRFValidate()) {
	  if ($camera_settings_file = fopen(RASPI_CAMERA_SETTINGS, 'w')) {
		$settings = array();
	    foreach ($camera_options_array as $option){
			$key = $option['name'];
			$value = $option['default'];
			$settings[$key] = $value;
	    }
	    fwrite($camera_settings_file, json_encode($settings));
		fclose($camera_settings_file);
	    $status->addMessage('Camera configuration reset to default');
	  } else {
	    $status->addMessage('Failed to reset camera configuration', 'danger');
	  }
    } else {
      error_log('CSRF violation');
    }
  }

  $camera_settings_str = file_get_contents(RASPI_CAMERA_SETTINGS, true);
  $camera_settings_array = json_decode($camera_settings_str, true);

  $text_options = array();
   foreach($camera_options_array as $option)
   {
      if ( $option['type'] === 'text' && !in_array($option['name'], ["filename", "fontcolor"])){
	$text_options[] = $option['name'];
      } 
   }

?>
  <div class="row">
    <div class="col-lg-12">
      <div class="panel panel-primary">
        <div class="panel-heading"><i class="fa fa-camera fa-fw"></i> Configure Camera Settings</div>
        <!-- /.panel-heading -->
        <div class="panel-body">
          <p><?php $status->showMessages(); ?></p>

          <form method="POST" class="form-inline" action="?page=camera_conf" name="camera_conf_form">
            <?php CSRFToken()?>

             <?php foreach($camera_options_array as $option) {
				$label = $option['label'];
				$name = $option['name'];
				$value = $camera_settings_array[$option['name']] != null ? $camera_settings_array[$option['name']] : $option['default'];
				$description = $option['description'];
				$type = $option['type'];
				echo "<div class='form-group' style='margin: 3px 0'>";
				echo "<label style='width: 140px'>$label</label>";
				    	echo "<input class='form-control' type='$type' style='text-align:right; width: 120px; margin-right: 20px' onclick='this.select();' name='$name' value='$value'>";
				echo "<span>$description</span>";
				echo "</div><div style='clear:both'></div>";
			 }?>

            <div style="margin-top: 20px">
	        <input type="submit" class="btn btn-outline btn-primary" name="save_camera_options" value="Save and Reboot">
		<input type="submit" class="btn btn-warning" name="reset_camera_options" value="Reset to default values">
	    </div>
          </form>
        </div><!-- ./ Panel body -->
        <!--<div class="panel-footer"><strong>Note,</strong> WEP access points appear as 'Open'. Allsky Camera Portal does not currently support connecting to WEP.</div>-->
      </div><!-- /.panel-primary -->
    </div><!-- /.col-lg-12 -->
  </div><!-- /.row -->
<?php
}

?>
