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
    exec('cat /proc/cpuinfo', $cpuinfo_array);
    $rev = trim(array_pop(explode(':', array_pop(preg_grep("/^Revision/", $cpuinfo_array)))));
    if (array_key_exists($rev, $revisions)) {
        return $revisions[$rev];
    } else {
        return 'Unknown Pi';
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
    $temp_type = $camera_settings_array['temptype'];
    if ($temp_type == "") $temp_type = "C";


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

    // Disk usage
    // File Usage
    /* get disk space free (in bytes) */
    $df = disk_free_space("/var/www");
    /* and get disk space total (in bytes)  */
    $dt = disk_total_space("/var/www");
    /* now we calculate the disk space used (in bytes) */
    $du = $dt - $df;
    /* percentage of disk used - this will be used to also set the width % of the progress bar */
    $dp = sprintf('%.2f', ($du / $dt) * 100);

    /* and we formate the size from bytes to MB, GB, etc. */
    $df = formatSize($df);
    $du = formatSize($du);
    $dt = formatSize($dt);


    if ($memused > 90) {
        $memused_status = "danger";
    } elseif ($memused > 75) {
        $memused_status = "warning";
    } elseif ($memused > 0) {
        $memused_status = "success";
    }

    // cpu load
    $cores = exec("grep -c ^processor /proc/cpuinfo");
    $loadavg = exec("awk '{print $1}' /proc/loadavg");
    $cpuload = floor(($loadavg * 100) / $cores);
    if ($cpuload > 90) {
        $cpuload_status = "danger";
    } elseif ($cpuload > 75) {
        $cpuload_status = "warning";
    } elseif ($cpuload > 0) {
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
    if (file_exists($fan_data)) {	// fanspeed is $1, we want percent
        $fanpercent = exec("awk '{print $2}' ".$fan_data);
        if ($fanpercent >= 90) {
	        $fan_status = "danger";
        } elseif ($fanpercent >= 75) {
	        $fan_status = "warning";
        } else {
	        $fan_status = "success";
        }
    } else {
        $fanpercent = "";
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
                                    <div class="info-item">Hostname</div>
                                    <?php echo $hostname ?></br>
                                    <div class="info-item">Pi Revision</div>
                                    <?php echo RPiVersion() ?></br>
                                    <div class="info-item">Uptime</div>
                                    <?php echo $uptime ?></br>
				    <div class="info-item">SD Card</div>
                                    <?php echo "$dt ($df free)" ?></br></br>
                                    <div class="info-item">Memory Used</div>
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-<?php echo $memused_status ?> ECCprogress-bar-striped active"
                                             role="progressbar"
                                             aria-valuenow="<?php echo $memused ?>" aria-valuemin="0"
                                             aria-valuemax="100"
                                             style="width: <?php echo $memused ?>%;"><?php echo $memused ?>%
                                        </div>
                                    </div>
                                    <div class="info-item">CPU Load</div>
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-<?php echo $cpuload_status ?> ECCprogress-bar-striped active"
                                             role="progressbar"
                                             aria-valuenow="<?php echo $cpuload ?>" aria-valuemin="0"
                                             aria-valuemax="100"
                                             style="width: <?php echo $cpuload ?>%;"><?php echo $cpuload ?>%
                                        </div>
                                    </div>
                                    <div class="info-item">CPU Temperature</div>
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-<?php echo $temperature_status ?> ECCprogress-bar-striped active"
                                             role="progressbar"
                                             aria-valuenow="<?php echo $temperature ?>" aria-valuemin="0"
                                             aria-valuemax="100"
                                             style="width: <?php echo $temperature ?>%;"><?php echo $display_temperature ?>
                                        </div>
                                    </div>
                                    <?php if ($fanpercent != "") { ?>
                                        <div class="info-item">Fan speed</div>
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-<?php echo $fan_status ?> ECCprogress-bar-striped active"
                                                 role="progressbar"
                                                 aria-valuenow="<?php echo $fanpercent ?>" aria-valuemin="0" aria-valuemax="100"
                                                 style="width: <?php echo $fanpercent ?>%;"><?php echo $fanpercent ?>%
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <div class="info-item">Disk Usage</div>
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-<?php echo $disk_usage_status ?> ECCprogress-bar-striped active"
                                             role="progressbar"
                                             aria-valuenow="<?php echo $dp ?>" aria-valuemin="0" aria-valuemax="100"
                                             style="width: <?php echo $dp ?>%;"><?php echo $dp ?>%
                                        </div>
                                    </div>
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
