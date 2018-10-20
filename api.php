<?php
    
    $value = "";
    $action = "";
	
    if(isset($_GET['action'])){
        $action = $_GET['action'];
    }
	else{
		echo "You require an action";
		exit();
	}
	if(isset($_GET['value'])){
		 $value= $_GET['value'];
	}
   if(isset($_GET['apik'])){
	   //TODO: Preform sanity check
   }
   else{
		echo "You require an apik";
		exit();
   }
	
	include "mysql_connect.php"; //Grants access to $mysqli-> variable.
	
   function sha256($data,$length = 32) {
        $random = sha256($data);
        return substr($random, 0, $length);
    }

	function doEcho(){
	}
	function doPull(){
		
	}
	function doPush(){
	}
   
   switch($action){
        case "ECHO":
			doEcho();
            break;
        case "PUSH": //Push data to the server.
            doPush();
            break;
        case "PULL": //Check the commands list for apik privided, Check the user.level >= action.level ifso echo the “ACTION” ifnot echo “ERROR” and remove entry.
            doPull();
            break;
        default:
			echo "Unknown action $action. "
			exit();
            break;
    }
?>