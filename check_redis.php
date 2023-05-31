<?php
// Check if Redis is installed and available
if (class_exists('Redis')) {
    echo 'Redis extension is installed.';
} else {
    echo 'Redis extension is not installed or not enabled.';
}
?>
