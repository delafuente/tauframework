<?php
/**
 * 
 * @abstract Handles all friendship issues
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 09-may-2011
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */

/* table tau_friendship:
id_rel
id_a - ui  ( always < id_b )
id_b - ui
relation  - enum (a_to_b,b_to_a,friends,exfriends_a,exfriends_b)
 * a_to_b : a requested friendship to b
 * b_to_a : inverse
 * friends : already friends
 * exfriends_a : were friends, but a broken the relationship
 * exfriends_b : inverse
*/
require_once("DataManager.php");

class TauFriendShip{

   protected $db; //Database Handler
   protected $logged_user_id;
   /**
    * Make a new TauFriendShip object. Make sure the user is logged in
    * before to use this class.
    * @param DataManager $db_handler The database handler
    */
    public function  __construct() {

        $this->db = DataManager::getInstance();
        
        $sessUser = TauSession::get('user');
        
        $this->logged_user_id = ($sessUser['id_user'])?$sessUser['id_user']:0;
        
    }

/**
 * Handles friendship requests. ( Friendship request, remove friendship, confirm request )
 * @param int $n The id either of requester or requested
 * @param int $z The id either of requester or requested
 * @param int $requester $n or $z, the requester id
 * @return string Result of the request:
 * [yet_solicited | already_friends | go_friends | friendship_requested | some_error ]
 */
public function testFriendshipRequest($n,$z,$requester){

    if($n < $z){ $a = $n; $b = $z;}else{$a = $z;$b = $n;}
    $can_make_action = ($a==$this->logged_user_id || $b == $this->logged_user_id);

    if(!$can_make_action){
        return "some_error";
    }

    try{
        
     $rel_search = ($requester==$a)?"a_to_b":"b_to_a";
     $query = "select id_rel,relation from tau_friendship where id_a=$a and id_b=$b limit 1;";
     $resultArray = $this->db->getRow($query);
     $relation = $resultArray['relation'];
     $id_rel = $resultArray['id_rel'];


   if($relation == "a_to_b" && $requester == $a){
       return "yet_solicited";
   }else if($relation == "b_to_a" && $requester == $b){
       return "yet_solicited";
   }else if( ($relation == "a_to_b" && $requester == $b) ||
          ($relation == "b_to_a" && $requester == $a) ){  // Yet confirmed by other counterpart
       return $this->registerFriendship($a, $b, $id_rel);
   }else if($relation == "friends"){
       return "already_friends";
   }else if($relation == "exfriends_a" || $relation == "exfriends_b"){
       //Some have broken the relationship
       if(ALLOW_RECONCILIATIONS){
           $can_delete = $this->deletePreviousRelation($a, $b);
            if($can_delete){
                return $this->registerFriendshipRequest($a, $b, $requester);
            }else{
                return "some_error";
            }
            
       }else{
            return "some_error";
       }
       
   }else{ // First solicitant
       return $this->registerFriendshipRequest($a, $b, $requester);
   }

   }catch(Exception $unknownException){
       if(DEBUG_MODE){
            error_log($unknownException->getTraceAsString());
       }

   }

}
/**
 * Used to clean previously broken relation
 * @param int $n one of the users id
 * @param int $z one of the users id
 * @return bool True if can delete, false otherwise 
 */
protected function deletePreviousRelation($n,$z){
    if($n < $z){ $a = $n; $b = $z;}else{$a = $z;$b = $n;}
     $can_make_action = ($a==$this->logged_user_id || $b == $this->logged_user_id);
     if($can_make_action){
         $query = "delete from tau_friendship where id_a=$a and id_b=$b";
         $res = $this->db->makeQuery($query);
         if($res){
             return true;
         }else{
             return false;
         }
     }else{
         return false;
     }
}

/**
 * Register a friendship between a and b. The id of a must be < id of b
 * @param int $a_id
 * @param int $b_id
 * @param int $rel_id
 * @return string [go_friends | some_error ]
 */

  public function registerFriendship($a_id,$b_id,$rel_id){
    //Control this user can modify this relation:
     $can_make_action = ($a_id==$this->logged_user_id || $b_id == $this->logged_user_id);
    $query = "update tau_friendship set id_a=$a_id, id_b=$b_id, relation='friends' where id_rel=$rel_id";
    if($can_make_action){
        $res = $this->db->makeQuery($query);
    }else{
        $res = false;
    }
    
    if($res){
        return "go_friends";
    }else{
        return "some_error";
    }


  }
  /**
   * Unregister a friendship between n and z.
   * @param int $n One of the users
   * @param int $z The other user
   * @param int $requester The id of that who want to end the relationship
   * @return bool If all was ok: true, false otherwise (i.e. if they were not friends, will return false ).
   */
  public function unregisterFriendship($n,$z,$requester){
    if($n < $z){ $a = $n; $b = $z;}else{$a = $z;$b = $n;}
    //Control this user can modify this relation:
     $can_make_action = ($a==$this->logged_user_id || $b == $this->logged_user_id);
       
    $id_of_rel = $this->areFriends($n, $z);
        
    if($id_of_rel && $can_make_action){
        
        $newRel = ($requester == $a)?"exfriends_a":"exfriends_b";       
        $query = "update tau_friendship set relation ='".$newRel."' where id_rel= $id_of_rel";
        $res = $this->db->makeQuery($query);
        
        if(!$res){
            return false;
        }
    }else{
        return false;
    }
    return $res;
  }


