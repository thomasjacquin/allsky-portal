<?php
include_once( 'includes/status_messages.php' );

function DisplayCameraConfig(){
  $camera_options_str = file_get_contents(RASPI_CAMERA_OPTIONS, true);
  $camera_options_array = json_decode($camera_options_str, true);

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
	    	$status->addMessage('Camera configuration saved.');
	    	shell_exec("sudo systemctl restart allsky.service");
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

  // Determine if the advanced settings should always be shown.
  $initial_display = $camera_settings_array['alwaysshowadvanced'] == 1 ? "table-row" : "none";
?>
<script language="javascript">
function toggle_advanced()
{
	var adv = document.getElementsByClassName("advanced");
	var newMode = "";
	for (var i=0; i<adv.length; i++) {
		// Don't hide the button!
		if (adv[i].id != "advButton") {
			var s = adv[i].style;
			if (s.display == "none") {
				newMode = "table-row";
			} else {
				newMode = "none";
			}
			s.display = newMode;
		}
	}

	var b = document.getElementById("advButton");
	if (newMode == "none") {
		// advanced options are now hidden, change button text
		b.innerHTML = "Show advanced options...";
	} else {
		b.innerHTML = "Hide advanced options";
	}

	// Show/hide the default values.
	var def = document.getElementsByClassName("default");
	var newMode = "";
	for (var i=0; i<def.length; i++) {
		var s = def[i].style;
		if (s.display == "none") {
			newMode = "inline";
		} else {
			newMode = "none";
		}
		s.display = newMode;
	}
}
</script>
  <div class="row">
    <div class="col-lg-12">
      <div class="panel panel-primary">
        <div class="panel-heading"><i class="fa fa-camera fa-fw"></i> Configure Camera Settings</div>
        <!-- /.panel-heading -->
        <div class="panel-body">
          <p><?php $status->showMessages(); ?></p>

          <form method="POST" action="?page=camera_conf" name="camera_conf_form">
            <?php CSRFToken()?>

             <?php
		// Allow for "advanced" options that aren't displayed by default to avoid
		// confusing novice users.
		$numAdvanced = 0;
		echo "<table border='0'>";
			foreach($camera_options_array as $option) {
				$advanced = $option['advanced'];
				if ($advanced == 1) {
					$numAdvanced++;
					$advClass = "advanced";
					$advStyle = "display: $initial_display";
				} else {
					$advClass = "";
					$advStyle = "";
				}
				$label = $option['label'];
				$name = $option['name'];
				$default = $option['default'];
				$type = $option['type'];
				if ($type == "header") {
					$value = "";
				} else {
					$value = $camera_settings_array[$name] != null ? $camera_settings_array[$name] : $default;
					// Allow single quotes in values (primarily string values) and descriptions).
					// &apos; isn't supported by all browsers so use &#x27; instead for single quote.
					$value = str_replace("'", "&#x27;", $value);
				}
				$description = str_replace("'", "&#x27;", $option['description']);
				// If in "advanced" mode, show the default if it's not the current value.
				if ($value != $default) {
					if ($default == "") $default = "[empty]";
					elseif ($type == "checkbox") {
						if ($default == "0") $default = "No";
						else $default = "Yes";
					}
					$description = "$description<span class='default' style='font-size: 85%; display: none'><br><i>Default=$default</i></span>";
				}
				// xxxxx Margin and padding don't seem to work, so using border-bottom...
				echo "\n<tr class='form-group $advClass' style='border-bottom: 3px solid transparent; $advStyle'>";
				if ($type == "header"){
					echo "<td colspan='3' class='settingsHeader'>$description</td>";
				} else {
					echo "<td valign='middle'>";
					echo "<label style='padding-right: 5px;'>$label</label></td>";
					echo "<td>";
					if ($type == "text" || $type == "number"){
						echo "<input class='form-control' type='$type' ".
						"style='text-align: right; width: 120px; margin-right: 20px' ".
						"name='$name' value='$value'>";
					} else if ($type == "select"){
						echo "<select class='form-control' name='$name' ".
							"style='width: 120px; margin-right: 20px'>";
						foreach($option['options'] as $opt){
							$val = $opt['value'];
							$lab = $opt['label'];
							if ($value == $val){
								echo "<option value='$val' selected>$lab</option>";
							} else {
								echo "<option value='$val'>$lab</option>";
							}
						}
						echo "</select>";
					} else if ($type == "checkbox"){
						echo "<div class='switch-field' style='margin-bottom: -5px;'>";
							echo "<input id='switch_no_".$name."' class='form-control' type='radio' ".
        	                                	"style='width: 40px; box-shadow:none' ".
                	                        	"name='$name' value='0' ".
                        	                	($value == 0 ? " checked " : "").
                                	        	">";
							echo "<label for='switch_no_".$name."'>No</label>";
							echo "<input id='switch_yes_".$name."' class='form-control' type='radio' ".
	                                        	"style='width: 40px; box-shadow:none' ".
        	                                	"name='$name' value='1' ".
                	                        	($value == 1 ? " checked " : "").
                        	                	">";
							echo "<label for='switch_yes_".$name."'>Yes</label>";
						echo "</div>";
					}
					echo "</td>";
					echo "<td>$description</td>";
				}
				echo "</tr>";
			 }
		echo "</table>";
	      ?>

            <div style="margin-top: 20px">
		<input type="submit" class="btn btn-outline btn-primary" name="save_camera_options" value="Save changes">
		<input type="submit" class="btn btn-warning" name="reset_camera_options" value="Reset to default values" onclick="return confirm('Really RESET ALL VALUES TO DEFAULT??');">
		<button type="button" class="btn advanced" id="advButton" onclick="toggle_advanced();"><?php if ($initial_display == "none") echo "Show advanced options"; else echo "Hide advanced options"; ?></button>
	    </div>
          </form>
        </div><!-- ./ Panel body -->
      </div><!-- /.panel-primary -->
    </div><!-- /.col-lg-12 -->
  </div><!-- /.row -->
<?php
}

?>
