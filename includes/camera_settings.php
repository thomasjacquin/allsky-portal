<?php
include_once( 'includes/status_messages.php' );

function DisplayCameraConfig(){
	$camera_options_str = file_get_contents(RASPI_CAMERA_OPTIONS, true);
	$camera_options_array = json_decode($camera_options_str, true);

	global $status;
	$status = new StatusMessages();

	if (isset($_POST['save_camera_settings'])) {
		if (CSRFValidate()) {
			if ($camera_settings_file = fopen(RASPI_CAMERA_SETTINGS, 'w')) {
				$settings = array();
	 			foreach ($_POST as $key => $value){
					// We look into POST data to only select camera settings
					if (!in_array($key, ["csrf_token", "save_camera_settings", "reset_camera_settings", "restart"])){
						$settings[$key] = $value;
					}
				}
				fwrite($camera_settings_file, json_encode($settings, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
				fclose($camera_settings_file);
				$msg = "Camera settings saved";
				if (isset($_POST['restart'])) {
					$msg .= " and service restarted";
					runCommand("sudo /bin/systemctl reload-or-restart allsky.service", $msg, "success");
				} else {
					$msg .= " but service NOT restarted";
					$status->addMessage($msg, 'info');
				}
			} else {
				$status->addMessage('Failed to save camera settings', 'danger');
			}
		} else {
			$status->addMessage('Unable to save camera settings - session timeout', 'danger');
		}
	}

	if (isset($_POST['reset_camera_settings'])) {
		if (CSRFValidate()) {
			if ($camera_settings_file = fopen(RASPI_CAMERA_SETTINGS, 'w')) {
				$settings = array();
				foreach ($camera_options_array as $option){
					$key = $option['name'];
					$value = $option['default'];
					$settings[$key] = $value;
				}
				fwrite($camera_settings_file, json_encode($settings,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK));
				fclose($camera_settings_file);
				$status->addMessage('Camera settings reset to default');
			} else {
				$status->addMessage('Failed to reset camera settings', 'danger');
			}
		} else {
			$status->addMessage('Unable to reset camera settings - session timeout', 'danger');
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
      <div class="panel-heading"><i class="fa fa-camera fa-fw"></i> Configure Camera Settings <?php echo "&nbsp; &nbsp; &nbsp; - &nbsp; &nbsp; &nbsp; " . RASPI_CAMERA_OPTIONS; ?></div>
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
				$display = $option['display'];
				if (! $display) continue;

				if (isset($option['minimum']))
					$minimum = $option['minimum'];
				else
					$minimum = "";
				if (isset($option['maximum']))
					$maximum = $option['maximum'];
				else
					$maximum = "";
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
				if (isset($option['default']))
					$default = $option['default'];
				else
					$default = "";
				$type = $option['type'];
				if ($type == "header") {
					$value = "";
				} else {
					if (isset($camera_settings_array[$name]))
						$value = $camera_settings_array[$name] != null ? $camera_settings_array[$name] : $default;
					else
						$value = $default;
					// Allow single quotes in values (for string values).
					// &apos; isn't supported by all browsers so use &#x27.
					$value = str_replace("'", "&#x27;", $value);
					$default = str_replace("'", "&#x27;", $default);
				}
				$description = $option['description'];
				// xxxxx Margin and padding don't seem to work, so using border-bottom...
				echo "\n<tr class='form-group $advClass' style='border-bottom: 3px solid transparent; $advStyle'>";
				if ($type == "header"){
					echo "<td colspan='3' class='settingsHeader'>$description</td>";
				} else {
					// Show the default in a popup
					if ($type == "checkbox") {
						if ($default == "0") $default = "No";
						else $default = "Yes";
					} elseif ($type == "select") {
						foreach($option['options'] as $opt) {
							$val = $opt['value'];
							if ($val != $default) continue;
							$default = $opt['label'];
							break;
						}
					} elseif ($default == "") {
						$default = "[empty]";
					}
					$popup = "Default=$default";
					if ($minimum !== "") $popup .= "\nMinimum=$minimum";
					if ($maximum !== "") $popup .= "\nMaximum=$maximum";

					echo "<td valign='middle'>";
					echo "<label style='padding-right: 5px;'>$label</label>";
					echo "</td>";
					echo "<td>";
					// The popup gets in the way of seeing the value a little.
					// May want to consider having a symbol next to the field
					// that has the popup.
					echo "<span title='$popup'>";
					if ($type == "text" || $type == "number"){
						echo "<input class='form-control' type='$type' ".
						"style='text-align: right; width: 120px; margin-right: 20px; padding: 0px 3px 0px 0px;' name='$name' value='$value'>";
					} else if ($type == "select"){
						// text-align for <select> works on Firefox but not Chrome or Edge
						echo "<select class='form-control' name='$name' style='width: 120px; margin-right: 20px; text-align: right; padding: 0px 3px 0px 0px;'>";
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
								"style='width: 40px; box-shadow:none' name='$name' value='0' ".
								($value == 0 ? " checked " : "").  ">";
							echo "<label for='switch_no_".$name."'>No</label>";
							echo "<input id='switch_yes_".$name."' class='form-control' type='radio' ".
								"style='width: 40px; box-shadow:none' name='$name' value='1' ".
								($value == 1 ? " checked " : "").  ">";
							echo "<label for='switch_yes_".$name."'>Yes</label>";
						echo "</div>";
					}
					echo "</span>";
					echo "</td>";
					echo "<td>$description</td>";
				}
				echo "</tr>";
			 }
		echo "</table>";
	?>

	<div style="margin-top: 20px">
		<input type="submit" class="btn btn-outline btn-primary" name="save_camera_settings" value="Save changes">
		<input type="submit" class="btn btn-warning" name="reset_camera_settings" value="Reset to default values" onclick="return confirm('Really RESET ALL VALUES TO DEFAULT??');">
		<button type="button" class="btn advanced" id="advButton" onclick="toggle_advanced();"><?php if ($initial_display == "none") echo "Show advanced options"; else echo "Hide advanced options"; ?></button>
		<div title="UNcheck to only save settings without restarting Allsky" style="line-height: 0.3em;"><br><input type="checkbox" name="restart" checked> Restart Allsky after saving changes?<br><br>&nbsp;</div>
	</div>
	</form>
</div><!-- ./ Panel body -->
</div><!-- /.panel-primary -->
</div><!-- /.col-lg-12 -->
</div><!-- /.row -->
<?php
}
?>
