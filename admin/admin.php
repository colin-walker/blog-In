<?php
/**
	Name: admin
**/

// Initialize the session
session_start();

define('APP_RAN', '');

// Include config file
require_once('../config.php');
require_once('../content_filters.php');
require_once('../Parsedown.php');
require_once('../ParsedownExtra.php');

$file = dirname(__FILE__) . '../setup.php';
if ( file_exists( $file ) ) {
    unlink( $file );
}

$authsql = $connsel->prepare("SELECT Option_Value FROM " . OPTIONS . " WHERE Option_Name = 'Auth' ");
$authsql->execute();
$authresult = mysqli_stmt_get_result($authsql);
$row = $authresult->fetch_assoc();
$dbauth = $row["Option_Value"];
$authsql->close();

if ($_SESSION['auth'] != $dbauth) {
  header("location: " . BASE_URL );
  exit;
}

if ($_POST['update'] == 'true') {
    $login = $_POST['username'];
    $avatar = $_POST['avatar'];
    $description = $_POST['description'];
    $about = $_POST['about'];
    $smtphost = $_POST['smtphost'];
    $smtpuser = $_POST['smtpuser'];
    $smtppass = $_POST['smtppass'];
    $smtpport = $_POST['smtpport'];
    $postorder = $_POST['postorder'];
    $posttitles = $_POST['posttitles'];
    $postmeta = $_POST['postmeta'];
    $commentmod = $_POST['commentmod'];
    $dateformat = $_POST['dateformat'];
    $timezone = $_POST['timezone'];
    $journal = $_POST['journal'];
    $now = $_POST['now'];
    
    $Parsedown = new ParsedownExtra();
  	$mdabout = $Parsedown->text($about);
    
    $username = getOption('Login');
    $dbavatar = getOption('Avatar');
    $dbdesc = getOption('Description');
    $dbabout = getOption('About');
    $dbsmtphost = getOption('SMTP_Host');
    $dbsmtpuser = getOption('SMTP_Username');
    $dbsmtppass = getOption('SMTP_Password');
    $dbsmtpport = getOption('SMTP_Port');    
    $dborder = getOption('Post_Order');
    $dbtitles = getOption('Post_Titles');
    $dbmeta = getOption('Post_Meta');
    $dbmod = getOption('Moderate_Comments');
    $dbdate = getOption('Date_Format');
    $dbtz = getOption('Timezone');
    $dbjournal = getOption('Journal');
    $dbnow = getOption('Use_Now');


// Base options
    
    if ($login != $username) {
        setOption('Login', $login);
        $changeStr .= 'Login name changed.<br/>';
    }
    
    if ($avatar != $dbavatar) {
        setOption('Avatar', $avatar);
        $changeStr .= 'Avatar changed.<br/>';
    }
    
    if ($description != $dbdesc) {
        setOption('Description', $description);
        $changeStr .= 'Description changed.<br/>';
    }
    
    $Parsedown = new ParsedownExtra();
    $dbmd = $Parsedown->text($dbabout);
    
    if ($about != $dbabout) {
        setOption('About', $about);
        $changeStr .= 'About text changed.<br/>';
    }
    
// SMTP Options

    if ($smtphost != $dbsmtphost) {
        setOption('SMTP_Host', $smtphost);
        $changeStr .= 'SMTP Host changed.<br/>';
    }

    if ($smtpuser != $dbsmtpuser) {
        setOption('SMTP_Username', $smtpuser);
        $changeStr .= 'SMTP Username changed.<br/>';
    }

    if ($smtppass != $dbsmtppass) {
        setOption('SMTP_Password', $smtppass);
        $changeStr .= 'SMTP Password changed.<br/>';
    }

    if ($smtpport != $dbsmtpport) {
        setOption('SMTP_Port', $smtpport);
        $changeStr .= 'SMTP Port changed.<br/>';
    }    


// Post & date/time
    
    if ($postorder != $dborder) {
        setOption('Post_Order', $postorder);
        $changeStr .= 'Post order changed.<br/>';
    }
    
    if ($posttitles != $dbtitles) {
        setOption('Post_Titles', $posttitles);
        $changeStr .= 'Post titles changed.<br/>';
    }
    
    if ($postmeta != $dbmeta) {
        setOption('Post_Meta', $postmeta);
        $changeStr .= 'Post meta changed.<br/>';
    }
    
    if ($commentmod != $dbmod) {
        setOption('Moderate_Comments', $commentmod);
        $changeStr .= 'Comment moderation changed.<br/>';
    }
    
    if ($dateformat != $dbdate) {
        setOption('Date_Format', $dateformat);
        $changeStr .= 'Date format changed.<br/>';
    }
    
    if ($timezone != $dbtz) {
        setOption('Date_Format', $timezone);
        $changeStr .= 'Timezone changed.<br/>';
    }

    if ($now != $dbnow) {
        setOption('Use_Now', $now);
        $changeStr .= 'Now page status changed.<br/>';
    }

    if ($journal != $dbjournal) {
        setOption('Journal', $journal);
        $changeStr .= 'Journal status changed.<br/>';
    }
}
    
