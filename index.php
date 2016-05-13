
<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

foreach (glob("./*/*.php") as $filename)
{
include $filename;
}

$HTML =new app_ads();


?>
