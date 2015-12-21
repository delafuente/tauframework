
<?php

/**
 * Lightweight Html table generator helper.
 * Limitations: (1) can't set individual td elements id or class attributes.
 * Simple usage example:
 *
 * $tbl = new TauTable('myTable','tblClass',3); //id,table class,columns
 * $tbl->setTblockClasses("theaderClass bolderType","tbodyClass","tfootClass");
 * $tbl->setOddClass("odd"); //If not specified, not odd/even rows
 * $tbl->setCaption("Table Example");
 * $tbl->setSummary("This table is an example"); //for blind readers
 * $tbl->addRow($tableHeaderCaptionsArray,false,"thead");
 *
 * //each $dataValue is a row (array)
 * foreach($data as $dataValue){
 *  $tbl->addRow($dataValue); //default in tbody, 3rd parameter 'thead','tbody',
 *              //or 'tfoot' values allowed.
 * }
 * $html = $tbl->toString(); //get the table htmled text.
 *
 * @abstract Lightweight Html table generator helper
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 01-oct-2010
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */

class TauTable {

   private $tableid; //html id of table, can be empty
   private $tableclass; //css class or classes, separated by spaces
   private $tablecaption; //table caption text
   private $tableHtml; //The full table Html + contents
   private $numColumns; //The number of columns
   private $numRows; //The number of rows
   private $summary; //Table attribute 'summary'
   private $oddClass; //if its specified,then odd rows has this class by default
   private $theadClass; //css class of thead
   private $tbodyClass; //css class of tbody
   private $tfootClass; //css class of tfoot
   private $theadArray; //array with thead rows
   private $tbodyArray; //array with tbody rows
   private $tfootArray; //array with tfoot rows
   private $theadRowsClass; //array with thead rows classes
   private $tbodyRowsClass; //array with tbody rows classes
   private $tfootRowsClass; //array with tfoot rows classes
  // private $tbodyRowsCounter; //counter for odd rows in tbody
   private $errorMessages; //Store all errors.
   private $fullColspan; //Multi-dimension array, stores which named rows are
   //full colspan. $fullColspan['tfoot']['row_id_5']=true;
   //looknew
   /**
    * Creates an instance of TauTable, with optional table id and class/classes
    * @param string $table_id html id of table, can be empty
    * @param string $table_class css class or classes separated by space
    * @param int $columns number of columns
    * @param int $rows number of rows
    */
   function  __construct($table_id=false,$table_class=false,$columns=false,
           $rows=false) {
        
       if($table_class){ $this->tableclass = $table_class; }
       if($table_id){ $this->tableid=$table_id; }
       if($columns && $rows){ $this->setDimensions($columns, $rows); }
       if($columns && !$rows){ $this->numColumns = $columns; }

       $this->tablecaption=false;
       $this->summary=false;
       $this->oddClass=false;
       $this->theadClass=false;
       $this->tfootClass=false;
       $this->tbodyClass=false;
       $this->theadArray = array();
       $this->tbodyArray = array();
       $this->tfootArray = array();
       $this->theadRowsClass = array();
       $this->tbodyRowsClass = array();
       $this->tfootRowsClass = array();
       //$this->tbodyRowsCounter=-1;
       $this->errorMessages=false;
       $this->fullColspan=array(); //looknew
       $this->fullColspan['thead']=array();
       $this->fullColspan['tbody']=array();
       $this->fullColspan['tfoot']=array();

    }
    //looknew
    /**
     * Add array of rows, being each row an array of data.
     * See addRow().
     *
     * @param array $structArray Array of rows ( each one is an array with data for cells )
     * @param array $rowIdsArray Array of html id values for each row.
     * @param string $where 'tbody','thead' or 'tfoot'.
     * @param string $rowClass  Css class value(s) for the row, space splitted.
     */
    public function addRows($structArray,$rowIdsArray=false,
            $where='tbody',$rowClass=false){
        
        $counter=-1;
        $insResult=true;
        $endOddClass=$rowClass;
        if($this->oddClass){
            $endOddClass = $this->oddClass  . " " . $rowClass ;
        }
        foreach($structArray as $value){

            $counter ++;
            $currentRowId=false;
            if($counter % 2==0){
                $endRowClass=$endOddClass;
            }else{
                $endRowClass = $rowClass;
            }
                if($rowIdsArray){
                    $currentRowId=$rowIdsArray[$counter];
                }
             if(! $this->addRow($value,$currentRowId , $where, $endRowClass)){
             $this->addErrorMessage("" .
                     "addRow return false in addRows in iteration $counter");

             }
           
         }
        }
        