if ( $_POST['passcheck'] == 'true' ) {
    $pass = md5($_POST['password']);
    setOption('Password', $pass);
    $changeStr = 'Password changed';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <title>Admin</title>
    <link rel="stylesheet" href="/style.css">

</head>
<body>

	<a accesskey="c" href="<?php echo BASE_URL; ?>"><img  style="
            position: absolute;
            top: 22px;
            right: 22px;
            font-size: 23px;
            cursor: pointer;
            color: #333;
            z-index: 100;
            width: 23px;
            display: block;" src="../images/cancel.png" />
    </a>
    
    <?php
    if (($_POST['update'] == 'true') || ($_POST['passcheck'] == 'true')) {
        if ($changeStr == '') {
            $changeStr = 'Nothing changed';
        }
    ?>
        <div class="wrapper">
            <div style="text-align: center; padding: 25px 0;">
                <strong>Done ✓</strong>
                <p>
                    <?php echo $changeStr; ?>
                </p>
                <br/>
                <a href="admin.php"><strong>More changes?</strong></a>
            </div>
        </div>
    <?php
    } else {
    ?>            
    <div class="wrapper">
    	<h2 class="titleSpan">Admin</h2>
		<form id="admin_form" method="post">
            <div>
                <input type="hidden" name="update" value="true">
            	<label>Username</label>
 				<input type="text" id="username" name="username" class="form-control" value="<?php echo getOption('Login'); ?>">
            	<label>Avatar</label>
 				<input type="text" name="avatar" class="form-control" value="<?php echo getOption('Avatar'); ?>">
            	<label>Description</label>
 				<textarea rows="3" name="description" class="form-control" style="height: 42px;"><?php echo getOption('Description'); ?></textarea>
            	<label>About</label>
 				<textarea rows="10" name="about" class="form-control" style="height: 150px; margin-bottom: 50px;"><?php echo getOption('About'); ?></textarea>
 				
            	<label>SMTP host</label>
 				<input type="text" name="smtphost" class="form-control" value="<?php echo getOption('SMTP_Host'); ?>">
            	<label>SMTP username</label>
 				<input type="text" name="smtpuser" class="form-control" value="<?php echo getOption('SMTP_Username'); ?>">
            	<label>SMTP password</label>
 				<input type="password" id="smtppass" name="smtppass" class="form-control" value="<?php echo getOption('SMTP_Password'); ?>">
 				<div style="text-align: right; padding-right: 0px; position: relative; top: -5px; margin-bottom: -20px;">Show <input type="checkbox" onclick="toggleSMTP()" style="transform: scale(1.3); position: relative; top: -1px;"></div>
            	<label>SMTP port</label>
 				<input type="text" name="smtpport" class="form-control" style="margin-bottom: 50px;" value="<?php echo getOption('SMTP_Port'); ?>">
 				
 				<label>Post order</label>
 				<select name="postorder" class="form-control" style="width: 100%;">
 				  <option value="ASC" <?php if(getOption('Post_Order') == 'ASC') { echo 'selected'; } ?>>ASC (chronological)</option>
 				  <option value="DESC"<?php if(getOption('Post_Order') == 'DESC') { echo 'selected'; } ?>>DESC (reverse-chronological)</option>
 				</select>
 				<label>Post titles</label>
 				<select name="posttitles" class="form-control" style="width: 100%;">
 				  <option value="no"<?php if(getOption('Post_Titles') == 'no') { echo 'selected'; } ?>>no</option>
 				  <option value="yes" <?php if(getOption('Post_Titles') == 'yes') { echo 'selected'; } ?>>yes</option>
 				</select>
 				<label>Post meta</label>
 				<select name="postmeta" class="form-control" style="width: 100%;">
 				  <option value="yes" <?php if(getOption('Post_Meta') == 'yes') { echo 'selected'; } ?>>yes (show date when order=DESC)</option>
 				  <option value="no"<?php if(getOption('Post_Meta') == 'no') { echo 'selected'; } ?>>no</option>
 				</select>
 				<label>Comment moderation</label>
 				<select name="commentmod" class="form-control" style="width: 100%; margin-bottom: 50px;">
 				  <option value="yes" <?php if(getOption('Moderate_Comments') == 'yes') { echo 'selected'; } ?>>yes</option>
 				  <option value="no"<?php if(getOption('Moderate_Comments') == 'no') { echo 'selected'; } ?>>no</option>
 				</select>
 				
 				<label>Date format</label>
 				<select name="dateformat" class="form-control" style="width: 100%;">
 				  <option value="UK" <?php if(getOption('Date_Format') == 'UK') { echo 'selected'; } ?>>UK (dd/mm/yyyy)</option>
 				  <option value="US"<?php if(getOption('Date_Format') == 'US') { echo 'selected'; } ?>>US (mm/dd/yyyy)</option>
 				</select>
            	<label>Timezone</label>
 				<input type="text" name="timezone" class="form-control" value="<?php echo getOption('Timezone'); ?>">
                
                <label>Now page</label>
 				<select name="now" class="form-control" style="width: 100%;">
 				  <option value="yes" <?php if(getOption('Use_Now') == 'yes') { echo 'selected'; } ?>>yes (show now link in footer)</option>
 				  <option value="no"<?php if(getOption('Use_Now') == 'no') { echo 'selected'; } ?>>no</option>
 				</select>            
                <label>Journal</label>
 				<select name="journal" class="form-control" style="width: 100%; margin-bottom: 50px;">
 				  <option value="yes" <?php if(getOption('Journal') == 'yes') { echo 'selected'; } ?>>yes (show journal streak)</option>
 				  <option value="no"<?php if(getOption('Journal') == 'no') { echo 'selected'; } ?>>no</option>
 				</select>
                <div style="text-align: right; margin-top: 12px; padding-right: 1px;"><input accesskey="u" type="submit" value="Update" style="font-size: 14px; font-weight: bold;"></div>
            </div>
        </form>
    </div>
    <div class="wrapper2">
        <form method="post">
            <div>
                <label>Password</label>
                <input type="hidden" name="passcheck" value="true">
 				<input type="password" id="password" name="password" class="form-control" value="" autocomplete="off">
 				<div style="text-align: right; padding-right: 0px; position: relative; top: -5px;">Show <input type="checkbox" onclick="togglePass()" style="transform: scale(1.3); position: relative; top: -1px;"></div>
 				<div style="text-align: right; margin-top: 12px; padding-right: 1px;"><input accesskey="p" type="submit" value="Change" style="font-size: 14px; font-weight: bold;" onClick="javascript: return confirm('Change password — are you sure?');"></div>
            </div>
        </form>
	</div>
	
	<?php
      }
  ?>    
	
	<script>
	  function togglePass() {
	    var pass = document.getElementById("password");
	    if (pass.type === "password") {
	        pass.type = "text";
	    } else {
	        pass.type = "password";
	    }
  	}
  	
	  function toggleSMTP() {
	    var pass = document.getElementById("smtppass");
	    if (pass.type === "password") {
	        pass.type = "text";
	    } else {
	        pass.type = "password";
	    }
  	}
	</script>
	
</body>
</html>