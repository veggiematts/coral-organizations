<?php

$dir = new RecursiveDirectoryIterator(realpath('../docelec'));


foreach (new RecursiveIteratorIterator($dir) as $filename=>$cur) {
    if (preg_match('/contact/i', $filename)) {
        $command = 'php import_contacts.php "' . $filename . '"';
        echo "\n\n$command\n";
        system($command); 
    }
}

?>
