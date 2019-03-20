<?php
$report_file = "report.json";
$timeout_seconds = 60 * 15; // 15 min

header("content-type: application/json");
$last_run = @filemtime($report_file);
$cache_age = (time() - $last_run);

if($cache_age > $timeout_seconds || isset($_GET['refresh'])) {
    // regenerate report
    require "/mcec/lib/mcec_loader.php";
    require "xfinityscraper.class.php";
    require "xfbw.class.php";

    $xfbw = new XFBW('your@xfinity-email.com', 'your_xfinity_password');
    $xfbw->generateReport();
    $report_data = $xfbw->GetReport();

    @unlink($report_file);
    fwrite(fopen($report_file, "w"), json_encode($report_data));

    $cache_age = 0;
}

$report = json_decode(file_get_contents($report_file), true);
$report['cache_age'] = $cache_age;
$report['cache_refresh'] = $timeout_seconds - $cache_age;

echo json_encode($report);
?>