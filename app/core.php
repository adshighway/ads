<?php


require ('./lib/helper/general.php');

class app_core {

    public $hash;
    public $action;
    public $isRest;
    public $lastUrl;
    public $parameters=array();
    public $longParameters=array();
    public $ip;
    public $session;
    public $baseURL;
 
 
        public function __construct($string = '0000000') {
        $keyhash = md5($string);
        $this->hash = $keyhash;
    }

    /*
     * Generate on Fly Methods on PHP
     * 
     * 
     */

    public function __call($method, $args) {
        if (isset($this->$method)) {
            $func = $this->$method;
            return call_user_func_array($func, $args);
        }
    }
    
    
     /*
     * Get Current Url On Page
     * 
     * @param $type full or short URL
     * 
     */
    
    public function curPageURL($type = '') {
        if ($type == 'full') {
            $pageURL = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        } elseif($type == 'request') {
            $pageURL = $_SERVER[REQUEST_URI];
        }else{
            $pageURL = "http://$_SERVER[HTTP_HOST]/";
        }
        return $pageURL;
    }
    
    public function checkPageURL($word){
        
        
        if (strpos($this->action,$word) !== false) {
            
               if($this->action==$word){
                 return 1;  
               }else{
                 return 2;  
               }
            
        }else{
            return 0;
        }
        
        
    }
    
        /*
     * Get TRUE Request and Set Parameters;
     * 
     * 
     */

    public function Action() {
         
        $this->ip = $this->getIpClient();

        if ($this->action == '') {
          
            $urlstring = $_SERVER['REQUEST_URI'];
       
            if (strpos($urlstring, '?type=rest') !== false) {
                $this->isRest = 1;
            } else {
                $this->isRest = 0;
            }

            if (strpos($urlstring, '?') !== false) {
                $this->clearURL = 0;
                $fullURL = explode('?', $urlstring);
                $urlstring = $fullURL[0];
                $parameterFull = explode('&', $fullURL[1]);
                foreach ($parameterFull as $key => $parameter) {
                    if (strpos($parameter, '=') !== false) {
                        
                        $parameterData = explode('=', $parameter);
                        
                        $this->parameters[$parameterData[0]] = $parameterData[1];
                    } else {
                       
                        $this->longParameters[$key] = $parameter;
                    }
                }
            } else {
                $this->clearURL = 1;
            }

            $urlstring = substr($urlstring, 1);
            
            if ($urlstring == '') {
         
                $urlstring = '/';
            }
         
            $this->action = $urlstring;
            
            if($urlstring=='admin'){
                session_start();
                $_SESSION['user'] = 'default';
                $_SESSION['privilege'] = 'default';
                $_SESSION['area'] = 'default';
                $_SESSION['history'] = 'home,admin';
            }else{
                session_start();
                $_SESSION['user'] = 'default';
                $_SESSION['privilege'] = 'default';
                $_SESSION['area'] = 'default';
                $_SESSION['history'] = 'default';
            }
           

        } else {
            
            $urlstring = $this->action;
        }
  
        return $urlstring;
    }

    /*
     * Get Current Ip Client
     * 
     * @return string ip adress
     */

    public function getIpClient() {

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $this->ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $this->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $this->ip = $_SERVER['REMOTE_ADDR'];
        }
        return $this->ip;
    }
    
    
    function Redirect($url, $permanent = false)
    {
    if (headers_sent() === false)
    {
    	header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }

    exit();
    }
    
    
    function getLastUrl(){
        
        return $_SERVER['HTTP_REFERER'];
        
    }
    
    
    public function info(){
        
        return "INFO";
    }

}


