<?php

/**
 * ReactDB short summary.
 *
 * ReactDB database namespace for all actions on database.
 *
 * @version 1.0
 * @author death
 */
namespace ReactDB
{
    //contents of init.php
    if (!defined('MY_USER')) {
        define('MY_USER', true);

        function rand_sha1($length) {
            $max = ceil($length / 40);
            $random = '';
            for ($i = 0; $i < $max; $i ++) {
                $random .= sha1(microtime(true).mt_rand(10000,90000));
            }
            return substr($random, 0, $length);
        }

      class User
      {
          public $uindex = -1; //uindex	int
          public $uname;  //uname	varchar(32)
          public $phash;  //phash	varchar(64)
          public $email;  //email	varchar(32)
          public $level;  //level	int

          public function __construct($newuser) {
              $this->uindex = $newuser['uindex'];
              $this->uname = $newuser['uname'];
              $this->phash = $newuser['phash'];
              $this->email = $newuser['email'];
              $this->level = $newuser['level'];
          }

          public static function getUserByIndex($uindex)
          {
              include "mysql_connect.php";
              $mysqli->real_escape_string($uindex);
              $sql = "SELECT * FROM Users WHERE uindex='$uindex';";

              if (!$result = $mysqli->query($sql)) {
                  echo $mysqli->error;
                  return null;
              }

              if ($result->num_rows === 0) {
                  return null;
              }

              $user = $result->fetch_assoc();

              $mysqli->close();

              return new User($user);
          }

          public static function getUserByName($name)
          {
              include "mysql_connect.php";

              $mysqli->real_escape_string($name);
              $sql = "SELECT * FROM Users WHERE uname='$name';";

              if (!$result = $mysqli->query($sql)) {
                  echo $mysqli->error;
                  return null;
              }

              if ($result->num_rows === 0) {
                  return null;
              }

              $user = $result->fetch_assoc();

              $mysqli->close();

              return new User($user);
          }

          public static function create($uname,$upass,$email,$level)
          {
              include "mysql_connect.php";//HACK real escape string prevent sqli
              $options = [
                    'cost' => 12,
                ];
              $phash = password_hash($upass, PASSWORD_BCRYPT, $options);
              $sql = "INSERT INTO Users (`uname`, `phash`, `email`, `level`) VALUES ('$uname', '$phash', '$email', '$level');";

              if(!$mysqli->query($sql))
              {
                  echo $mysqli->error;
              }
              $mysqli->close();

              return User::getUserByName($uname);
          }

          public function update()
          {
              if(User::getUserByIndex($this->uindex) == null)
              {
                  //new
                  include "mysql_connect.php";
                  $sql = "INSERT INTO Users (`uname`, `phash`, `email`, `level`) VALUES ('$this->uname', '$this->phash', '$this->email', '$this->level');";

                  $mysqli->query($sql);

                  $mysqli->close();
              }
              else
              {
                  //Update
                  include "mysql_connect.php";
                  $sql = "UPDATE Users SET `uname`='$this->uname', `phash`='$this->phash', `email`='$this->email', `level`='$this->level' WHERE `uindex`='$this->uindex';"; //TODO Prevent sqli

                  $mysqli->query($sql);

                  $mysqli->close();
              }
          }

          public function remove()
          {
              if(User::getUserByIndex($this->uindex) == null)
              {
                  //Does not exist.
              }
              else
              {
                  //Exists.
                  include "mysql_connect.php";
                  $sql = "DELETE FROM Users WHERE uindex='$this->uindex'";
                  if(!$mysqli->query($sql))
                  {
                      echo $mysqli->error;
                  }
                  $mysqli->close();
              }
          }

          public static function getAll()
          {
              $sql = "SELECT * FROM Users;";
              include "mysql_connect.php";

              if (!$result = $mysqli->query($sql)) {
                  echo $mysqli->error;
                  return null;
              }

              if ($result->num_rows === 0) {
                  echo "No results";
                  return null;
              }
              $users = array();

              while($row = $result->fetch_assoc()) {
                  array_push($users, new User($row));
              }
              $mysqli->close();

              return $users;
          }
      }

      class Location
      {
          public $lindex;   //lindex	int
          public $uindex;   //uindex	int
          public $name;     //name	varchar(32)
          public $address;  //address	varchar(64)
          public $ssid;     //Wifi name varchar(32)
          public $wifi_pass;//Password of the wifi

          public static function create($name,$owner,$addr,$ssi,$pass)
          {
              include "mysql_connect.php";
              $lindex = null;
              $name = $mysqli->real_escape_string($name);
              $owner = $mysqli->real_escape_string($owner);
              $addr = $mysqli->real_escape_string($addr);
              $ssi = $mysqli->real_escape_string($ssi);
              $pass = $mysqli->real_escape_string($pass);

              $sql = "INSERT INTO Location (uindex,name,address,ssid,wifi_pass) VALUES ($owner,'$name','$addr','$ssi','$pass');";

              if(!$mysqli->query($sql))
              {
                  echo $mysqli->error;
              }

              $sql = "SELECT LAST_INSERT_ID() AS _LAST;";

              if (!$result = $mysqli->query($sql)) {
                  echo $mysqli->error;
              }

              if ($result->num_rows === 0) {
                  echo "Was not inserted";
              }

              $location = $result->fetch_assoc();

              $lindex = $location['_LAST'];

              $mysqli->close();

              return $lindex;
          }

          public function  __construct($array)
          {
              $this->lindex = $array['lindex'] ;
              $this->uindex = $array['uindex'] ;
              $this->name = $array['name'] ;
              $this->address = $array['address'] ;
              $this->ssid = $array['ssid'] ;
              $this->wifi_pass = $array['wifi_pass'] ;
          }

          public static function getLocationByIndex($index)
          {
              include "mysql_connect.php";

              $sql = "SELECT * FROM Location WHERE lindex='$index';";

              if (!$result = $mysqli->query($sql)) {
                  echo $mysqli->error;
                  return null;
              }

              if ($result->num_rows === 0) {
                  echo "Location not found";
                  return null;
              }

              $location = $result->fetch_assoc();

              $mysqli->close();

              return new Location($location);
          }

          public static function getLocationIndexByApikey($apik)
          {
              $apikey = ApiKey::getByApikey($apik);
              if(!$apikey)
              {
                  echo "Key not found";
                  return null;
              }
              include "mysql_connect.php";

              $sql = "SELECT * FROM Location WHERE lindex='$apikey->lindex';";

              if (!$result = $mysqli->query($sql)) {
                  echo $mysqli->error;
                  return null;
              }

              if ($result->num_rows === 0) {
                  echo "Location not found";
                  return null;
              }

              $location = $result->fetch_assoc();

              $mysqli->close();

              return new Location($location);
          }

          public static function getAllLocations()
          {
              include "mysql_connect.php"; //W
              $sql = "SELECT * FROM Location;";
              $locations = array();

              if (!$result = $mysqli->query($sql)) {
                  echo $mysqli->error;
                  return null;
              }

              if ($result->num_rows === 0) {
                  echo "No locations found";
                  return null;
              }

              while($row = $result->fetch_assoc()) {
                  array_push($locations, new Location($row));
              }
              $mysqli->close();
              return $locations;
          }

          public static function getAllLocationsForUser($uindex)
          {
              include "mysql_connect.php";
              $sql = "SELECT * FROM Location WHERE luser='$uindex';";

              if (!$result = $mysqli->query($sql)) {
                  echo $mysqli->error;
                  return null;
              }
              if ($result->num_rows == 0) {
                  echo "No locations found";
                  return null;
              }
              $locations = array();
              while($row = $result->fetch_assoc()) {
                  array_push($locations, new Location($row));
              }
              $mysqli->close();
              return $locations;
          }

      }

      class Audit
      {
          public $aindex;
          public $lindex;
          public $atime;
          public $aname;

          public function  __construct($array)
          {
              $this->aindex = $array['aindex'] ;
              $this->lindex = $array['lindex'] ;
              $this->atime = $array['atime'] ;
              $this->aname = $array['aname'] ;
          }

          public static function create($location_index)
          {
              $date = new \DateTime();
              $newtable = "Audit_" . sha1($date->getTimestamp());
              include "mysql_connect.php";
              $sql = "  CREATE TABLE `$newtable` (
	                        `dindex` int NOT NULL,
	                        `lindex` int NOT NULL,
	                        `machine_numb` int NOT NULL,
	                        `date` DATETIME NOT NULL,
	                        `change` int NOT NULL,
	                        PRIMARY KEY (`dindex`)
                            );";
               $mysqli->query($sql);
               echo $mysqli->error;

               $sql = "INSERT INTO `$newtable`
                            SELECT * from `Data`
                            WHERE lindex=$location_index;";
               $mysqli->query($sql);
               echo $mysqli->error;

               $sql = "DELETE FROM Data
                            WHERE lindex = $location_index;";
              $mysqli->query($sql);
              echo $mysqli->error;

              $sql = "INSERT INTO `Audit` (`aindex`, `lindex`, `atime`, `aname`) VALUES (NULL, $location_index,  CURRENT_TIMESTAMP, '$newtable');";
              $mysqli->query($sql);
              echo $mysqli->error;

              $id = $mysqli->insert_id;

              $mysqli->close();

              return $id;
          }

          public static function getAllAuditsOfLocation($loc)
          {
              $sql = "SELECT * FROM Audit WHERE lindex=$loc";
              include "mysql_connect.php";

              if (!$result = $mysqli->query($sql)) {
                  echo $mysqli->error;
                  return null;
              }

              if ($result->num_rows === 0) {
                  return null;
              }

              $locations = array();

              while($row = $result->fetch_assoc()) {
                  array_push($locations, new Audit($row));
              }
              $mysqli->close();
              return $locations;
          }

          public function getDataOfAudit()
          {
              $name = $this->aname;
              $sql = "SELECT * FROM $name;";
              include "mysql_connect.php";

              if (!$result = $mysqli->query($sql)) {
                  echo $mysqli->error;
                  return null;
              }

              if ($result->num_rows === 0) {
                  echo "No data";
                  return null;
              }

              $datas = array();

              $mysqli->close();

              while($row = $result->fetch_assoc()) {
                  array_push($datas, new Data($row));
              }

              return $datas;
          }

          public static function getAuditByIndex($index)
          {
              $sql = "SELECT * FROM Audit WHERE aindex=$index";
              include "mysql_connect.php";

              if (!$result = $mysqli->query($sql)) {
                  echo $mysqli->error;
                  return null;
              }

              if ($result->num_rows === 0) {
                  return null;
              }

              $location = $result->fetch_assoc();

              $mysqli->close();

              return new Audit($location);
          }
      }

