<?php
/**
 * Zls
 * @author        影浅
 * @email         seekwe@gmail.com
 * @copyright     Copyright (c) 2015 - 2017, 影浅, Inc.
 * @link          ---
 * @since         v0.0.1
 * @updatetime    2018-02-01 15:01
 */
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];
$REQUEST_URI = explode('?', $_SERVER["REQUEST_URI"]);
$indexs = ['index.php', 'default.php'];
if (file_exists($DOCUMENT_ROOT . $REQUEST_URI[0])) {
    return false;
} else {
    foreach ($indexs as $index) {
        $indexFile = $DOCUMENT_ROOT . '/' . $index;
        if (file_exists($indexFile)) {
            include $indexFile;
            break;
        }
    }
}
