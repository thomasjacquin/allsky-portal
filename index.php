<?php

/**
 * AllSky Administration Panel
 *
 * Enables use of simple web interface rather than SSH to control a ZWO camera on the Raspberry Pi.
 * Uses code from RaspAP by Lawrence Yau <sirlagz@gmail.com> and Bill Zimmerman <billzimmerman@gmail.com>
 *
 * @author     Lawrence Yau <sirlagz@gmail.comm>
 * @author     Bill Zimmerman <billzimmerman@gmail.com>
 * @author     Thomas Jacquin <jacquin.thomas@gmail.com>
 * @license    GNU General Public License, version 3 (GPL-3.0)
 * @version    0.0.1
 * @link       https://github.com/thomasjacquin/allsky-portal
 */

include_once('includes/functions.php');		// needs to be at top for get_variable()

define('ALLSKY_HOME', '/home/pi/allsky');			// value updated during installation
define('ALLSKY_SCRIPTS', ALLSKY_HOME . '/scripts');	// value updated during installation
define('ALLSKY_IMAGES', ALLSKY_HOME . '/images');	// value updated during installation

// xxx COMPATIBILITY CHECK:
// Version 0.8 and older of allsky had config.sh and autocam.sh in $ALLSKY_HOME.
// Newer versions have them in $ALLSKY_CONFIG.
// Look for a variable we know won't be null in the new location;
// if it's not there, use the old location.
define('ALLSKY_CONFIG_DIR', '/config');		// name of just the directory
define('ALLSKY_CONFIG', ALLSKY_HOME . ALLSKY_CONFIG_DIR);	// value updated during installation
if (get_variable(ALLSKY_CONFIG . '/config.sh', 'CAMERA=', 'NOTFOUND') == "NOTFOUND") {
	define('ALLSKY_CONFIG_DIR', '');
	define('ALLSKY_CONFIG', ALLSKY_HOME);
}

// $img_dir is an alias in the web server's config.
// It's the same as ALLSKY_HOME which is the full path name on the server.
$img_dir = get_variable(ALLSKY_CONFIG . '/config.sh', 'IMG_DIR=', 'current');
$cam = get_variable(ALLSKY_CONFIG .'/autocam.sh', 'CAMERA=', 'ZWO');

define('RASPI_CONFIG', '/etc/raspap');			// xxx will replace with ALLSKY_CONFIG
define('RASPI_CAMERA_SETTINGS', RASPI_CONFIG . '/settings_'.$cam.'.json');
define('RASPI_CAMERA_OPTIONS', RASPI_CONFIG . '/camera_options_'.$cam.'.json');

$camera_settings_str = file_get_contents(RASPI_CAMERA_SETTINGS, true);
$camera_settings_array = json_decode($camera_settings_str, true);
// xxx new way:  $image_name = $img_dir . "/" . $camera_settings_array['filename'];
// old way uses IMG_PREFIX:
$img_prefix = get_variable(ALLSKY_CONFIG .'/config.sh', 'IMG_PREFIX=', 'liveview-');
$image_name = $img_dir . "/" . $img_prefix . $camera_settings_array['filename'];


// Constants for configuration file paths.
// These are typical for default RPi installs. Modify if needed.
define('RASPI_ADMIN_DETAILS', RASPI_CONFIG . '/raspap.auth');
define('RASPI_DNSMASQ_CONFIG', '/etc/dnsmasq.conf');
define('RASPI_DNSMASQ_LEASES', '/var/lib/misc/dnsmasq.leases');
define('RASPI_HOSTAPD_CONFIG', '/etc/hostapd/hostapd.conf');
define('RASPI_WPA_SUPPLICANT_CONFIG', '/etc/wpa_supplicant/wpa_supplicant.conf');
define('RASPI_HOSTAPD_CTRL_INTERFACE', '/var/run/hostapd');
define('RASPI_WPA_CTRL_INTERFACE', '/var/run/wpa_supplicant');
define('RASPI_OPENVPN_CLIENT_CONFIG', '/etc/openvpn/client.conf');
define('RASPI_OPENVPN_SERVER_CONFIG', '/etc/openvpn/server.conf');
define('RASPI_TORPROXY_CONFIG', '/etc/tor/torrc');

