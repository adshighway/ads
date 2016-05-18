<?php

class app_data extends app_core {

    public $DB;
    public $mongoDB;
    public $object;
    public $numeric = 0;
    public $history;
    public $current = array();
    public $methods = array();
    public $data = array();
    public $datasend = array();
    public $isObject = 0;
    public $mongoActive =FALSE;

    public function __construct(app_core $data,general $helper) {
        $this->object = $data;
        $keyhash = md5($data->hash);
        $this->hash = $keyhash;
        $this->helper=$helper;
        $this->connection();
       
    }

    /*
     * Build Services
     *
     *
     */

    public function connection() {


        if (isset($_SERVER['SERVER_ADDR'])) {
            $currentHost = $_SERVER['SERVER_ADDR'];
        } else {
            $currentHost = '';
        }

        if ($currentHost == '127.0.0.1') {

            $host = '127.0.0.1';
            $database = 'general';
            $this->mongoDB = $database;
            $port = '27017';
            //$username = 'adrian';
            $username='general';
            $password = 'ak540zthe900';

            $connecting_string = sprintf('mongodb://%s:%d/%s', $host, $port, $database);
            try{
            $connection = new MongoClient($connecting_string, array('username' => $username, 'password' => $password));
            
            $this->DB = $connection->selectDB('general');
            $this->mongoActive = TRUE;
            }catch(Exception $e){
                $this->mongoActive = FALSE;
                var_dump($e);
                $this->helper->toLog($e);
            }
        } else {

# get the mongo db name out of the env
// $mongo_url = parse_url(getenv("MONGO_URL"));
//$dbname = str_replace("/", "", $mongo_url["path"]);

            $host = 'ds057224.mongolab.com';
            $database = 'heroku_91nxcxtf';
            $this->mongoDB = $database;
            $port = '57224';
            $username = 'general';
            $password = 'ak540zthe900';

            $connecting_string = sprintf('mongodb://%s:%d/%s', $host, $port, $database);
            
             try{
            $connection = new MongoClient($connecting_string, array('username' => $username, 'password' => $password));
            $this->DB = $connection->selectDB('heroku_91nxcxtf');
            $this->mongoActive = TRUE;
             }catch(Exception $e){
             $this->mongoActive = FALSE;    
             $this->helper->toLog($e);   
             }
        }
    }

    public function createUsers($username, $password) {

        $dbname = $this->DB;

        $collection = $db->selectCollection("system.users");
        $collection->insert(array('user' => $username, 'pwd' => md5($username . ":mongo:" . $password), 'readOnly' => false));
    }

    /*
     * 
     * 
     * 
     * 
     * 
     */

    public function getUsers($name) {

        $dbname = $this->DB;
        $username = $dbname->command(array('usersInfo' => array('user' => $name, 'db' => $this->mongoDB)));
        return $username;

//      foreach($dbname->selectCollection('system.users')->find() as $user){
//       var_dump($user);
//       echo "<br>";
//      }
    }


    public function show($object, $num = 0) {
        $Array = array();

        foreach ($object as $ob) {


            $Array[] = $ob;
        }

        if ($num == 0) {
            return $Array[0];
        } else {
            return $Array;
        }
    }

    public function matchData($SQUEMA = 'users', $ENTITY, $DATA) {

        $string = file_get_contents("./models/" . $ENTITY . "/" . $SQUEMA . ".json");

        $JSON = json_decode($string, true);

        $this->toData($JSON, $ENTITY, $DATA);

        $this->inData($DATA, $ENTITY);

        return $this->setData();
    }

