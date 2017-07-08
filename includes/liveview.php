<?php

function DisplayLiveView(){

  $status = new StatusMessages();
  ?>
<script src="../bower_components/jquery/dist/jquery.min.js"></script>
<script type="text/javascript">
    var getImage = function () {
    	var img = $("<img />").attr('src', 'image.jpg?_ts=' + new Date().getTime())
        .on('load', function() {
            if (!this.complete || typeof this.naturalWidth == "undefined" || this.naturalWidth == 0) {
                alert('broken image!');
                $timeout(function(){
                    getImage();
                }, 500);
            } else {
                $("#live_container").empty().append(img);
            }
        });
    };
    var intervalFunction = function () {
        setTimeout(function () {
            getImage();
            intervalFunction();
        }, 5000)
    };
    $( document ).ready(function() {
console.log("hello");
	intervalFunction();
    });
    
</script>
  <div class="row">
	<div id="live_container">
      	<img id="current" class="current" src="loading.jpg">
  	</div>
  </div>
  <?php 
}

?>
