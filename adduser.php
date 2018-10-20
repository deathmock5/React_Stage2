<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

	if(isset($_POST['post_users']))
	{
		//We have a post
		$user = $_POST['post_users'];
		$level = $_POST['post_level'];
		$pass = $_POST['post_pass'];

		//Hash the password
		$hash = password_hash($pass, PASSWORD_DEFAULT);

		$sql = "INSERT INTO users (uname,level,passhash) VALUES ($user,$level,$hash);";

		include "mysql_connect.php"; //Grants access to $mysqli-> variable.

		if(!$mysqli->query($sql))
            {
                echo $mysqli->error;
            }

		$mysqli->close();
	}
	else
	{
		//display form
		echo '<title>API Backend</title>
  </head>
  <body>
  <form action="adduser.php" method="POST">
  <table>
	<tr>
		<td>
		<h3>Username:</h3>
		</td>
		<td>
			<input type="text" name="post_users">
		</td>
	</tr>
	<tr>
		<td>
    <h3>level:</h3>
	</td>
		<td>
		<select name="post_level">';


		include "mysql_connect.php"; //Grants access to $mysqli-> variable.

		$sql = "SELECT * FROM levels;";
		$result = $mysqli->query($sql);

		while ($row = $result->fetch_assoc()) {
			echo "<option value=\"" . $row['level_index'] ."\">" . $row['lname'] . "</option>";
		}

		echo '</select>
		</td>
	</tr>
	<tr>
		<td>
    <h3>Password</h3>
	</td>
		<td>
    <input type="password" name="post_pass">
		</td>
	</tr>
	<tr>
		<td>
    <h3>Post:</h3>
	</td>
		<td>
    <input type="submit"></input>
		</td>
	</tr>
	</table>
</form>';
	}
?>


