<?php

class xfbw extends mcec_app {

    public $_name = "XFinity Bandwidth Scraper";
    public $_desc = "Retreive bandwidth information, print as json for import into other applications";
    public $_ver  = "0.0.1";

    public function init($email, $pass) {
        //$this->debug(true);

        $this->_email = $email;
        $this->_pass = $pass;
        $this->_url_login = "https://login.xfinity.com/login?r=comcast.net&s=oauth&continue=https%3A%2F%2Foauth.xfinity.com%2Foauth%2Fauthorize%3Fclient_id%3Dmy-account-web%26prompt%3Dlogin%26redirect_uri%3Dhttps%253A%252F%252Fcustomer.xfinity.com%252Foauth%252Fcallback%26response_type%3Dcode%26state%3D%2523%252Fservices%252Finternet%26response%3D1&forceAuthn=1&client_id=my-account-web";
        $this->_url = "https://customer.xfinity.com/apis/services/internet/usage";
    }

    public function generateReport() {
        $report = ['result' => false];

        // make a new XF scraper, call scrape, then try to regex our shit out?

        $xf = new XFinityScraper();
        if(!$scrape = $xf->scrape()) {
            $report['error'] = $xf->getLastError();
        } else {

            $json_report = json_decode($scrape, true);
            //$report['data'] = $json_report;

            if(!$json_report) {
                $report['error'] = "Couldn't parse JSON report from XFinity";
            } else {

                // we're far enough
                $report['result'] = true;

                // convert report
                $months = $json_report['usageMonths'];
                $last_month = end($months);

                // set report values for data
                $report['plan'] = $last_month['policyName'];
                $report['used'] = $last_month['homeUsage'];
                $report['allowed'] = $last_month['allowableUsage'] > 0 ? $last_month['allowableUsage'] : "Unlimited";
                $report['remaining'] = (int) $report['allowed'] - (int) $report['used'];
                $report['units'] = $last_month['unitOfMeasure'];
                $report['percent_used'] = $last_month['allowableUsage'] > 0 ? (float) number_format(($last_month['homeUsage'] / $last_month['allowableUsage']) * 100, 1) : 'Unlimited';
                $report['percent_left'] = $last_month['allowableUsage'] > 0 ? 100 - $report['percent_used'] : 'Unlimited';

                // dates
                $report['start'] = $last_month['startDate'];
                $report['end'] = $last_month['endDate'];
                $report['days_in'] = date_diff(date_create($last_month['startDate']), date_create(date('m/d/Y')))->days + 1;
                $report['days_left'] = date_diff(date_create(date('m/d/Y')), date_create($last_month['endDate']))->days;

                // do math
                $report['usage_per_day'] = round($report['used'] / $report['days_in'], 2);
                $report['est_usage_forecast'] = round($report['usage_per_day'] * $report['days_left'], 2);
                $report['est_usage_total'] = round($report['est_usage_forecast'] + $report['used'], 2);
                $report['on_track'] = !($report['est_usage_total'] > $report['allowed']) ? "true" : "false";
                $report['gone_over'] = $json_report['inPaidOverage'] ? "true" : "false";
                $report['credits'] = $json_report['courtesyRemaining'] . ' of ' . $json_report['courtesyAllowed'];

                // shove the whole month in there why not
                //$report['usage'] = $json_report;
            }
        }

        $this->report = $report;
    }

    public function PrintReport() {
        echo json_encode($this->report);
    }

    public function GetReport() {
        return $this->report;
    }

}

?>