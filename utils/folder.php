<?php
function deleteFolderRecursive($folder) {
    if (!is_dir($folder)) 
        return;

    $items = scandir($folder);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') 
            continue;

        $path = $folder . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            deleteFolderRecursive($path);
        } else {
            unlink($path);
        }
    }

    rmdir($folder);
}
?>
