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
$SCRIPT_FILENAME = isset($_SERVER["SCRIPT_FILENAME"]) ? $_SERVER["SCRIPT_FILENAME"] : $DOCUMENT_ROOT . '/index.php';
$REQUEST_URI = explode('?', $_SERVER["REQUEST_URI"]);
if (file_exists($DOCUMENT_ROOT . $REQUEST_URI[0])) {
    return false;
} else {
    require_once $SCRIPT_FILENAME;
}
