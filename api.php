<?php
    include "mysql_connect.php";
    $value = "";
    $action = "";
	$apik = "";
	
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
	
   function rand_sha1($length) {
        $max = ceil($length / 40);
        $random = '';
        for ($i = 0; $i < $max; $i ++) {
            $random .= sha1(microtime(true).mt_rand(10000,90000));
        }
        return substr($random, 0, $length);
    }

    switch($action)
    {
        case "ECHO":
			doEcho();
            break;
        case "PUSH": //Push data to the server.
            doPush();
            break;
        case "PING":
            doPing();
            break;
        default:
			echo "Unknown action $action. "
			exit();
            break;
    }
?>