    public function setData() {

        $Data = array();

        $command = '$Data';

        foreach ($this->data as $kys => $setData) {

            if (isset($this->datasend[$kys])) {
                $setValues = $this->datasend[$kys];
                if (is_object($setValues)) {
                    $this->isObject = 1;
                } else {
                    $this->isObject = 0;
                }
            } else {
                $setValues = NULL;
            }
            $identifies = explode('.', $kys);

            foreach ($identifies as $k => $sets) {


                $command .= '["' . $sets . '"]';
            }


            if ($setValues != NULL) {
                if ($this->isObject == 1) {
                    $stringValue = var_export($setValues);



                    $command .='= \'' . $setValues . '\';';
                } else {
                    $command .='= \'' . $setValues . '\';';
                }
            } else {
                $command .='= \'' . $setData . '\';';
            }


            eval($command);


            $command = '$Data';
        }

        return $Data;
    }

    public function inData($DATA, $ENTITY) {

        foreach ($DATA as $Keys => $data) {
            if (is_array($data)) {
                $this->inData($data, $ENTITY . '.' . $Keys);
            } else {
                $this->datasend[$ENTITY . '.' . $Keys] = $data;
            }
        }
    }

    public function toData($JSON, $ENTITY, $VALUES) {

        $NAME = '';
        $VALUE = NULL;
        $ENITY_DEEP = '';

        foreach ($JSON as $key => $data) {



            if (!is_array($data)) {

                if ($key == "title") {
                    $ENTITY_DEEP = $data;
                    $NAME = $data;
                }
                if ($key == "value") {
                    $VALUE = $data;
                }
            }


            if ($key == "properties") {

                $this->properties($data, $ENTITY, $ENTITY_DEEP, $VALUES);
            }
        }
    }

    public function properties($JSON, $ENTITY, $ENTITY_DEEP, $VALUES) {


        foreach ($JSON as $key => $data) {

            if (isset($data['value'])) {

                if ($data['type'] == 'string') {

                    if (is_string($data['value'])) {
                        $this->data[$ENTITY . '.' . $ENTITY_DEEP . '.' . $data['title']] = $data['value'];
                    }
                }
                if ($data['type'] == 'integer') {
                    if (is_integer($data['value'])) {
                        $this->data[$ENTITY . '.' . $ENTITY_DEEP . '.' . $data['title']] = $data['value'];
                    }
                }
            } else {
                $this->toData($data, $ENTITY . '.' . $ENTITY_DEEP, $VALUES);
            }
        }
    }

    /*
     * Show Collections In Mongo DB as Command 
     * 
     * 
     * 
     */

    public function getCollections() {
        if($this->mongoActive){
        $dbname = $this->DB;
        $collections = $dbname->getCollectionNames();
        return $collections;
        }else{
        return array();    
        }
    }

    public function setCollection($collection, $dataCollection, $secureIndex = "default") {

        $dbname = $this->DB;
        $nameCollection = $dbname->$collection;

        $indexes = $this->checkIndex($collection);

        if (count($indexes) == 0) {

            $log = $nameCollection->ensureIndex(array($secureIndex => 1), array('unique' => TRUE, 'dropDups' => TRUE));
//var_dump($log);
        }



        foreach ($dataCollection as $memberData) {
            try {
                $nameCollection->insert($memberData);
            } catch (MongoCursorException $e) {
//var_dump($e);
                echo $this->translate("no se puede guardar dos veces");
            }
        }
    }

    public function getCollection($collection) {
        if($this->mongoActive){ 
        $dbname = $this->DB;

        $result = $dbname->selectCollection($collection)->find();

        return $result;
        }else{
        return array();    
        }
    }
    
    
    public function encodeCollection($collectionObject){
        
        $structureArray = array();
        
        foreach($collectionObject as $object){
            
            $structureArray[]=$object;
            
        }
        
        return $structureArray;
        
    }
    
    
    public function toList($collectionArray,$KEY=''){
        
        $listArray = array();
        
        foreach($collectionArray as $key=>$array){
            
            if(is_string($array)){
               if($KEY!=''){ 
               $listArray[$KEY.'.'.$key]= $array;
               }else{
               $listArray[$key]= $array;    
               }
               
            }else{
               $aArray = $this->toList($array,$key);
               $listArray = array_merge($aArray,$listArray);
            }
        }
        
       return $listArray; 
    }