  /**
   * Register a request of friendship from a to b, or from b to a.
   * The id of a must be < id of b
   * @param int $a_id
   * @param int $b_id
   * @param int $requesterId
   * @return string [friendship_requested | some_error ]
   */

  public function registerFriendshipRequest($a_id,$b_id,$requesterId){
    $relationString = ($requesterId==$a_id)?"a_to_b":"b_to_a";
    //Control this user can modify this relation:
     $can_make_action = ($a_id==$this->logged_user_id || $b_id == $this->logged_user_id);

    $query = "insert into tau_friendship (id_a,id_b,relation,dt_created) values($a_id,$b_id,'" .
    $relationString . "','" . date("Y-m-d H:i:s",time()) . "');";
    if($can_make_action){
        $res = $this->db->makeQuery($query);
    }else{
        $res = false;
    }
 
    if($res){
        return "friendship_requested";
    }else{
        return "some_error";
    }

  }
  /**
   * Test if two users are friends
   * @param int $n
   * @param int $z
   * @return int The id of the relation if they are friends, false otherwise.
   */
  public function areFriends($n,$z){
    if($n < $z){ $a = $n; $b = $z;}else{$a = $z;$b = $n;}
    $query = "select id_rel from tau_friendship where id_a=$a and id_b=$b and " .
    " relation='friends' limit 1";
    $id_of_relation = $this->db->getRow($query);
    
    if($id_of_relation){
        return $id_of_relation['id_rel'];
    }else{
        return false;
    }

  }
  /**
   * Get all the friends of a user
   * @param int $a The id of the user to get the friends of
   * @param int $limit Limit the max results to this integer
   * @return array Associative array with key => username value => id of all the friends of $a. ( False if no friends )
   */
  public function getFriends($user_id, $limit = false){
     $baseList ="";
      $sqlLimit ='';
     if($limit !== false){
         $sqlLimit = " limit $limit";
     }
     $query = "select id_a,id_b from tau_friendship where (id_a=$user_id or id_b=$user_id) and relation='friends' $sqlLimit";
     $endList = array();

     $res = $this->db->getResults($query);
     if(!$res){
         return false;
     }
     foreach($res as $row){
         if($row['id_a']==$user_id){
             $baseList .= $row['id_b'] . ",";
         }else{
             $baseList .= $row['id_a'] . ",";
         }
     }
     $baseList = trim($baseList,",");

     $query = "select * from tau_user where bo_active=1 and " .
     " ui_id_user in(" . $baseList . ");";

     $result = $this->db->getResults($query);
     
     $userDataQuery = "select * from tau_user_data where id_user in(" . $baseList . ");";
     
     $resultData = $this->db->getResults($userDataQuery);
     
     if($result && $resultData){
         
        foreach($resultData as $data){
            $endList['data'][$data['id_user']] = $data;
        }
         
        foreach($result as $row){
         $endList['user'][$row['ui_id_user']] = $row;
        }
     }else{
         return false;
     }

     return $endList;
  }

   /**
   * Get all the friendship requests of a user
   * @param int $a The id of the user to get the friends of
   * @param int $limit Limit the max results to this integer
   * @return array Associative array with key => username value => id of all the fr requests of $a. ( False if no friends )
   */
  public function getFriendshipRequests($user_id, $limit = false){
     $baseList ='';
     $sqlLimit ='';
     if($limit !== false){
         $sqlLimit = " limit $limit";
     }
     $query = "select id_a,id_b from tau_friendship where (id_a=$user_id and relation='b_to_a') or (id_b=$user_id and relation='a_to_b') $sqlLimit";
     $endList = array();

     $res = $this->db->getResults($query);
     if(!$res){
         return false;
     }
     foreach($res as $row){
         if($row['id_a']==$user_id){
             $baseList .= $row['id_b'] . ',';
         }else{
             $baseList .= $row['id_a'] . ',';
         }
     }
     $baseList = trim($baseList,',');
     
     $query = "select * from tau_user where " .
     " ui_id_user in(" . $baseList . ");";
     if(DEBUG_MODE){
         error_log("<TauFriendShip.php>.getFriendShipRequest($user_id) query: " . $query );
     }
     $result = $this->db->getResults($query);
     if($result){
        foreach($result as $row){
         $endList['user'][$row['ui_id_user']] = $row;
        }
     }else{
         return false;
     }

     $userDataQuery = "select * from tau_user_data where id_user in(" . $baseList . ");";
     
     $resultData = $this->db->getResults($userDataQuery);
     
     if($result && $resultData){
         
        foreach($resultData as $data){
            $endList['data'][$data['id_user']] = $data;
        }

     }else{
         return false;
     }

     return $endList;

  }

  protected function dlog($message,$method){
      if(DEBUG_MODE){
         error_log("<TauFriendShip::$method()>" . $message );
     }
  }

}