      class Data{
          public $dindex;
          public $lindex;
          public $machine_numb;
          public $date;
          public $change;

          public function  __construct($array)
          {
              $this->dindex = $array['dindex'];
              $this->lindex = $array['lindex'];
              $this->machine_numb = $array['machine_numb'];
              $this->date = $array['date'];
              $this->change = $array['change'];
          }

          public static function getMachineDataOfLocation($location,$machine)
          {
              $sql = "Select * FROM Data WHERE lindex = " . $location . " AND machine_numb=" . $machine . ";"; //TODO: Vulnerable to mysql inject.
              include "mysql_connect.php";

              if (!$result = $mysqli->query($sql)) {
                  echo $mysqli->error;
                  return null;
              }

              if ($result->num_rows === 0) {
                  return null;
              }
              $locations = array();

              while($row = $result->fetch_assoc()) {
                  array_push($locations, new Data($row));
              }
              $mysqli->close();

              return  $locations;
          }

          public static function getAllDataOfLocation($location)
          {
              $sql = "Select * FROM Data WHERE lindex = " . $location . " ORDER BY machine_numb ASC;"; //TODO: Vulnerable to mysql inject.
              include "mysql_connect.php";

              if (!$result = $mysqli->query($sql)) {
                  echo $mysqli->error;
                  return null;
              }

              if ($result->num_rows === 0) {
                  echo "no rows";
                  return null;
              }

              $locations = array();

              while($row = $result->fetch_assoc()) {
                  array_push($locations, new Data($row));
              }
              $mysqli->close();

              return $locations;
          }

