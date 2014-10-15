<?php

/**
 * 
 * @abstract Handles Tau Framework installation
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 14-jul-2014
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */



class TauInstall {

    protected $db;
    protected $tables; //array with all tau tables
    protected $tablesSQL; //array with all table creation sql
    
    public function __construct(DataManager $db = null) {
        
        if($db instanceof DataManager){
            $this->db = $db;
        }else{
            $this->db = DataManager::getInstance();    
        }
        
        $this->tables = array();
        $this->tablesSQL = array();
        //Init
       
    }
    
    
    public function getExistingTables(){
        $existingTables = array();

        foreach($this->tables as $table){
            if($this->db->getVar("SHOW TABLES LIKE '".$table."'") == $table){
                $existingTables[] = $table;
            }
        }
        return $existingTables;
    }
    
    public function endPage(){
        echo "<hr/><p>Powered by " . Tau::getTauFrameworkGreek() . ".</p></div>";
        $this->db->close();
        die();
    }
    
    public function getAllTauTables(){
        $schema = file("schema.sql");
        $matches = array();
        $currentTable = "";
        foreach ($schema as $line){
            
            if(strpos($line,"CREATE TABLE") !== false){
                 preg_match('/\`([^\`]*?)\`/', $line, $matches);
                 $this->tables[] = $matches[1];
                 $currentTable = $matches[1];
            }elseif(strpos($line,"ALTER TABLE") !== false){
                 preg_match('/\`([^\`]*?)\`/', $line, $matches);
                 $this->tables[] = "ALTER " . $matches[1];
                 $currentTable = "ALTER " . $matches[1];
            }elseif(strpos($line,"-- --------------------------------------------------------") !== false){
                 $currentTable = "";
            }
            if($currentTable != ""){
            if(!isset($this->tablesSQL[$currentTable])){
                    $this->tablesSQL[$currentTable] ="";
                }
                $this->tablesSQL[$currentTable] .= $line;
            }
            
            
        }
        return $this->tablesSQL;
    }

}

?>
