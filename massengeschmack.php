<?php

/**
 * @author Daniel Gehn <me@theinad.com>
 * @version 0.2
 * @copyright 2014 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */

class SynoFileHostingMassengeschmack {
    private $Url;
    private $Username;
    private $Password;
    private $HostInfo;

    public function __construct($Url, $Username, $Password, $HostInfo) {
        $this->Url = $Url;
        $this->Username = $Username;
        $this->Password = $Password;
        $this->HostInfo = $HostInfo;
    }

    //This function returns download url.
    public function GetDownloadInfo() {
        $ret = FALSE;
        $VerifyRet = $this->Verify();

        if (USER_IS_PREMIUM == $VerifyRet) {
            $ret = $this->Download();
        }

        return $ret;
    }

    //This function verifies and returns account type.
    public function Verify()
    {
        return USER_IS_PREMIUM;
    }

    //This function get premium download url.
    private function Download() {
        $DownloadUrl = str_replace(array('http://', 'https://'), array('http://'.urlencode($this->Username).':'.urlencode($this->Password).'@', 'https://'.$this->Username.':'.$this->Password.'@'), $this->Url);

        $DownloadInfo = array();
        $DownloadInfo[DOWNLOAD_URL] = trim($DownloadUrl);

        return $DownloadInfo;
    }
}
?>
