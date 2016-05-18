<?php


require ('./lib/redis/Autoloader.php');


class app_redis extends app_core {
    
    public $db;
    public $object;
    public $numeric = 0;
    public $history;
    public $current = array();
    public $methods = array();
    public $cache = array();
    public $message = '';
    public $helper;
    public $redisActive = FALSE;
    public $toLook = array();
    
    /*
     * 
     * Build a Redis Connection 
     * 
     * 
     * 
     */
    
    public function __construct(app_core $data,general $helper) {
        $this->object=$data;
        $this->helper=$helper;
        $keyhash = md5($data->hash);
        $this->hash = $keyhash;
        
        $status = $this->getConnection();
        
        
        if($status){
        
        $redis = $this->db;
        
        if(!$redis->exists('data')){
        $string = file_get_contents("./models/store/data.json");
        $json= json_decode($string, true); 
        $this->setData('data',$json);
        }
        $object = $this->getData('data');
        
        $this->cache = $object;
        }
        
    }
    
   public function getConnection() {

        Predis\Autoloader::register();

        try {
            if (isset($_ENV['REDISCLOUD_URL'])) {
                $redis = new Predis\Client(array('host' => parse_url($_ENV['REDISCLOUD_URL'], PHP_URL_HOST), 'port' => parse_url($_ENV['REDISCLOUD_URL'], PHP_URL_PORT), 'password' => parse_url($_ENV['REDISCLOUD_URL'], PHP_URL_PASS)));
                $this->db = $redis;

                $redis->client('setname', 'veterinarica');
                $this->nameConection = $redis->client('getname');
                
		

                // gets the value of message
                if(!$redis->exists('message')){
                 $redis->set('redis','OK'); 
                }
        
                $this->message = $redis->get('redis');

                            } else {

                                //$redis = new Predis\Client();
                                $redis = new Predis\Client(array(
                                            "scheme" => "tcp",
                                            "host" => "127.0.0.1",
                                            "port" => 6379,
                                            "name" => "veterinarica"));
                                $this->db = $redis;
                if(!$redis->exists('message')){
                $redis->set('redis','OK');
                }
                // gets the value of message
                $this->message = $redis->get('redis');

               
                            }
                         $this->redisActive=TRUE;   
                         return TRUE;   
                        } catch (Exception $e) {
                          
                            
                             $error = $e->getMessage();
                             
                        $this->helper->toLog($error);
                        $this->redisActive= FALSE;     
                        return FALSE;
                            
                        }
    }


    /*
     * Database REDIS
     * 
     * 
     * 
     */

    
    public function getData($keyRedis){
              if($this->redisActive){
                  
              $redis =$this->db;
              $object = array();
              if($redis->exists($keyRedis)){
                $type = $redis->type($keyRedis);
               if($type=='hash'){
                $obj = $redis->hgetall($keyRedis);
                if(is_array($obj)){
                    foreach($obj as $ky=>$ob){
  
                        $kj = $keyRedis.'.'.$ky;         
                        if($redis->exists($kj)){
                            $_type = $redis->type($kj);
                           
                           if($_type=='hash'){ 
                           $sob = $redis->hgetall($kj);
                        foreach($sob as $k=>$sb){
                                $kr = $keyRedis.'.'.$ky.'.'.$k;
                            if($redis->exists($kr)){                             
                                $subObject = $this->getData($kr);
                                $sob[$k] = $subObject;
                            }else{
                                $sob[$k]=$sb;
                            }
                        }  
                        
                            $object[$ky] = $sob;                              
                           }else{
                              $_longList = $redis->llen($kj);
                              
                              $_obj = $redis->lrange($kj,0,$_longList);
                              
                              foreach($_obj as $k=>$sb){
                                  $kr = $keyRedis.'.'.$ky.'.'.$k;
                                 if($redis->exists($kr)){                             
                                 $subObject = $this->getData($kr);
                                 $_obj[$k] = $subObject;
                                 }else{
                                 $_obj[$k]=$sb;
                                 }                                  
                                  
                              }
                            $object[$ky] = $_obj;  
                           }
                            
                        }else{
                            $object[$ky] = $ob;
                        }
                    }
                }  
       
               return $object; 
               }elseif($type=='string'){
                   $value = $redis->get($keyRedis);
                   return array($value);
               }
               else{
                  
                  $longList = $redis->llen($keyRedis);
                   
                  $obj = $redis->lrange($keyRedis,0,$longList); 
                  
                  foreach($obj as $ky=>$ob){
  
                        $kj = $keyRedis.'.'.$ky;         
                      
                        if($redis->exists($kj)){
                            
                           $_type = $redis->type($kj);
                           
                           if($_type=='hash'){ 
                           $sob = $redis->hgetall($kj);
                        foreach($sob as $k=>$sb){
                                $kr = $keyRedis.'.'.$ky.'.'.$k;
                            if($redis->exists($kr)){                             
                                $subObject = $this->getData($kr);
                                
                                $sob[$k] = $subObject;
                            }else{
                                $sob[$k]=$sb;
                            }
                        }   
                           
                            $object[$ky] = $sob;                              
                           }else{
                              $_longList = $redis->llen($kj);
                              
                              $_obj = $redis->lrange($kj,0,$_longList);
                             
                              foreach($_obj as $k=>$sb){
                                  $kr = $keyRedis.'.'.$ky.'.'.$k;
                                 if($redis->exists($kr)){                             
                                 $subObject = $this->getData($kr);
                                 
                                 $_obj[$k] = $subObject;
                                 }else{
                                 $_obj[$k]=$sb;
                                 }                                  
                                  
                              }
                            $object[$ky] = $_obj;  
                           }
                            
                            
                        }else{                        
                            $object[$ky] = $ob;
                        }
                        
                    }
                   
               return $object;    
               }
              }else{
                 
               return $object;    
              }     
              }else{
               return array();   
              }
    }
    
