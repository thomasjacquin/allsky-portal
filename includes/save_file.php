<?php

$content = "";
$path = "";

if (isset($_POST['content'])) {
    $content = $_POST['content'];
}

if (isset($_POST['path'])) {
    $path = $_POST['path'];
}

$file = str_replace('current','/home/pi/allsky', $path);

file_put_contents(getcwd()."/temp", $content);

echo "sudo mv ".getcwd()."/temp ".$file;
echo "sudo chmod +x ".$file;
shell_exec("sudo mv ".getcwd()."/temp ".$file);
shell_exec("sudo chown pi:pi ".$file);
shell_exec("sudo chmod +x ".$file);

?>
