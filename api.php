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
            if(isset($_GET['key']))
            {
                $apikey = $_GET['key'];
            }
            else
            {
                echo "You need an api key.\r\n";
                break;
            }
            $loc = Location::getLocationIndexByApikey($apikey);

            if($loc)
            {
                $machine = $_GET['macn'];
                $date = $_GET['date'];
                $value = $_GET['val'];
                $chks = $_GET['chk'];
                //Check the checksum
                $validate = "/api.php?action=PUSH&val=" . $value . "&date=" . $date . "&key=" . $apikey . "&macn=" . $machine . "da39a3ee5e6b4b0d3255bfef95601890afd80709";//Magic number.
                if(sha1($validate) === $chks)
                {
                    $data = Data::create($loc->lindex,$machine,$date, $value);
                    echo "ok\r\n";
                }
                else
                {
                    echo "bad\r\n";
                }
            }
            else
            {
                echo "No location\r\n";
            }
            break;
        case "PING":
            $sql = "SELECT COUNT(*) as total FROM Data WHERE lindex=" . $_GET['loc'] . ";";
            if(!$mysqli->query($sql))
            {
                echo $mysqli->error;
            }
            else
            {
                if(!$mysqli->query($sql))
                {
                    echo $mysqli->error;
                }
                $mysqli->close();
            }
            $mysqli->close();
            break;
        case "PONG":
            header('Content-type: text/javascript');
            echo json_encode(Audit::getAllAuditsOfLocation($_GET['val']));
            break;
        default:
            break;
    }
    $mysqli->close();

    // Open the file to get existing content

    // Append a new person to the file
    // Write the contents back to the file

?>