    /**
     * Add a row to the table, in thead,tbody or tfoot parts. Returns false
     * if not specified dataArray or when dataArray is not array.
     * First call will compare numColumns with count(dataArray), if numColumns
     * is not defined yet, then will store count(dataArray) value, and if it's
     * different, will return false. If return false, use getErrorMessages to
     * see what happened. If countRows=false, this comparison is avoided //looknew
     *
     * @param array $dataArray Array with the data for cells.
     * @param mixed $rowId String or int html id attr for row
     * @param string $where thead,tbody,tfoot are the accepted values
     * @param string $rowClass css class or classes for row, space separated.
     * @param boolean $countRows If true, count($dataArray) will be tested. //looknew
     * @return boolean True on success, false otherwise.
     */
    public function addRow($dataArray,$rowId=false,$where="tbody",
            $rowClass=false,$countRows=true){ //looknew
            $actualNumColumns=-1;
        if(!$dataArray || !is_array($dataArray)){
            $this->addErrorMessage("not data received, or data is not " .
                        "an array in addRow()");
            return false;
        }
        if($countRows){  //looknew
            $countData = count($dataArray);
            $actualNumColumns = $this->numColumns;
            if($actualNumColumns){
                if($actualNumColumns != $countData){
                    $this->addErrorMessage("numColumns specified differ from " .
                            "length of array parsed in addRow()") ;
                    return false;
                }
            }else{
                $this->numColumns=$countData;
            }
        }

        switch($where){
            case "tbody": 
                if($rowId){
                    $this->tbodyArray[$rowId]=$dataArray;
                    $this->tbodyRowsClass[$rowId]=$rowClass;
                }else{
                    $this->tbodyArray[]=$dataArray;
                    $this->tbodyRowsClass[]=$rowClass;
                }
                break;
            case "thead":
                if($rowId){
                    $this->theadArray[$rowId]=$dataArray;
                    $this->theadRowsClass[$rowId]=$rowClass;
                }else{
                    $this->theadArray[]=$dataArray;
                    $this->theadRowsClass[]=$rowClass;
                }
                break;
            case "tfoot": 
                if($rowId){
                    $this->tfootArray[$rowId]=$dataArray;
                    $this->tfootRowsClass[$rowId]=$rowClass;
                }else{
                    $this->tfootArray[]=$dataArray;
                    $this->tfootRowsClass[]=$rowClass;
                }
                break;
            default:
                $this->addErrorMessage("unknown type of 'where' in addRow()");
                return false;
        }

        //looknew
        return true;

    }
    /**
     * Not yet fulfilled, do not use
     * //TODO: write this function and make the functionallity ?
     *
     * @param mixed $rowId html attr id of row, or numeric index (begin on 0),
     * counting from each block, thead,tbody or tfoot.
     * @param int $cellNumber column index of cell, begin on zero.
     * @param int $numColumns number of colspan columns
     * @param string $where 'thead','tbody' or 'tfoot'. Default 'tbody'
     */
   public function setCellColspan($rowId,$cellNumber,$numColumns,
           $where="tbody"){


   }
   //looknew
   /**
    * Add a row with only one cell which spans all columns. If it's the first
    * row inserted and not yet specified the number of columns, colspan will be
    * set to '0'. Some browsers treat this different, and only strict html
    * doctypes support this feature.
    *
    * @param string $innerHtml
    * @param string $rowId
    * @param string $where
    * @param string $rowClass
    * @return type
    */
   public function addFullSpanRow($innerHtml,$rowId,$where="tfoot",
            $rowClass=false){

            $actualNumColumns = $this->numColumns;

            if(!$actualNumColumns){
                $actualNumColumns ="all";
            }
            if($where != "thead" && $where && "tbody" && $where != "tfoot"){
                $this->addErrorMessage("unknown type of 'where' in addFullSpanRow()");
                return false;
            }
            $this->fullColspan[$where][$rowId]=$actualNumColumns;
            $this->addRow($innerHtml,$rowId,$where,$rowClass,false);
   }
   //Set number of rows and columns for the table
   //can be used with only columns: setDimensions(4,false);
   //or only rows if columns yet specified.
   public function setDimensions($columns,$rows){
       $this->numColumns = $columns;
       $this->numRows = $rows;
   }
   public function setTableId($id){
       $this->tableid = $id;
   }
   public function setTableClass($class){
       $this->tableclass = $class;
   }
   public function setCaption($text){
       $this->tablecaption = $text;
   }
   public function setSummary($text){
       $this->summary = $text;
   }
   /**
    * If specified, all odd rows will have this class in addition of any other
    * specified.
    * @param string $oddClass class for all odd rows
    */
   public function setOddClass($oddClass){
       $this->oddClass=$oddClass;
       $this->addErrorMessage("oddClass=" . $this->oddClass);
   }
   //thead class or classes separated by spaces
   public function setTheadClass($class){
       $this->theadClass=$class;
   }
   //tfoot class or classes separated by spaces
   public function setTfootClass($class){
       $this->tfootClass=$class;
   }
   //tbody class or classes separated by spaces
   public function setTbodyClass($class){
       $this->tbodyClass=$class;
   }
   //Set thead,tbody and tfoot classes in one function.
   //Could be class or classes separated by spaces, for each type.
   public function setTblockClasses($thead=false,$tbody=false,$tfoot=false){
       $this->theadClass=$thead;
       $this->tbodyClass=$tbody;
       $this->tfootClass=$tfoot;
   }
   /**
    * Makes a full table with a mysql result.
    * 
    * @param mysql_res $result A mysql_query result.
    * @param array $headersArray Array with column titles, if false will take real field names.
    * @param string $caption  The table caption
    * @param array $tfootText Text for footer, colspan all columns. Use an array. (array[0])
    * @param string $thRowId html id of header row
    * @param string $thRowClass css class of header row
    * @param string $tbodyRowsClass css class of all tbody rows
    * @param array  $tbodyRowsIdsArray Array of all tbody rows html id's
    */ 
   public function makeTableFromResult(&$result,$headersArray=false,
    $caption=false,$tfootText=false,$thRowId=false,
    $thRowClass=false,$tbodyRowsClass=false,$tbodyRowsIdsArray=false){
    
    $tcounter = -1;
    $tbody_tr_id=false;
    
    if($caption){
        $this->setCaption($caption);
    }
    
    if($headersArray){
        $this->addRow($headersArray,$thRowId,'thead',$thRowClass);
    }
    
    while($row = mysql_fetch_assoc($result)){
        $tcounter++;
        if(!$headersArray){
            $headersArray = array();
            foreach($row as $key => $value){
                $headersArray[]=$key;    
            }
            $this->addRow($headersArray,$thRowId,'thead',$thRowClass);
        }
        
        if($tbodyRowsIdsArray){
            $tbody_tr_id = $tbodyRowsIdsArray[$tcounter];
        }
        $this->addRow($row,$tbody_tr_id,'tbody',$tbodyRowsClass);
        
        
    } 
    
    if($tfootText){
        $rndValue = rand(0,1000);
        $this->addFullSpanRow($tfootText,'tfoot_table_' . $rndValue .'_id');
    }
   }
   /**
    *
    * @return string html paragraphs of any errors ocurred, separated by \n.
    */
   public function getErrorMessages(){
       return $this->errorMessages;
   }
   /**
    * Returns html string of the table with the data, or false on error.
    */
   public function toString(){

       $this->tableHtml = "\n\n<table";
       if($this->tableid){
           $this->tableHtml .= $this->makeAttr("id", $this->tableid);
       }
       if($this->tableclass){
           $this->tableHtml .= $this->makeAttr("class", $this->tableclass);
       }
       if($this->summary){
           $this->tableHtml .= $this->makeAttr("summary", $this->summary);
       }

       $this->tableHtml .= ">\n";

       if($this->tablecaption){
         $this->tableHtml .= "\n\t<caption>" . $this->tablecaption . "</caption>\n";
       }
       // -- Writting thead --
       $this->tableHtml .= "\n<thead";

       if($this->theadClass){
           $this->tableHtml .= $this->makeAttr("class", $this->theadClass);
       }
       $this->tableHtml .= ">";
        //process all thead rows
       $isAssocThead = $this->is_assoc($this->theadArray);

       $headRows =-1;

       foreach($this->theadArray as $key => $value){
            $headRows++;
            $colspanAttr=""; //looknew

            if(isset($this->fullColspan['thead'][$key])){
                if($this->fullColspan['thead'][$key]=="all"){
                   $this->fullColspan['thead'][$key]="0";
                }
                $colspanAttr = " colspan='".
                $this->fullColspan['thead'][$key] . "' ";
            }


           $this->tableHtml .= "\n<tr";
           if($isAssocThead){
               if($key){
                   $this->tableHtml .= $this->makeAttr("id", $key);
               }
               if($this->theadRowsClass[$key]){
                   $this->tableHtml .= $this->makeAttr("class",$this->theadRowsClass[$key]);
               }
           }else{
               if($this->theadRowsClass[$headRows]){
                   $this->tableHtml .= $this->makeAttr("class",$this->theadRowsClass[$headRows]);
               }
           }
           $this->tableHtml .= ">\n";

           foreach($value as $tdata){
               //looknew -> and th x td
               $this->tableHtml .= "\t<th$colspanAttr>" . $tdata . "</th>\n";
           }
           $this->tableHtml .= " </tr>\n";

       }

       $this->tableHtml .= "</thead>\n";

       // -- Writting tfoot --
       $this->tableHtml .= "\n<tfoot";

       if($this->tfootClass){
           $this->tableHtml .= $this->makeAttr("class", $this->tfootClass);
       }
       $this->tableHtml .= ">";
        //process all tfoot rows
       $isAssocTfoot = $this->is_assoc($this->tfootArray);

       $footRows =-1;

       foreach($this->tfootArray as $key => $value){
            $footRows++;
               $colspanAttr=""; //looknew

            if($this->fullColspan['tfoot'][$key]){
                if($this->fullColspan['tfoot'][$key]=="all"){
                   $this->fullColspan['tfoot'][$key]="0";
                }
                $colspanAttr = " colspan='".
                $this->fullColspan['tfoot'][$key] . "' ";
            }
           $this->tableHtml .= "\n<tr";
           if($isAssocTfoot){
               if($key){
                   $this->tableHtml .= $this->makeAttr("id", $key);
               }
               if($this->tfootRowsClass[$key]){
                   $this->tableHtml .= $this->makeAttr("class",$this->tfootRowsClass[$key]);
               }
           }else{
               if($this->tfootRowsClass[$footRows]){
                   $this->tableHtml .= $this->makeAttr("class",$this->tfootRowsClass[$footRows]);
               }
           }
           $this->tableHtml .= ">\n";

           foreach($value as $tdata){
               $this->tableHtml .= "\t<td$colspanAttr>" . $tdata . "</td>\n"; //looknew
           }
           $this->tableHtml .= " </tr>\n";

       }

       $this->tableHtml .= "</tfoot>\n";

       //process all tbody rows
       $isAssoctbody = $this->is_assoc($this->tbodyArray);
        $this->tableHtml .= "\n<tbody";

       if($this->tbodyClass){
           $this->tableHtml .= $this->makeAttr("class", $this->tbodyClass);
       }
       $this->tableHtml .= ">";
       $bodyRows =-1;

       foreach($this->tbodyArray as $key => $value){
            $bodyRows++;
            $colspanAttr=""; //looknew

            if(isset($this->fullColspan['tbody'][$key])){
                if($this->fullColspan['tbody'][$key]=="all"){
                   $this->fullColspan['tbody'][$key]="0";
                }
                $colspanAttr = " colspan='".
                $this->fullColspan['tbody'][$key] . "' ";
            }
           $this->tableHtml .= "\n<tr";
           if($isAssoctbody){
               if($key){
                   $this->tableHtml .= $this->makeAttr("id", $key);
               }
               if($this->tbodyRowsClass[$key]){
                   $this->tableHtml .= $this->makeAttr("class",$this->tbodyRowsClass[$key]);
               }
           }else{
               if($this->tbodyRowsClass[$bodyRows]){ //looknew error in previous version
                   $this->tableHtml .= $this->makeAttr("class",$this->tbodyRowsClass[$bodyRows]);
               }
           }
           $this->tableHtml .= ">\n";

           foreach($value as $tdata){
               $this->tableHtml .= "\t<td$colspanAttr>" . $tdata . "</td>\n"; //looknew
           }
           $this->tableHtml .= " </tr>\n";

       }

       $this->tableHtml .= "</tbody>\n";

       $this->tableHtml .= "</table>\n";

       return $this->tableHtml;





   }
   /**
    * Returns an attribute='value' text, i.e.: makeAttr("id","myTable")
    * will output this text: id='myTable', surrounded with spaces.
    * @param string $attrName name of the attribute
    * @param string $attrValue value of the attribute
    */
   private function makeAttr($attrName,$attrValue){
       return " $attrName=" . "'$attrValue' "; 
   }

   /**
    * Add error string to errorMessages member, between html paragraphs, and
    * splitted with \n.
    * @param string $errorDescription Error description
    */
   private function addErrorMessage($errorDescription){
       $this->errorMessages .= "<p>" . $errorDescription . "</p>\n";
   }
   //determines wether an array is an associative array
   //function from Anonymous in http://www.php.net/manual/es/function.is-array.php#96724
   private function is_assoc($array) {
    return (is_array($array) && (0 !== count(array_diff_key($array,
            array_keys(array_keys($array)))) || count($array)==0));
   }



}