       /*
        *  Build a Data Base Structure Demo 
        * 
        * 
        * 
        */
    
    
    public function setData($KEY,$OBJECT){
  
        $redis =$this->db;
        
        foreach($OBJECT as $key=>$data){
            if(is_array($data)){
                $K = $KEY.'.'.$key;
                
                $redis->hset($KEY,$key,$K);
                
                $this->setData($K,$data);
            }else{
                $redis->hset($KEY,$key,$data);
             
            }
        }
        
  
    }
    
    public function delete($KEY){
        
        $redis = $this->db;
        
        $this->lookData($KEY);
        
        $deltes = $this->toLook;
        
        foreach($deltes as $delte){
            
            $redis->del($delte);
            
        }
        
    }
    
    
    public function timeOut($KEY,$time=20){
    
        $redis = $this->db;
        
        $this->lookData($KEY);
        
        $deltes = $this->toLook;
        
        foreach($deltes as $delte){
            
            $redis->expire($delte,$time);
            
        }    
        
        
    }
    
    
    
    public function lookData($KEY){
        
        $redis = $this->db;
        
           if($redis->exists($KEY)){
                $type = $redis->type($KEY);
               if($type=='hash'){
                   $aks = $redis->hgetall($KEY);  
                   $this->toLook[]=$KEY;  
                   foreach($aks as $ks=>$ys){
                     if (strpos($ys, '.') !== false) {                     
                      $this->lookData($ys);       
                     }
                   }                                  
               }
           }
    }
    
    
    public function hasData($KEY){
        
        
        $redis = $this->db;
        
         if($redis->exists($KEY)){
                $type = $redis->type($KEY);
               if($type=='hash'){
                     return array('hash'=>TRUE);
                 
               }elseif($type=='string'){
                     return array('string'=>TRUE);
               }else{
                     return array('null'=>FALSE);
               }
         }
        
        
    }
    
    
  
    
    
    
    /*
     * 
     * 
     * 
     * 
     * 
     */
    public function setTranslate($word,$translate,$language='english'){
        
        
        
    }
    
   
    
    
    /*
     * Translate languages, Use default english 
     * @param $word string
     * @param $language string
     * @return $translateWord string 
     */
    
    public function translate($word,$language='english'){
        
       $redis = $this->db;
       if($redis->exists('translate:'.$language)){
            $translateWord = $redis->hget('translate:'.$language,$word);
       if($translateWord!=null){
            return $translateWord;
       }else{
            return $word;    
       }
       }else{
            return $word;    
       } 
    }

}


