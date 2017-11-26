<?php

function delete_directory($dirname) {
	$result = shell_exec("sudo chgrp -R www-data /home/pi/allsky/images/");
	$result = shell_exec("sudo chgrp -R www-data /home/pi/allsky/images/*");
     if (is_dir($dirname))
           $dir_handle = opendir($dirname);
	 if (!$dir_handle)
	      return false;
	 while($file = readdir($dir_handle)) {
	       if ($file != "." && $file != "..") {
	            if (!is_dir($dirname."/".$file))
	                 unlink($dirname."/".$file);
	            else
	                 delete_directory($dirname.'/'.$file);
	       }
	 }
	 closedir($dir_handle);
	 rmdir($dirname);
	 return true;
}

function ListDays(){

$days = array();

if (isset($_POST['delete_directory'])) {
	$dir = $_POST['delete_directory'];
  echo '<div class="alert alert-warning">Deleted directory '.$dir.'</div>';
  delete_directory('/home/pi/allsky/images/'.$dir);
}

if ($handle = opendir('/home/pi/allsky/images/')) {
    $blacklist = array('.', '..', 'somedir', 'somefile.php');
    while (false !== ($day = readdir($handle))) {
        if (!in_array($day, $blacklist)) {
            $days[] = $day;
        }
    }
    closedir($handle);
}

asort($days);

?>
<div class="row">
  <div class="col-lg-12">
  <div class="panel panel-primary">
  <div class="panel-heading"><i class="fa fa-image fa-fw" style="margin-right: 10px"></i>Images</div>
  <div class="panel-body">
    <div class="row">
	<form action="?page=list_days" method="POST" onsubmit="return confirm('Are you sure you want to delete ALL images for that day?');">
	<table style='margin: 20px'>
		<tbody>
<?php
foreach ($days as $day) {
	echo "<tr>
				<td style='width:100px'><a href='index.php?page=list_images&day=$day'>$day</a>
				</td>
				<td style='padding: 5px'>
					<button type='submit' 
						class='btn btn-danger' 
						data-toggle='confirmation' 
						name='delete_directory' 
						value='$day' 
						style='width: 90px; text-align: center, color:white'>
					<i class='fa fa-trash text-danger' style='color:white'></i> Delete</button>
				</td>
			</tr>";
}
?>
		</tbody>
	</table>
	</form>
    </div><!-- /.row -->
	</div><!-- /.panel-body -->
  </div><!-- /.panel-primary -->
  </div><!-- /.col-lg-12 -->
  </div><!-- /.row -->
  <?php 
}

?>


