<?php

  define('RASPI_CONFIG', '/etc/raspap');

  $file = '/home/pi/allsky/config.sh';
  $searchfor = 'CAMERA=';

  // get the file contents, assuming the file to be readable (and exist)
  $contents = file_get_contents($file);
  // escape special characters in the query
  $pattern = preg_quote($searchfor, '/');
  // finalise the regular expression, matching the whole line
  $pattern = "/^.*$pattern.*\$/m";
  // search, and store all matching occurences in $matches
  if(preg_match_all($pattern, $contents, $matches)){
        $double_quote = '"';
        $cam = str_replace($double_quote, '', explode( '=', implode("\n", $matches[0]))[1]);
  }
  else{
    $cam = "ZWO";
  }

  define('RASPI_CAMERA_SETTINGS', RASPI_CONFIG . '/settings_'.$cam.'.json');

  $camera_settings_str = file_get_contents(RASPI_CAMERA_SETTINGS, true);
  $camera_settings_array = json_decode($camera_settings_str, true);

?>

<style>
body {
	margin: 0;
}
</style>

<div class="row">
   <div id="live_container" style="background-color: black;">
   	<img id="current" class="current" src="current/liveview-<?php echo $camera_settings_array['filename'] ?>" style="width:100%">
   </div>
</div>

<!-- jQuery -->
<script src="bower_components/jquery/dist/jquery.min.js"></script>

<script type="text/javascript">
                function getImage(){
                        var img = $("<img />").attr('src', 'current/liveview-<?php echo $camera_settings_array["filename"] ?>?_ts=' + new Date().getTime())

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
                }, <?php echo $camera_settings_array["exposure"]/1000 < 5000 ? 5000 : $camera_settings_array["exposure"]/1000?>);

 </script>

