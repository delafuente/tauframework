<?php
/**
 * 
 * @abstract Semi-singleton wrapper class to control DB access and actions
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 05-11-2011
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
require_once( APPLICATION_PATH. "/tau/inc/db/shared/ez_sql_core.php");
require_once( APPLICATION_PATH. "/tau/inc/db/mysql/ez_sql_mysql.php");
require_once( APPLICATION_PATH. "/tau/Tau.php");

class DataManager {

    protected $db;
    protected $db_name;
    protected $db_host;
    protected $db_pass;
    protected $db_user;
    protected $lastErrorMessage;
    protected $lastErrorTrace;
    protected $queryLog;
    private static $uniqueInstance = null;
    
    protected function __construct($db_name = false, $db_host = false, $db_pass = false, $db_user = false){
        
        $this->db_name = $db_name;
        $this->db_host = $db_host;
        $this->db_pass = $db_pass;
        $this->db_user = $db_user;
        
        $this->queryLog = array();
        
        if(!$db_name){ $this->db_name = DB_NAME; }
        if(!$db_host){ $this->db_host = DB_HOST; }
        if(!$db_pass){ $this->db_pass = DB_ADMIN_PASSWD; }
        if(!$db_user){ $this->db_user = DB_ADMIN; }
                
        $this->db = new ezSQL_mysql($this->db_user, $this->db_pass, $this->db_name, $this->db_host, "UTF-8");
        $this->lastErrorMessage = "NO ERROR";
        $this->lastErrorTrace = "NO TRACE";
        $this->makeQuery("SET NAMES utf8");
    }

    private final function __clone() {}
    private final function __wakeup() {}
    
    public function getDataBaseName(){
        return $this->db_name;
    }
    public function getDataBaseHost(){
        return $this->db_host;
    }
    public function getDataBaseUser(){
        return $this->db_user;
    }
    /**
     * Get the main singleton instance, or different instance if you specify $db_name 
     * ( main instance continue working with initial config if you call getInstance() without params )
     * @param string $db_name The name of the database
     * @param string $db_host The host
     * @param string $db_pass The password
     * @param string $db_user The user name
     * @return DataManager Singleton instance, or new instance if you specify $db_name
     */
    public static function getInstance( $db_name = false, $db_host = false, $db_pass = false, $db_user = false ){
        
        if(self::$uniqueInstance === null || $db_name !== false){
            if($db_name !== false){
                $instance = new DataManager($db_name, $db_host, $db_pass, $db_user);
                Tau::addDbInstance($instance);
                return $instance;
            }
            self::$uniqueInstance = new DataManager();
            Tau::addDbInstance(self::$uniqueInstance);
        }
        
        return self::$uniqueInstance;
    }
    
    public static function reset() {
        self::$uniqueInstance = null;
    }
    
    public function getRow($query,$returnAs=ARRAY_A){
        
        if(DEBUG_MODE){ $r=mt_rand(0,1000); $this->queryLog[date("Y-m-d H:i:s",time())."_$r".uniqid()] = $query; }
        $res = $this->db->get_row($query,$returnAs);
        return $this->getQueryResult($res);
    }

    public function getResults($query,$returnAs=ARRAY_A){
        if(DEBUG_MODE){ $r=mt_rand(0,1000); $this->queryLog[date("Y-m-d H:i:s",time())."_$r".uniqid()] = $query; }
        $res = $this->db->get_results($query,$returnAs);
        return $this->getQueryResult($res);
    }
    
    public function getVar($query,$returnAs=ARRAY_A){
        if(DEBUG_MODE){ $r=mt_rand(0,1000); $this->queryLog[date("Y-m-d H:i:s",time())."_$r".uniqid()] = $query; }
        $res = $this->db->get_var($query);
        return $this->getQueryResult($res);
    }

    public function makeQuery($query){
        if(DEBUG_MODE){ $r=mt_rand(0,1000); $this->queryLog[date("Y-m-d H:i:s",time())."_$r".uniqid()] = $query; }
        $res = $this->db->query($query);
        return $this->getQueryResult($res, $query);
    }
    /**
     * Get array of type arr[key_field] = value_field
     * @param string $query The query, must require at least key_field and value_field fields
     * @param string $key_field The field that is going to be used as key of the array
     * @param string $value_field The field that is going to be used as value of the array
     * @return mixed Assoc array with results, of false if no results
     */
    public function getList($query,$key_field,$value_field){
        if(DEBUG_MODE){ $r=mt_rand(0,1000); $this->queryLog[date("Y-m-d H:i:s",time())."_$r".uniqid()] = $query; }
        $res = $this->db->get_results($query,ARRAY_A);
        $list = array();
        $elems = count($res);

        if($res !== false){
            
            for($i=0;$i < $elems; $i++){
                $list[$res[$i][$key_field]] = $res[$i][$value_field];
            }
            return $list;
        }else{
            return false;
        }
    }
  /**
     * Get array of type arr[key_field] = value_field, and arr[key_field + __full] = row
     * @param string $query The query, must require at least key_field and value_field fields
     * @param string $key_field The field that is going to be used as key of the array
     * @param string $value_field The field that is going to be used as value of the array
     * @return mixed Assoc array with results, of false if no results
     */
    public function getListAndFull($query,$key_field,$value_field){
        if(DEBUG_MODE){ $r=mt_rand(0,1000); $this->queryLog[date("Y-m-d H:i:s",time())."_$r".uniqid()] = $query; }
        $res = $this->db->get_results($query,ARRAY_A);
        $list = array();
        $elems = count($res);

        if($res !== false){
            
            for($i=0;$i < $elems; $i++){
                $list[$res[$i][$key_field]] = $res[$i][$value_field];
                $list[$res[$i][$key_field] . "__full"] = $res[$i];
            }
            return $list;
        }else{
            return false;
        }
    }
    /**
     * Starts a transaction
     */
    public function beginTransaction(){
        if(DEBUG_MODE){ $r=mt_rand(0,1000); $this->queryLog[date("Y-m-d H:i:s",time())."_$r".uniqid()] = "START TRANSACTION;"; }
        $this->db->query("START TRANSACTION;");
    }
    /**
     * Commits a previously started transaction.
     * Use it if all of previous queries were correct, or
     * use rollback() otherwise
     */
    public function commit(){
        if(DEBUG_MODE){ $r=mt_rand(0,1000); $this->queryLog[date("Y-m-d H:i:s",time())."_$r".uniqid()] = "COMMIT;"; }
        $this->db->query("COMMIT");
    }
    /**
     * Use it when in transaction, and something went wrong.
     * Will roll back any changes to the db in current transaction.
     */
    public function rollback(){
        if(DEBUG_MODE){ $r=mt_rand(0,1000); $this->queryLog[date("Y-m-d H:i:s",time())."_$r".uniqid()] = "ROLLBACK;"; }
        $this->db->query("ROLLBACK");
    }
    /**
     * Process a queries array within a transaction
     * @param array $queryArray The list of queries
     * @return boolean True if correct, false otherwise.
     */
    public function makeTransaction($queryArray){
        $this->beginTransaction();
        try {
            foreach ($queryArray as $query) {
                $result = $this->doTransactionQuery($query);
                if ($result === false) {
                    $this->rollback();
                    throw new Exception($this->db->last_error);
                }
                
            }
        }catch(Exception $ex){
            $this->lastErrorMessage = $ex->getMessage();
            $this->lastErrorTrace = $ex->getTraceAsString();
            return false;
        }
        $this->commit();
        return true;
    }
    /**
     * Same as query(), but throws an Exception when fails
     * @param string $query The query to execute
     */
    protected function doTransactionQuery($query){
        if(DEBUG_MODE){ $r=mt_rand(0,1000); $this->queryLog[date("Y-m-d H:i:s",time())."_$r".uniqid()] = $query; }
        $res = $this->db->query($query);

        return $res;
    }
    /**
     *  Get a list of all queries executed, if DEBUG_MODE = true
     * @return array List of all queries executed, if DEBUG_MODE = true
     */
    public function getExecutedQueries(){
        return $this->queryLog;
    }
    public function escape($vars){
        if(is_array($vars)){
            $new_array = array();

            foreach($vars as $key => $val){
                $new_array[$key] = $this->db->escape($val);
            }
            return $new_array;
        }else{
            return $this->db->escape($vars);
        }
    }
    public function getDebug(){
        return $this->db->debug();
    }
    
    public function getLastErrorMessage(){
        return $this->lastErrorMessage;
    }
    public function getLastErrorTrace(){
        return $this->lastErrorTrace;
    }
    public function close(){
        //echo "<p>DataManager close() for database " . $this->getDataBaseName() . "</p>";
        $this->db->disconnect();
    }
    public function getAffectedRows(){
        return $this->db->rows_affected;
    }
    /**
     * Internal function to grab the error if any, and return the result
     * of a query function.
     * @param resource $res The result of a query
     * @return mixed false if fails, result of query otherwise
     */
    protected function getQueryResult($res, $query = ""){
        if($res === false){
            if($this->db->rows_affected === -1){
                $this->lastErrorMessage = "Sentence don't modified rows";
                return false;
            }else{
                return true;
            }
            return false;
        }else{
            return $res;
        }
    }
}