    public function deleteCollection($collection) {
        if($this->mongoActive){ 
        $dbname = $this->DB;
        $collection = $dbname->selectCollection($collection);
        $log = $collection->drop();
         return TRUE;
        }else{
         return FALSE;   
        }
    }

    /*
     * 
     * 
     * 
     * 
     */

    public function checkIndex($collection) {
        if($this->mongoActive){ 
        $indexSet = array();
        $dbname = $this->DB;
        $collection = $dbname->selectCollection($collection);

        $indexes = $collection->getIndexInfo();

        foreach ($indexes as $key => $index) {

            $nameIndex = $index['name'];

            $name_code = explode('_', $nameIndex);

            if ($name_code[0] == '') {
                $name = 'id';
                $indexSet[] = 'id';
            } else {
                $name = $name_code[0];
                $indexSet[] = $name;
            }
        }
        return $indexSet;
        }else{
        return array();   
        }
    }

    public function runSearch($collection, $searchWord, $limit = 1) {
        
        $result = array();
        $dbname = $this->DB;
        $searchResult = $collection = $dbname->command(array('text' => $collection, 'search' => $searchWord, 'limit' => $limit));

        foreach ($searchResult['results'] as $result) {

//var_dump($result['obj']);
        }
    }

    public function getFields($collection) {

        $indexes = array();
        $dbname = $this->DB;
        $collection = $dbname->selectCollection($collection);
        $cursor = $collection->find()->limit(1);
        foreach ($cursor as $key => $data) {
            foreach ($data as $index => $value) {
                $indexes[] = $index;
            }
        }
        return $indexes;
    }

    public function setUniqueIndex($collection, $indexes, $index) {

        if (!in_array($index, $indexes)) {
            $collection->ensureIndex(array($index => 1), array('unique' => 1));
        }
    }

    public function getQueryLike($collection, $index, $like) {
        $dbname = $this->DB;

        $collection = $dbname->selectCollection($collection);

        $regularExpression = array($index => new MongoRegex("/" . $like . "/i"));

        $cursor = $collection->find($regularExpression);

        return $cursor;
    }

    public function getQueryOr($collection, $indexValues) {

        $dbname = $this->DB;

        $collection = $dbname->selectCollection($collection);

        $format = '$or';

        $filter = array(
            '$or' => $indexValues
        );

        $result = $collection->find($filter);
        return $result;
    }

    /*
     * 
     * 
     * 
     * 
     */

    public function setQuery($collection, $data) {

        $dbname = $this->DB;


//$this->setUniqueIndex($collection,$indexs,$index);

        $collection = $dbname->selectCollection($collection);

        foreach ($data as $memberData) {
            try {
                $collection->insert($memberData);
            } catch (MongoCursorException $e) {
//echo $this->translate("no se puede guardar dos veces");
            }
        }
    }

    public function getQuery($collection, $data) {

        $dbname = $this->DB;
        $result = array();

        $collection = $dbname->selectCollection($collection);

//$result = $collection->find(array('rider'=>'buzz'));
//array('box'=>array('$in'=>array(6140)))    
        $result = $collection->find($data);
//$result = $collection->find(array('box'=>1000));

        return $result;
    }

    /*
     * 
     * 
     */

    public function deleteQuery($collection, $data) {

        $dbname = $this->DB;
        $collection = $dbname->selectCollection($collection);

        foreach ($data as $index => $value) {

            $find = $collection->find(array($index => array('$in' => array($value))));
            $counter = 0;
            foreach ($find as $found) {
                $counter++;
            }
            if ($counter != 0) {
                try {
                    $collection->remove(array($index => $value));
                } catch (MongoCursorException $e) {
                    echo $this->translate("No puedo Borrar porque no existe");
                }
            } else {
                return FALSE;
            }
        }
    }

}
