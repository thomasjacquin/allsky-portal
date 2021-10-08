<?php

$content = "";
$path = "";

if (isset($_POST['content'])) {
    $content = $_POST['content'];
}

if (isset($_POST['path'])) {
    $path = $_POST['path'];
}

$file = str_replace('current', ALLSKY_HOME, $path);

file_put_contents(getcwd()."/temp", $content);
shell_exec("sudo mv ".getcwd()."/temp ".$file . ";sudo chown pi:pi ".$file . ";sudo chmod +x ".$file);

?>
