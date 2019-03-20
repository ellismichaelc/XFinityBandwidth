<?php
require "/mcec/lib/mcec_loader.php";
require "xfinityscraper.class.php";
require "xfbw.class.php";

$xfbw = new XFBW('your@xfinity-email.com', 'your_xfinity_password');
$xfbw->generateReport();
$xfbw->PrintReport();
?>