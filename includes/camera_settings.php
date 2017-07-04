<?php

/**
*
*
*/
function DisplayCameraConfig(){
  $status = new StatusMessages();
?>
  <div class="row">
    <div class="col-lg-12">
      <div class="panel panel-primary">           
        <div class="panel-heading"><i class="fa fa-camera fa-fw"></i> Configure Camera Settings</div>
        <!-- /.panel-heading -->
        <div class="panel-body">
          <p><?php $status->showMessages(); ?></p>
          <!--<h4>Camera settings</h4>-->
	<?php
		$camera_options_str = $str = file_get_contents("camera_options.json", true);
		$camera_options_array = json_decode($camera_options_str, true);
		//echo '<pre>' . print_r($camera_options_array, true) . '</pre>';
		$ini_array = parse_ini_file("camera.ini");
	?>

          <form method="POST" action="?page=camera_conf" name="camera_conf_form">
            <?php CSRFToken()?>
 
             <?php foreach($camera_options_array as $option) {
		$label = $option['label'];
		$name = $option['name'];
		$value = $ini_array[$option['name']] ? $ini_array[$option['name']] : $option['default'];
		$description = $option['description'];
		echo "<label style='width: 140px'>$label</label>";
            	echo "<input type='text' style='text-align:right; width: 120px' name='$name' value='$value'>";
		echo "<span style='margin-left: 20px'>$description</span></br>"; 
	     }?>

            <div class="btn-group" style="margin-top: 20px">
	        <input type="submit" class="btn btn-outline btn-primary" value="Save">
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