// Optional services, set to true to enable.
define('RASPI_OPENVPN_ENABLED', false);
define('RASPI_TORPROXY_ENABLED', false);

include_once(RASPI_CONFIG . '/raspap.php');
include_once('includes/dashboard.php');
include_once('includes/dashboard_eth0.php');
include_once('includes/liveview.php');
include_once('includes/authenticate.php');
include_once('includes/admin.php');
include_once('includes/dhcp.php');
include_once('includes/hostapd.php');
include_once('includes/system.php');
include_once('includes/configure_client.php');
include_once('includes/camera_settings.php');
include_once('includes/days.php');
include_once('includes/images.php');
include_once('includes/videos.php');
include_once('includes/keograms.php');
include_once('includes/startrails.php');
include_once('includes/editor.php');

$output = $return = 0;
if (isset($_GET['page']))
    $page = $_GET['page'];
else
    $page = "";

session_start();
if (empty($_SESSION['csrf_token'])) {
    if (function_exists('mcrypt_create_iv')) {
        $_SESSION['csrf_token'] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
    } else {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
}
$csrf_token = $_SESSION['csrf_token'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Thomas Jacquin">

    <title>AllSky Admin Panel</title>

    <!-- Bootstrap Core CSS -->
    <link href="bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
	<!-- Make messages look nicer, and align the "x" with the message. -->
	<style>
		.alert {
			padding: 3px;
			margin-bottom: 0;
		}
		.alert-message {
			color: #31708f;
			background-color: #d9edf7;
			border-color: #bce8f1;
		}
		.alert-dismissable .close {
    			right: 0px;
			opacity: .6;
		}
	</style>
    <!-- MetisMenu CSS -->
    <link href="bower_components/metisMenu/dist/metisMenu.min.css" rel="stylesheet">

    <!-- Timeline CSS -->
    <link href="dist/css/timeline.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="dist/css/sb-admin-2.css" rel="stylesheet">

    <!-- Morris Charts CSS -->
    <link href="bower_components/morrisjs/morris.css" rel="stylesheet">

    <!-- Font Awesome -->
    <script defer src="js/all.min.js"></script>

    <!-- Custom CSS -->
    <link href="dist/css/custom.css" rel="stylesheet">

    <link rel="shortcut icon" type="image/png" href="img/allsky-favicon.png">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- RaspAP JavaScript -->
    <script src="dist/js/functions.js"></script>

    <!-- jQuery -->
    <script src="bower_components/jquery/dist/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="bower_components/metisMenu/dist/metisMenu.min.js"></script>

    <script src="js/bigscreen.min.js"></script>

    <script type="text/javascript">
        function getImage() {
            var img = $("<img />").attr('src', '<?php echo $image_name ?>?_ts=' + new Date().getTime())
                .attr("id", "current")
                .attr("class", "current")
                .css("width", "100%")
                .on('load', function () {
                    if (!this.complete || typeof this.naturalWidth == "undefined" || this.naturalWidth == 0) {
                        console.log('broken image!');
                        setTimeout(function () {
                            getImage();
                        }, 500);
                    } else {
                        $("#live_container").empty().append(img);
                    }
                });
        }

        // Inititalize theme to light
        if (!localStorage.getItem("theme")) {
            localStorage.setItem("theme", "light")
        }

    </script>

    <!-- Custom Theme JavaScript -->
    <script src="dist/js/sb-admin-2.js"></script>

    <link rel="stylesheet"
          href="lib/codeMirror/codemirror.min.css">

    <link rel="stylesheet"
          href="lib/codeMirror/monokai.min.css">

    <script type="text/javascript"
            src="lib/codeMirror/codemirror.min.js">
    </script>

    <script type="text/javascript"
            src="lib/codeMirror/shell.js">
    </script>
</head>
<body>

<div id="wrapper">
    <!-- Navigation -->
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php">
                <img src="img/allsky-logo.png">
                <div class="navbar-title">AllSky Administration Panel</div>
            </a>
        </div>
        <!-- /.navbar-header -->

        <!-- Navigation -->
        <div class="navbar-default sidebar" role="navigation">
            <div class="sidebar-nav navbar-collapse">
                <ul class="nav" id="side-menu">
                    <li>
                        <a href="index.php?page=live_view"><i class="fa fa-eye fa-fw"></i> Live View</a>
                    </li>
                    <li>
                        <a href="index.php?page=list_days"><i class="fa fa-image fa-fw"></i> Images</a>
                    </li>
                    <li>
                        <a href="index.php?page=camera_conf"><i class="fa fa-camera fa-fw"></i> Camera Settings</a>
                    </li>
                    <li>
                        <a href="index.php?page=editor"><i class="fa fa-code fa-fw"></i> Editor</a>
                    </li>
		    <li>
                        <a href="index.php?page=eth0_info"><i class="fa fa-tachometer-alt fa-fw"></i> <b>LAN</b> Connection Status</a>
                    </li>
                    <li>
                        <a href="index.php?page=wlan0_info"><i class="fa fa-tachometer-alt fa-fw"></i> <b>WLAN</b> Connection Status</a>
                    </li>
                    <li>
                        <a href="index.php?page=wpa_conf"><i class="fa fa-signal fa-fw"></i> Configure Wifi</a>
                    </li>
                    <!-- <?php if (RASPI_OPENVPN_ENABLED) : ?>
                        <li>
                            <a href="index.php?page=openvpn_conf"><i class="fa fa-lock fa-fw"></i> Configure OpenVPN</a>
                        </li>
                    <?php endif; ?>
                    <?php if (RASPI_TORPROXY_ENABLED) : ?>
                        <li>
                            <a href="index.php?page=torproxy_conf"><i class="fa fa-eye-slash fa-fw"></i> Configure TOR
                                proxy</a>
                        </li>
                    <?php endif; ?>-->
                    <li>
                        <a href="index.php?page=auth_conf"><i class="fa fa-lock fa-fw"></i> Change Password</a>
                    </li>
                    <li>
                        <a href="index.php?page=system_info"><i class="fa fa-cube fa-fw"></i> System</a>
                    </li>
                    <li>
                        <span onclick="switchTheme()"><i class="fa fa-moon fa-fw"></i> Light/Dark mode</span>
                    </li>

                </ul>
            </div><!-- /.navbar-collapse -->
        </div><!-- /.navbar-default -->
    </nav>

    <div id="page-wrapper">
        <div class="row right-panel">
            <div class="col-lg-12">
                <?php

                switch ($page) {
                    case "live_view":
                        DisplayLiveView("$image_name");
                        break;
                    case "wlan0_info":
                        DisplayDashboard();
                        break;
                    case "eth0_info":
                        DisplayDashboard_eth0();
                        break;
                    case "camera_conf":
                        DisplayCameraConfig();
                        break;
                    case "wpa_conf":
                        DisplayWPAConfig();
                        break;
                    case "auth_conf":
                        DisplayAuthConfig($config['admin_user'], $config['admin_pass']);
                        break;
                    case "system_info":
                        DisplaySystem();
                        break;
                    case "list_days":
                        ListDays();
                        break;
                    case "list_images":
                        ListImages();
                        break;
                    case "list_videos":
                        ListVideos();
                        break;
                    case "list_keograms":
                        ListKeograms();
                        break;
                    case "list_startrails":
                        ListStartrails();
                        break;
                    case "editor":
                        DisplayEditor();
                        break;
                    default:
                        DisplayLiveView("$image_name");
                }
                ?>
            </div>
        </div>
    </div><!-- /#page-wrapper -->
</div><!-- /#wrapper -->

<script type="text/javascript">
    $("body").attr("class", localStorage.getItem("theme"));

    function switchTheme() {
        if (localStorage.getItem("theme") === "light") {
            localStorage.setItem("theme", "dark");
        } else {
            localStorage.setItem("theme", "light");
        }
        $("body").attr("class", localStorage.getItem("theme"));
    }

	$("#live_container").click(function () {
            if (BigScreen.enabled) {
                BigScreen.toggle(this, null, null, null);
            } else {
                console.log("Not Supported");
            }
        });
</script>

</body>
</html>
