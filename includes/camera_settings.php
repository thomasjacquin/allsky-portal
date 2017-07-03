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
          <h4>Camera settings</h4>

          <form method="POST" action="?page=camera_conf" name="camera_conf_form">
            <?php CSRFToken() ?>
            <input type="hidden" name="camera_settings" ?>
            
          </form>
        </div><!-- ./ Panel body -->
        <!--<div class="panel-footer"><strong>Note,</strong> WEP access points appear as 'Open'. Allsky Camera Portal does not currently support connecting to WEP.</div>-->
      </div><!-- /.panel-primary -->
    </div><!-- /.col-lg-12 -->
  </div><!-- /.row -->
<?php
}

?>
