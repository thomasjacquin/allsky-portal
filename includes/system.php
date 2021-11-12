<?php
/**
 *
 * Find the version of the Raspberry Pi
 * Currently only used for the system information page but may useful elsewhere
 *
 */

function RPiVersion()
{
    // Lookup table from https://www.raspberrypi.org/documentation/hardware/raspberrypi/revision-codes/README.md
    // Last updated december 2020
    $revisions = array(
	'0002' => 'Model B Revision 1.0',
	'0003' => 'Model B Revision 1.0 + ECN0001',
	'0004' => 'Model B Revision 2.0 (256 MB)',
	'0005' => 'Model B Revision 2.0 (256 MB)',
	'0006' => 'Model B Revision 2.0 (256 MB)',
	'0007' => 'Model A',
	'0008' => 'Model A',
	'0009' => 'Model A',
	'000d' => 'Model B Revision 2.0 (512 MB)',
	'000e' => 'Model B Revision 2.0 (512 MB)',
	'000f' => 'Model B Revision 2.0 (512 MB)',
	'0010' => 'Model B+',
	'0013' => 'Model B+',
	'0011' => 'Compute Module',
	'0012' => 'Model A+',
	'a01040' => 'Pi 2 Model B Revision 1.0 (1 GB)',
	'a01041' => 'Pi 2 Model B Revision 1.1 (1 GB)',
	'a02042' => 'Pi 2 Model B (with BCM2837) Revision 1.2 (1 GB)',
	'a21041' => 'Pi 2 Model B Revision 1.1 (1 GB)',
	'a22042' => 'Pi 2 Model B (with BCM2837) Revision 1.2 (1 GB)',
	'a020a0' => 'Compute Module 3 Revision 1.0 (1 GB)',
	'a220a0' > 'Compute Module 3 Revision 1.0 (1 GB)',
	'900021' => 'Model A+ Revision 1.1 (512 MB)',
	'900032' => 'Model B+ Revision 1.2 (512 MB)',
	'900061' => 'Compute Module Revision 1.1 (512 MB)',
	'900092' => 'PiZero 1.2 (512 MB)',
	'900093' => 'PiZero 1.3 (512 MB)',
	'9000c1' => 'PiZero W 1.1 (512 MB)',
	'920092' => 'PiZero Revision 1.2 (512 MB)',
	'920093' => 'PiZero Revision 1.3 (512 MB)',
	'9020e0' => 'Pi 3 Model A+ Revision 1.0 (512 MB)',
	'a02082' => 'Pi 3 Model B Revision 1.2 (1 GB)',
	'a22082' => 'Pi 3 Model B Revision 1.2 (1 GB)',
	'a22083' => 'Pi 3 Model B Revision 1.3 (1 GB)',
	'a020d3' => 'Pi 3 Model B+ Revision 1.3 (1 GB)',
	'a32082' => 'Pi 3 Model B Revision 1.2 (1 GB)',
	'a52082' => 'Pi 3 Model B Revision 1.2 (1 GB)',
	'a03111' => 'Model 4B Revision 1.1 (1 GB)',
	'b03111' => 'Model 4B Revision 1.1 (2 GB)',
	'b03112' => 'Model 4B Revision 1.2 (2 GB)',
	'b03114' => 'Model 4B Revision 1.4 (2 GB)',
	'c03111' => 'Model 4B Revision 1.1 (4 GB)',
	'c03112' => 'Model 4B Revision 1.2 (4 GB)',
	'c03114' => 'Model 4B Revision 1.4 (4 GB)',
	'd03114' => 'Model 4B Revision 1.4 (8 GB)',
	'c03130' => 'Pi 400 Revision 1.0 (4 GB)'
    );
    // space and tab for -F
    exec("awk -F ' ' '/^Revision/ {print $3; exit 0;}' < /proc/cpuinfo", $rev);
    $rev = trim(array_pop($rev));
    if (array_key_exists($rev, $revisions)) {
        return $revisions[$rev];
    } else {
        return 'Unknown Pi, rev=' . $rev;
    }
}

function formatSize($bytes)
{
    $types = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $bytes >= 1024 && $i < (count($types) - 1); $bytes /= 1024, $i++) ;
    return (round($bytes, 2) . " " . $types[$i]);
}

