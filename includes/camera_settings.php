<?php

include_once( 'includes/status_messages.php' );

function DisplayCameraConfig(){

  $camera_options_str = file_get_contents(RASPI_CAMERA_OPTIONS, true);
  $camera_options_array = json_decode($camera_options_str, true);

  $text_options = array();
   foreach($camera_options_array as $option)
   {
      if ( $option['type'] === 'text' ){
	$text_options[] = $option['name'];
      } 
   }
print_r($text_options);

  $status = new StatusMessages();
  if (isset($_POST['save_camera_options'])) {
    if (CSRFValidate()) {
	  if ($camera_config_file = fopen(RASPI_CAMERA_CONFIG, 'w')) {
	    foreach ($_POST as $key => $value){
		if (!in_array($key, ["csrf_token", "save_camera_options", "reset_camera_options"])){
		    if (in_array($key, $text_options))
		        fwrite($camera_config_file, $key.' = "'.$value.'"'.PHP_EOL);
		    else
			fwrite($camera_config_file, $key.' = '.$value.PHP_EOL);
		}    		
	    }
	    fclose($camera_config_file);
	    $status->addMessage('Camera configuration saved');
	  } else {
	    $status->addMessage('Failed to save camera configuration', 'danger');
	  }
    } else {
      error_log('CSRF violation');
    }
  }

  if (isset($_POST['reset_camera_options'])) {
    if (CSRFValidate()) {
	  if ($camera_config_file = fopen(RASPI_CAMERA_CONFIG, 'w')) {
	    foreach ($camera_options_array as $option){
		$key = $option['name'];
		$value = $option['default'];
		if (in_array($key, $text_options))
		        fwrite($camera_config_file, $key.' = "'.$value.'"'.PHP_EOL);
		    else
			fwrite($camera_config_file, $key.' = '.$value.PHP_EOL);
	    }
	    fclose($camera_config_file);
	    $status->addMessage('Camera configuration reset to default');
	  } else {
	    $status->addMessage('Failed to reset camera configuration', 'danger');
	  }
    } else {
      error_log('CSRF violation');
    }
  }

  $ini_array = parse_ini_file(RASPI_CAMERA_CONFIG);

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
		$value = $ini_array[$option['name']] ? $ini_array[$option['name']] : $option['default'];
		$description = $option['description'];
		$type = $option['type'];
		echo "<div class='form-group' style='margin: 3px 0'>";
		echo "<label style='width: 140px'>$label</label>";
            	echo "<input class='form-control' type='$type' style='text-align:right; width: 120px; margin-right: 20px' onclick='this.select();' name='$name' value='$value'>";
		echo "<span>$description</span>"; 
		echo "</div><div style='clear:both'></div>";
	     }?>

            <div style="margin-top: 20px">
	        <input type="submit" class="btn btn-outline btn-primary" name="save_camera_options" value="Save">
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
