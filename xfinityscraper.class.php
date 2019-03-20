<?php

class xfinityscraper extends mcec_scraper {

    public function init() {
        // all variables are accessible from here as they are from the root of the app
        // such as $this->_email, $this->_url, etc.

        $this->error_string = "Authentication failed";

        $this->login_post_params = [
                        'user' => $this->_email,
                        'passwd' => $this->_pass,
                        'rm' => '1',
                        'r' => 'comcast.net',
                        's' => 'oauth',
                        'deviceAuthn' => 'false',
                        'ipAddrAuthn' => 'false',
                        'forceAuthn' => '0',
                        'lang' => 'en',
                        'passive' => 'false',
                        'client_id' => 'my-xfinity'
                        ];

        $this->login_url = $this->_url_login;
        $this->url = $this->_url;
    }

    public function start() {

        if(!$this->_login()) {
            return $this->getLastError();
        }

        if(!$data = $this->_scrape()) {
            return $this->getLastError();
        }

        if(strstr($data, 'unauthenticated')) {
            return $this->error("Couldn't get report from XFinity (unauthenticated)");
        }

        return $data;
    }

}

?>