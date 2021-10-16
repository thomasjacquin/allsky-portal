<?php

include_once('includes/functions.php');

define('ALLSKY_HOME', '/home/pi/allsky');		// value updated during installation
define('ALLSKY_SCRIPTS', ALLSKY_HOME . '/scripts');	// value updated during installation
define('ALLSKY_IMAGES', ALLSKY_HOME . '/images');	// value updated during installation

// xxx COMPATIBILITY CHECK:
// Version 0.8 and older of allsky had config.sh and autocam.sh in $ALLSKY_HOME.
// Newer versions have them in $ALLSKY_CONFIG.
// Look for a variable we know won't be null in the new location;
// if it's not there, use the old location.
$allsky_config_dir = '/config';
$allsky_config = ALLSKY_HOME . $allsky_config_dir;	// value updated during installation
if (file_exists($allsky_config . '/config.sh')) {
	define('ALLSKY_CONFIG_DIR', '/config');
	define('ALLSKY_CONFIG', ALLSKY_HOME . ALLSKY_CONFIG_DIR);
} else {
	define('ALLSKY_CONFIG_DIR', '');
	define('ALLSKY_CONFIG', ALLSKY_HOME);
}

$img_dir = get_variable(ALLSKY_CONFIG . '/config.sh', 'IMG_DIR=', 'current');
$cam = get_variable(ALLSKY_CONFIG . '/autocam.sh', 'CAMERA=', 'ZWO');
define('RASPI_CONFIG', '/etc/raspap');			// xxx will replace with ALLSKY_CONFIG
define('RASPI_CAMERA_SETTINGS', RASPI_CONFIG . '/settings_'.$cam.'.json');

$camera_settings_str = file_get_contents(RASPI_CAMERA_SETTINGS, true);
$camera_settings_array = json_decode($camera_settings_str, true);
// xxx NEW:  $image_name = $img_dir . "/" . $camera_settings_array['filename'];
// // old way uses IMG_PREFIX:
$img_prefix = get_variable(ALLSKY_CONFIG . '/config.sh', 'IMG_PREFIX=', '');
$image_name = $img_dir . "/" . $img_prefix . $camera_settings_array['filename'];

?>

<style>
body {
	margin: 0;
}
</style>

<div class="row">
   <div id="live_container" style="background-color: black;">
   	<img id="current" class="current" src="<?php echo $image_name ?>" style="width:100%">
   </div>
</div>

<!-- jQuery -->
<script src="bower_components/jquery/dist/jquery.min.js"></script>

<script type="text/javascript">
                function getImage(){
                        var img = $("<img />").attr('src', '<?php echo $image_name ?>?_ts=' + new Date().getTime())

                                .attr("id", "current")
                                .attr("class", "current")
                                .css("width", "100%")
                                .on('load', function() {
                                    if (!this.complete || typeof this.naturalWidth == "undefined" || this.naturalWidth == 0) {
                                        console.log('broken image!');
                                        setTimeout(function(){
                                            getImage();
                                        }, 500);
                                    } else {
                                        $("#live_container").empty().append(img);
                                    }
                                });
                }

                setInterval(function(){
                        getImage();
                }, <?php echo $camera_settings_array["nightexposure"]/1000 < 5000 ? 5000 : $camera_settings_array["nightexposure"]/1000?>);

 </script>
