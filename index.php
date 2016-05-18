
<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

foreach (glob("./*/*.php") as $filename)
{
include $filename;
}

$CORE    = new app_core();

$GENERAL = new general();

$DATA = new app_data($CORE,$GENERAL);

?>
