<?php
define('ALLSKY_HOME', '/home/pi/allsky');                     // value updated during installation

$content = "";
$path = "";

// On error, return a string.  On success, return nothing.

if (isset($_POST['content'])) {
	$content = $_POST['content'];
}

if (isset($_POST['path'])) {
	$path = $_POST['path'];
}
if ($path == "") {
	echo "save_file.php: Path to save to is null";
	exit;
}

// "current" is a web alias to ALLSKY_HOME.
$file = str_replace('current', ALLSKY_HOME, $path);
$tempFile = getcwd() . "/temp";
if (file_put_contents($tempFile, $content) == false) {
	echo error_get_last()['message'];
	exit;
} else {
	// shell_exec() doesn't return anything unless there is an error.
	$msg = shell_exec("sudo mv '$tempFile' '$file' || echo 'Unable to mv [$tempFile] to [$file]'");
	if ($msg == "") {
		shell_exec("sudo chown pi:pi '$file'; sudo chmod +x '$file'");
	} else {
		//header("HTTP/1.0 400 Bad Request");
		echo "save_file.php: $msg";
		exit;
	}
}
?>
