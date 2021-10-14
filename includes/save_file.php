<?php
define('ALLSKY_HOME', '/home/pi/allsky');                     // value updated during installation

$content = "";
$path = "";

if (isset($_POST['content'])) {
	$content = $_POST['content'];
}

if (isset($_POST['path'])) {
	$path = $_POST['path'];
}
if ($path == "") {
	echo "save_file.php: Unknown path to save to, is null";
	exit;
}

$file = str_replace('current', ALLSKY_HOME, $path);
$tempFile = getcwd() . "/temp";
if (file_put_contents($tempFile, $content) == false) {
    echo "<p style='color: red'>Unable to save to temporary file '$tempFile'.</p>";
    echo "<br>Contents:<br><pre>$content</pre>";
	exit;
} else {
	// This shouldn't return anything unless there is an error.
	$msg = shell_exec("sudo mv '$tempFile' '$file' || echo 'Unable to mv $tempFile to $file'; sudo chown pi:pi '$file'; sudo chmod +x '$file'");
	if ($msg != "")
		echo "<p style='color: red'>save_file.php: $msg</p>";
}
?>
