<?php

  define('RASPI_CAMERA_SETTINGS', './settings.json');
  $camera_settings_str = file_get_contents(RASPI_CAMERA_SETTINGS, true);
  $camera_settings_array = json_decode($camera_settings_str, true);

?>

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
                }, <?php echo $camera_settings_array["exposure"]/1000 ?>);

 </script>

