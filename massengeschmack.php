<?php

/**
 * @author Daniel Gehn <me@theinad.com>
 * @version 0.3a
 * @copyright 2014 Daniel Gehn
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */

require_once "provider.php";

class SynoFileHostingMassengeschmack extends TheiNaDProvider {

    protected $LogPath = '/tmp/massengeschmack.log';
    protected $cookiePath = '/tmp/mgcookie.txt';

    protected $loginUrl = 'http://massengeschmack.tv/login/';

    protected $curl;

    public function __construct($Url, $Username = '', $Password = '', $HostInfo = '', $Filename = '', $debug = false)
    {
        parent::__construct($Url, $Username, $Password, $HostInfo, $Filename, $debug);
        $this->curl = curl_init();
    }

    public function __destruct() {
        curl_close($this->curl);
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

    public function Verify($ClearCookie = '')
    {
        $this->DebugLog("Verifying User");
        curl_setopt($this->curl, CURLOPT_URL, $this->loginUrl);
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, 'email=' . $this->Username . '&password=' . $this->Password);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:11.0) Gecko/20100101 Firefox/11.0");
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookiePath);

        $html = curl_exec($this->curl);

        if (!$html) {
            $this->DebugLog("Failed to retrieve XML. Error Info: " . curl_error($this->curl));
            return USER_IS_FREE;
        }

        file_put_contents(dirname(__FILE__) . 'login.txt', $html);

        if(strpos($html, 'logout.php') !== false) {
            return USER_IS_PREMIUM;
        }

        return USER_IS_FREE;
    }

    //This function get premium download url.
    protected function Download() {
        curl_setopt($this->curl, CURLOPT_URL, $this->Url);
        curl_setopt($this->curl, CURLOPT_POST, false);

        $html = curl_exec($this->curl);

        if (!$html) {
            $this->DebugLog("Failed to retrieve XML. Error Info: " . curl_error($this->curl));
            return false;
        }

        $title = "";

        if(preg_match('#<title>(.*?)\s*-\s*Massengeschmack-TV<\/title>#si', $html, $match) === 1) {
            $title = $match[1];
        }

        preg_match_all('#href="(.*?)\.mp4"#i', $html, $matches);

        $bestQualityUrl = "";

        foreach($matches[1] as $match) {
            if(strpos($match, 'HD'))
            {
                $bestQualityUrl = $match;
            }
            else if($bestQualityUrl == "") {
                $bestQualityUrl = $match;
            }
        }

        $bestQualityUrl =  "http://massengeschmack.tv" . $bestQualityUrl . ".mp4";

        $url = trim($bestQualityUrl);
        $basicAuthString = urlencode($this->Username) . ':' . urlencode($this->Password) . '@';
        $basicAuthUrl = str_replace(array('http://', 'https://'), array('http://' . $basicAuthString, 'https://' . $basicAuthString), $url);

        $DownloadInfo = array();
        $DownloadInfo[DOWNLOAD_URL] = $basicAuthUrl;
        $DownloadInfo[DOWNLOAD_FILENAME] = $this->buildFilename($url, $title);

        return $DownloadInfo;
    }

    protected function buildFilename($url, $title = "") {
        $pathinfo = pathinfo($url);

        if(!empty($title))
        {
            $filename = $title . '.' . $pathinfo['extension'];
        }
        else
        {
            $filename =  $pathinfo['basename'];
        }

        return $this->safeFilename($filename);
    }
}
?>
