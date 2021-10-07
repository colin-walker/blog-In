<?php
/**
	Name: setup
**/


$root = $_SERVER['DOCUMENT_ROOT'];
$file = $root . '/config.php';

if ( file_exists( $file ) ) {
		exit('You are already configured.');
}

	if ( $_POST['name'] ) {
		
		$sitename = $_POST['name'];
		$url = $_POST['url'];
		$login = $_POST['login'];
		$password = md5($_POST['password']);
		$email = $_POST['email'];
		$dbname = $_POST['dbname'];
		$dbread = $_POST['dbread'];
		$readpass = $_POST['readpass'];
		$dbwrite = $_POST['dbwrite'];
		$writepass = $_POST['writepass'];
		$prefix = $_POST['prefix'];
		$dbserver = 'localhost';
		
		$createfile = fopen($file,'w');
		fwrite($createfile,'<?php'.PHP_EOL);
		fwrite($createfile,''.PHP_EOL);
		fwrite($createfile,'if(!defined("APP_RAN")){ die(); }'.PHP_EOL);
		fwrite($createfile,''.PHP_EOL);
		//fwrite($createfile,'define("NAME", "' . $sitename . '");'.PHP_EOL);
		fwrite($createfile,'define("BASE_URL", "' . $url . '");'.PHP_EOL);
		fwrite($createfile,'define("MAILTO", "' . $email . '");'.PHP_EOL);
		fwrite($createfile,''.PHP_EOL);
		fwrite($createfile,'define("DB_SERVER", "localhost");'.PHP_EOL);
		fwrite($createfile,'define("DB_NAME", "' . $dbname . '");'.PHP_EOL);
		fwrite($createfile,''.PHP_EOL);
		fwrite($createfile,'define("DB_USERNAME", \'' . $dbwrite . '\');'.PHP_EOL);
		fwrite($createfile,'define("DB_PASSWORD", \'' . $writepass . '\');'.PHP_EOL);
		fwrite($createfile,''.PHP_EOL);
		fwrite($createfile,'$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);'.PHP_EOL);
		fwrite($createfile,'$conn->set_charset("utf8mb4_unicode_ci");'.PHP_EOL);
		fwrite($createfile,''.PHP_EOL);
		fwrite($createfile,'define("DB_USERNAMESEL", \'' . $dbread . '\');'.PHP_EOL);
		fwrite($createfile,'define("DB_PASSWORDSEL", \'' . $readpass . '\');'.PHP_EOL);
		fwrite($createfile,''.PHP_EOL);
		fwrite($createfile,'$connsel = new mysqli(DB_SERVER, DB_USERNAMESEL, DB_PASSWORDSEL, DB_NAME);'.PHP_EOL);
		fwrite($createfile,'$connsel->set_charset("utf8mb4_unicode_ci");'.PHP_EOL);
		fwrite($createfile,''.PHP_EOL);
		fwrite($createfile,'define("OPTIONS", "' . $prefix . '_options");'.PHP_EOL);
		fwrite($createfile,'define("POSTS", "' . $prefix . '_posts");'.PHP_EOL);
		fwrite($createfile,'define("COMMENTS", "' . $prefix . '_comments");'.PHP_EOL);
		fwrite($createfile,'define("JOURNAL", "' . $prefix . '_journal");'.PHP_EOL);		fwrite($createfile,''.PHP_EOL);
		
		fwrite($createfile,'function getOption($name) {'.PHP_EOL);
		fwrite($createfile,'$connsel = new mysqli(DB_SERVER, DB_USERNAMESEL, DB_PASSWORDSEL, DB_NAME);'.PHP_EOL);
		fwrite($createfile,'$option_sql = $connsel->prepare("SELECT Option_Value FROM " . OPTIONS . " WHERE Option_Name=? ");'.PHP_EOL);
		fwrite($createfile,'$option_sql->bind_param("s", $name);'.PHP_EOL);
		fwrite($createfile,'$option_sql->execute();'.PHP_EOL);
		fwrite($createfile,'$option_result = mysqli_stmt_get_result($option_sql);'.PHP_EOL);
		fwrite($createfile,'$row = $option_result->fetch_assoc();'.PHP_EOL);
		fwrite($createfile,'$value = $row["Option_Value"];'.PHP_EOL);
		fwrite($createfile,'$option_sql->close();'.PHP_EOL);
		fwrite($createfile,'return $value;'.PHP_EOL);
		fwrite($createfile,'}'.PHP_EOL);
		
		fwrite($createfile,'function setOption($name, $value) {'.PHP_EOL);
		fwrite($createfile,'$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);'.PHP_EOL);
		fwrite($createfile,'$sql = $conn->prepare("UPDATE " . OPTIONS . " SET Option_Value=? WHERE Option_Name=? ");'.PHP_EOL);
		fwrite($createfile,'$sql->bind_param("ss", $value, $name);'.PHP_EOL);
		fwrite($createfile,'$sql->execute();'.PHP_EOL);
		fwrite($createfile,'$sql->close();'.PHP_EOL);
		fwrite($createfile,'}'.PHP_EOL);
				
		fwrite($createfile,''.PHP_EOL);
		fwrite($createfile,'define("DESCRIPTION", getOption("Description"));'.PHP_EOL);
		fwrite($createfile,'$avatar = getOption("Avatar");'.PHP_EOL);
		fwrite($createfile,'define("AVATAR", $avatar);'.PHP_EOL);
		fwrite($createfile,''.PHP_EOL);
		fwrite($createfile,'if (getOption("Date_Format") == "UK") {'.PHP_EOL);
		fwrite($createfile,'define("DAY_SLASH", "d/m/Y");'.PHP_EOL);
		fwrite($createfile,'define("DATE_META", "d/m/Y g:ia");'.PHP_EOL);
		fwrite($createfile,'} else {'.PHP_EOL);
		fwrite($createfile,'define("DAY_SLASH", "m/d/Y");'.PHP_EOL);
		fwrite($createfile,'define("DATE_META", "m/d/Y g:ia");'.PHP_EOL);
		fwrite($createfile,'}'.PHP_EOL);
		fwrite($createfile,'define("TIMEZONE", getOption("Timezone"));'.PHP_EOL);
		fwrite($createfile,''.PHP_EOL);
		fwrite($createfile,'define("SMTPHOST", getOption("SMTP_Host"));'.PHP_EOL);
		fwrite($createfile,'define("SMTPUSER", getOption("SMTP_Username"));'.PHP_EOL);
		fwrite($createfile,'define("SMTPPASS", getOption("SMTP_Password"));'.PHP_EOL);
		fwrite($createfile,'define("SMTPPORT", getOption("SMTP_Port"));'.PHP_EOL);
		fwrite($createfile,''.PHP_EOL);
		fwrite($createfile,'define("INSTALL_DATE", getOption("Install_Date"));'.PHP_EOL);
		fwrite($createfile,''.PHP_EOL);
		fwrite($createfile,'?>');	
		fclose($createfile);
		
		$conn = new mysqli($dbserver, $dbwrite, $writepass, $dbname);
		
		$sql = "CREATE TABLE " . $prefix . "_options (
 					`ID` int(11) NOT NULL AUTO_INCREMENT,
 					`Option_Name` text COLLATE utf8mb4_unicode_ci NOT NULL,
 					`Option_Value` text COLLATE utf8mb4_unicode_ci NOT NULL,
 					PRIMARY KEY (`ID`)
					) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

		if ($conn->query($sql) === TRUE) {
			//echo "Table " . $prefix . "_options created successfully".PHP_EOL;
		} else {
  			echo "Error creating table: " . $conn->error;
		}

		$sql = "CREATE TABLE " . $prefix . "_posts (
					`ID` int(1) NOT NULL AUTO_INCREMENT,
					`Permalink` text COLLATE utf8mb4_unicode_ci NOT NULL,
 					`Section` int(11) NOT NULL,
 					`Title` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
 					`Content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
 					`Date` text COLLATE utf8mb4_unicode_ci NOT NULL,
 					`Day` text COLLATE utf8mb4_unicode_ci NOT NULL,
 					`Draft` text COLLATE utf8mb4_unicode_ci NOT NULL,
 					PRIMARY KEY (`ID`)
					) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
					
		if ($conn->query($sql) === TRUE) {
  			//echo "Table " . $prefix . "_posts created successfully".PHP_EOL;
		} else {
  			echo "Error creating table: " . $conn->error;
		}
		
		$sql = "CREATE TABLE " . $prefix . "_comments (
 					`ID` int(11) NOT NULL AUTO_INCREMENT,
 					`Parent` int(11) NOT NULL,
 					`Name` text COLLATE utf8mb4_unicode_ci NOT NULL,
 					`Photo` text COLLATE utf8mb4_unicode_ci NOT NULL,
 					`Website` text COLLATE utf8mb4_unicode_ci NOT NULL,
 					`Comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
 					`Mention` text COLLATE utf8mb4_unicode_ci NOT NULL,
 					`isLike` text COLLATE utf8mb4_unicode_ci NOT NULL,
 					`isReply` text COLLATE utf8mb4_unicode_ci NOT NULL,
 					`Fragmention` text COLLATE utf8mb4_unicode_ci NOT NULL,
 					`InReplyTo` int(11) NOT NULL,
 					`Moderated` int(11) NOT NULL,
 					PRIMARY KEY (`ID`)
					) ENGINE=MyISAM AUTO_INCREMENT=578 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
					
		if ($conn->query($sql) === TRUE) {
  			//echo "Table " . $prefix . "_comments created successfully".PHP_EOL;
		} else {
  			echo "Error creating table: " . $conn->error;
		}
		
		$sql = "CREATE TABLE " . $prefix . "_journal (
 					`ID` int(11) NOT NULL AUTO_INCREMENT,
 					`Content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
 					`Day` text COLLATE utf8mb4_unicode_ci NOT NULL,
 					PRIMARY KEY (`ID`)
					) ENGINE=MyISAM AUTO_INCREMENT=190 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
					
		if ($conn->query($sql) === TRUE) {
  			//echo "Table " . $prefix . "_comments created successfully".PHP_EOL;
		} else {
  			echo "Error creating table: " . $conn->error;
		}

		
		
		
		$conn->close();
		
		$options = $prefix."_options";
		
		$conn = new mysqli($dbserver, $dbwrite, $writepass, $dbname);
		$sql = "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('Sitename', '" . $sitename ."');";
		$sql = "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('Login', '" . $login ."');";
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('Password', '" . $password ."');";
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('Auth', '');";
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('Avatar', '');";
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('Description', '');";
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('About_Type', 'name');";
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('About', '');";
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('Licence', 'yes');";
		
		
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('SMTP_Host', '');";
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('SMTP_Username', '');";
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('SMTP_Password', '');";
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('SMTP_Port', '');";
		
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('Post_Order', 'ASC');";
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('Post_Titles', 'no');";
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('Post_Meta', 'no');";
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('Moderate_Comments', 'no');";
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('Date_Format', 'UK');";
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('Timezone', 'Europe/London');";
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('Journal', 'no');";
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('Install_Date', '" . date('Y-m-d') . "');";
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('Use_Now', 'no');";
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('Now_Text', '');";
		$sql .= "INSERT INTO " . $options . " (Option_Name, Option_Value) VALUES ('Now_Updated', '');";

		
		if ($conn->multi_query($sql) === TRUE) {
			session_start();

            $hash = password_hash($password, PASSWORD_DEFAULT);

			$newconn = new mysqli($dbserver, $dbwrite, $writepass, $dbname);
            $hash_sql = $newconn->prepare("UPDATE " . $options . " SET Option_Value=? WHERE Option_Name = 'Auth' ");
            $hash_sql->bind_param("s", $hash);
            $hash_sql->execute();
            $hash_sql->close();
            $newconn->close();
            
            $_SESSION['loggedin'] = 'true';
            $_SESSION['auth'] = $hash;
            
            $session_id = session_id();
    		header("location: /admin/");
			exit;
  		} else { 
    		echo "Error: " . $sql . "<br>" . $conn->error;
    		
  		}
		$conn->close();

} else {

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>Setup</title>
    <link rel="stylesheet" href="/style.css">
    <style type="text/css">
        .wrapper{ width: 450px; }
    </style>
</head>
<body>
    <div class="wrapper">
	    <h2 class="titleSpan">Setup</h2>
	    <form id="setup_form" method="post">
            <div>
                <label>Sitename</label>
                <input type="text" name="name" class="form-control" value="" required>
                <label>URL</label>
                <input type="url" name="url" class="form-control" value="" required>
                <label>Admin account</label>
                <input type="text" name="login" class="form-control" value="" required>
                <label>Admin password</label>
                <input type="password" name="password" class="form-control" value="" required>
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="" required>
                <label>DB name</label>
                <input type="text" name="dbname" class="form-control" value="" required>
                <label>DB User (read)</label>
                <input type="text" name="dbread" class="form-control" value="" required>
                <label>Password</label>
                <input type="password" name="readpass" class="form-control" value="" required>
                <label>DB User (write)</label>
                <input type="text" name="dbwrite" class="form-control" value="" required>
                <label>Password</label>
                <input type="password" name="writepass" class="form-control" value="" required>
                <label>Table prefix</label>
                <input type="text" name="prefix" class="form-control" value="" required>
                <div style="text-align: right; padding-right: 3px;"><input type="submit" value="Setup" style="font-size: 14px; font-weight: bold;"></div>
            </div>

<?php
}
?>

	</div>
	
</body>
</html>