/**
 *
 *
 */
function DisplaySystem()
{
    $camera_settings_str = file_get_contents(RASPI_CAMERA_SETTINGS, true);
    $camera_settings_array = json_decode($camera_settings_str, true);
	if (isset($camera_settings_array['temptype'])) {
		$temp_type = $camera_settings_array['temptype'];
		if ($temp_type == "") $temp_type = "C";
	} else {
		$temp_type = "C";
	}

    // hostname
    exec("hostname -f", $hostarray);
    $hostname = $hostarray[0];

    // uptime
    $uparray = explode(" ", exec("cat /proc/uptime"));
    $seconds = round($uparray[0], 0);
    $minutes = $seconds / 60;
    $hours = $minutes / 60;
    $days = floor($hours / 24);
    $hours = floor($hours - ($days * 24));
    $minutes = floor($minutes - ($days * 24 * 60) - ($hours * 60));
    $uptime = '';
    if ($days != 0) {
        $uptime .= $days . ' day' . (($days > 1) ? 's ' : ' ');
    }
    if ($hours != 0) {
        $uptime .= $hours . ' hour' . (($hours > 1) ? 's ' : ' ');
    }
    if ($minutes != 0) {
        $uptime .= $minutes . ' minute' . (($minutes > 1) ? 's ' : ' ');
    }

    // mem used
    exec("free -m | awk '/Mem:/ { total=$2 } /buffers\/cache/ { used=$3 } END { print used/total*100}'", $memarray);
    $memused = floor($memarray[0]);
    // check for memused being unreasonably low, if so repeat expecting modern output of "free" command
    if ($memused < 0.1) {
        unset($memarray);
        exec("free -m | awk '/Mem:/ { total=$2 } /Mem:/ { used=$3 } END { print used/total*100}'", $memarray);
        $memused = floor($memarray[0]);
    }
    if ($memused > 90) {
        $memused_status = "danger";
    } elseif ($memused > 75) {
        $memused_status = "warning";
    } elseif ($memused > 0) {
        $memused_status = "success";
    }


    // Disk usage
    // File Usage
    /* get disk space free (in bytes) */
    $df = disk_free_space("/var/www");
    /* and get disk space total (in bytes)  */
    $dt = disk_total_space("/var/www");
    /* now we calculate the disk space used (in bytes) */
    $du = $dt - $df;
    /* percentage of disk used - this will be used to also set the width % of the progress bar */
    $dp = sprintf('%.1f', ($du / $dt) * 100);

    /* and we formate the size from bytes to MB, GB, etc. */
    $df = formatSize($df);
    $du = formatSize($du);
    $dt = formatSize($dt);

	// Throttle / undervoltage status
	$x = exec("sudo vcgencmd get_throttled 2>&1");	// Output: throttled=0x12345...
	if (preg_match("/^throttled=/", $x) == false) {
			$throttle_status="<div class='progress-bar-danger' style='overflow: hidden'>Not able to get throttle status:<br>$x";
			$throttle_status = $throttle_status . "<br><span style='font-size: 150%'>Run 'sudo ~/allsky/gui/install.sh --update' to try and resolve.</style>";
			$throttle_status = $throttle_status . "</div>";
	} else {
		$x = explode("x", $x);	// Output: throttled=0x12345...
//FOR TESTING: $x[1] = "50001";
		if ($x[1] == "0") {
				$throttle_status="<div class='progress-bar-success'>No throttling</div>";
		} else {
			$bits = base_convert($x[1], 16, 2);	// convert hex to bits
			// See https://www.raspberrypi.com/documentation/computers/os.html#vcgencmd
			$messages = array(
				0 => 'Currently under-voltage',
				1 => 'ARM frequency currently capped',
				2 => 'Currently throttled',
				3 => 'Soft temperature limit currently active',

				16 => 'Under-voltage has occurred since last reboot.',
				17 => 'Throttling has occurred since last reboot.',
				18 => 'ARM frequency capped has occurred since last reboot.',
				19 => 'Soft temperature limit has occurred'
			);
			$l = strlen($bits);
//echo "<br>bits=$bits, strlen=$l";
			$s = "warning";
			$throttle_status = "";
		// bit 0 is the rightmost bit
			for ($pos=0; $pos<$l; $pos++) {
				$i = $l - $pos - 1;
				$bit = $bits[$i];
//echo "<br>pos=$pos, lookin at bit $i, is $bit";
				if ($bit == 0) continue;
				if (array_key_exists($pos, $messages)) {
//echo "<br>Found, = " . $messages[$pos];
                	if ($throttle_status == "") {
                    	$throttle_status = $messages[$pos];
					} else {
				    	$throttle_status = $throttle_status . "<br>" . $messages[$pos];
                	}
					if ($pos <=3) $s = "danger"; // current issues are a danger; prior a warning
				}
			}
			$throttle_status = "<div class='progress-bar-$s' style='overflow: hidden'>" . $throttle_status . "</div>";
		}
	}

    // cpu load
    $secs = 2; $q = '"';
    $cpuload = exec("(grep -m 1 'cpu ' /proc/stat; sleep $secs; grep -m 1 'cpu ' /proc/stat) | awk '{u=$2+$4; t=$2+$4+$5; if (NR==1){u1=u; t1=t;} else printf($q%.0f$q, (($2+$4-u1) * 100 / (t-t1))); }'");
    if ($cpuload < 0 || $cpuload > 100) echo "<p style='color: red; font-size: 125%;'>Invalid cpuload value: $cpuload</p>";
    if ($cpuload > 90 || $cpuload < 0) {
        $cpuload_status = "danger";
    } elseif ($cpuload > 75) {
        $cpuload_status = "warning";
    } elseif ($cpuload >= 0) {
        $cpuload_status = "success";
    }

    // temperature
    $temperature = round(exec("awk '{print $1/1000}' /sys/class/thermal/thermal_zone0/temp"), 2);
    if ($temperature > 70 || $temperature < 0) {
        $temperature_status = "danger";
    } elseif ($temperature > 60 || $temperature < 10) {
        $temperature_status = "warning";
    } else {
        $temperature_status = "success";
    }
    $display_temperature = "";
    if ($temp_type == "C" || $temp_type == "B")
        $display_temperature = number_format($temperature, 1, '.', '') . "&deg;C";
    if ($temp_type == "F" || $temp_type == "B")
        $display_temperature = $display_temperature . "&nbsp; &nbsp;" . number_format((($temperature * 1.8) + 32), 1, '.', '') . "&deg;F";

    // fan speed.  Should probably put the path in config.sh...
    $fan_data = "/home/pi/fan/fandata.txt";
    if (file_exists($fan_data)) {	// fanspeed is $1, we want percent which is $2
        $fan = exec("awk '{print $2}' ".$fan_data);
        if ($fan >= 90) {
	        $fan_status = "danger";
        } elseif ($fan >= 75) {
	        $fan_status = "warning";
        } else {
	        $fan_status = "success";
        }
    } else {
        $fan = "";
    }

    // disk usage
    if ($dp > 90) {
        $disk_usage_status = "danger";
    } elseif ($dp > 70 && $dp < 90) {
        $disk_usage_status = "warning";
    } else {
        $disk_usage_status = "success";
    }
    ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-primary">
                <div class="panel-heading"><i class="fa fa-cube fa-fw"></i> System</div>
                <div class="panel-body">

                    <?php
                    if (isset($_POST['system_reboot'])) {
                        echo '<div class="alert alert-warning">System Rebooting Now!</div>';
                        $result = shell_exec("sudo /sbin/reboot");
                    }
                    if (isset($_POST['system_shutdown'])) {
                        echo '<div class="alert alert-warning">System Shutting Down Now!</div>';
                        $result = shell_exec("sudo /sbin/shutdown -h now");
                    }
		    if (isset($_POST['service_start'])) {
                        echo '<div class="alert alert-warning">allsky service started</div>';
                        $result = shell_exec("sudo /bin/systemctl start allsky");
                    }
		    if (isset($_POST['service_stop'])) {
                        echo '<div class="alert alert-warning">allsky service stopped</div>';
                        $result = shell_exec("sudo /bin/systemctl stop allsky");
                    }
                    ?>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <h4>System Information</h4>
									<table>
									<tr class="x"><td class="info-item">Hostname</td><td><?php echo $hostname ?></td></tr>
									<tr class="x"><td class="info-item">Pi Revision</td><td><?php echo RPiVersion() ?></td></tr>
									<tr class="x"><td class="info-item">Uptime</td><td><?php echo $uptime ?></td></tr>
									<tr class="x"><td class="info-item">SD Card</td><td><?php echo "$dt ($df free)" ?></td></tr>
									<tr class="x"><td class="info-item">Throttle Status</td><td><?php echo $throttle_status ?></td></tr>

									<tr><td colspan="2" style="height: 15px"></td></tr>
									<tr><td class="info-item">Memory Used</td>
										<td style="width: 100%" class="progress"><div class="progress-bar progress-bar-<?php echo $memused_status ?>"
										role="progressbar"
										aria-valuenow="<?php echo $memused ?>" aria-valuemin="0" aria-valuemax="100"
										style="width: <?php echo $memused ?>%;"><?php echo $memused ?>%
										</div></td></tr>

									<tr><td colspan="2" style="height: 15px"></td></tr>
									<tr><td class="info-item">CPU Load</td>
										<td style="width: 100%" class="progress"><div class="progress-bar progress-bar-<?php echo $cpuload_status ?>"
										role="progressbar"
										aria-valuenow="<?php echo $cpuload ?>" aria-valuemin="0" aria-valuemax="100"
										style="width: <?php echo $cpuload ?>%;"><?php echo $cpuload ?>%
										</div></td></tr>

									<tr><td colspan="2" style="height: 15px"></td></tr>
									<tr><td class="info-item">CPU Temperature</td>
										<td style="width: 100%" class="progress"><div class="progress-bar progress-bar-<?php echo $temperature_status ?>"
										role="progressbar"
										aria-valuenow="<?php echo $temperature ?>" aria-valuemin="0" aria-valuemax="100"
                                        style="width: <?php echo $temperature ?>%;"><?php echo $display_temperature ?>
										</div></td></tr>
                                <?php if ($fan != "") { ?>

									<tr><td colspan="2" style="height: 15px"></td></tr>
									<tr><td class="info-item">Fan Speed</td>
										<td style="width: 100%" class="progress"><div class="progress-bar progress-bar-<?php echo $fan_status ?>"
										role="progressbar"
										aria-valuenow="<?php echo $fan ?>" aria-valuemin="0" aria-valuemax="100"
										style="width: <?php echo $fan ?>%;"><?php echo $fan ?>%
										</div></td></tr>
                                <?php } ?>

									<tr><td colspan="2" style="height: 15px"></td></tr>
									<tr><td class="info-item">Disk Usage</td>
										<td style="width: 100%" class="progress"><div class="progress-bar progress-bar-<?php echo $disk_usage_status ?>"
										role="progressbar"
										aria-valuenow="<?php echo $dp ?>" aria-valuemin="0" aria-valuemax="100"
										style="width: <?php echo $dp ?>%;"><?php echo $dp ?>%
										</div></td></tr>
									</table>
                                </div><!-- /.panel-body -->
                            </div><!-- /.panel-default -->
                        </div><!-- /.col-md-6 -->
                    </div><!-- /.row -->

                    <form action="?page=system_info" method="POST">
			<div style="margin-bottom: 20px">
				<button type="button" class="btn btn-outline btn-primary" onclick="document.location.reload(true)"><i class="fa fa-sync-alt"></i> Refresh</button>
                        </div>
			<div style="margin-bottom: 15px">
				<button type="submit" class="btn btn-success" style="margin-bottom:5px" name="service_start"/><i class="fa fa-play"></i> Start allsky</button>
				<button type="submit" class="btn btn-danger" style="margin-bottom:5px" name="service_stop"/><i class="fa fa-stop"></i> Stop allsky</button>
			</div>
                        <button type="submit" class="btn btn-warning" style="margin-bottom:5px" name="system_reboot"/><i class="fa fa-power-off"></i> Reboot Raspberry Pi</button>
                        <button type="submit" class="btn btn-warning" style="margin-bottom:5px" name="system_shutdown"/><i class="fa fa-plug"></i> Shutdown Raspberry Pi</button>
                    </form>

                </div><!-- /.panel-body -->
            </div><!-- /.panel-primary -->
        </div><!-- /.col-lg-12 -->
    </div><!-- /.row -->
    <?php
}
?>