          public static function create($lindex,$machine_numb,$date,$change)
          {
              include "mysql_connect.php";
              $dateObj = \DateTime::createFromFormat("d-m-Y_H-i-s", $date);
              if (!$dateObj)
              {
                  echo "Could not parse the date: $date";
              }
              $date = $dateObj->format("Y-m-d H:i:s");
              $sql = "INSERT INTO Data (`lindex`, `machine_numb`, `date`, `change`) VALUES ($lindex, $machine_numb, '$date', $change);";
              //echo $sql;
              if(!$mysqli->query($sql))
              {
                  echo $mysqli->error;
              }
              $mysqli->close();
          }
      }

      class ApiKey{
          public $aindex = -1;  //aindex	int
          public $lindex;       //lindex	int
          public $keystring;    //keystring	varchar(32)



          public static function create($location)//return aindex
          {
              $lindex = null;
              $key = rand_sha1(32);
              include "mysql_connect.php";
              $location = $mysqli->real_escape_string($location);
              $sql =  "INSERT INTO ApiKeys (lindex,keystring) VALUES ($location,'$key');";

              if(!$mysqli->query($sql))
              {
                  echo $mysqli->error;
              }

              $sql = "SELECT LAST_INSERT_ID() AS _LAST;";

              if (!$result = $mysqli->query($sql)) {
                  echo $mysqli->error;
              }

              if ($result->num_rows === 0) {
                  echo "Was not inserted";
              }

              $location = $result->fetch_assoc();

              $lindex = $location['_LAST'];

              $mysqli->close();

              return $lindex;
          }

          public function __construct($key) {
              $this->aindex = $key['aindex'];
              $this->lindex = $key['lindex'];
              $this->keystring = $key['keystring'];
          }

          public static function getByApikey($apik)
          {
              include "mysql_connect.php";
              $mysqli->real_escape_string($apik);
              $sql = "SELECT * FROM ApiKeys WHERE keystring='$apik';";

              if (!$result = $mysqli->query($sql)) {
                  echo $mysqli->error;
                  return null;
              }

              if ($result->num_rows === 0) {
                  echo "No key found";
                  return null;
              }

              $key = $result->fetch_assoc();

              $mysqli->close();

              return new ApiKey($key);
          }

          public static function getApikForLocationIndex($lindex)
          {
              $sql = "SELECT * FROM ApiKeys WHERE lindex = $lindex;";

              include "mysql_connect.php";

              if (!$result = $mysqli->query($sql)) {
                  echo $mysqli->error;
                  return null;
              }

              if ($result->num_rows === 0) {
                  echo "No key found";
                  return null;
              }

              $key = $result->fetch_assoc();


              $mysqli->close();

              return new ApiKey($key);
          }
      }
    }
}

?>