<?php
/*
	Name: Update Journal
*/

// Initialise session
session_start();

define('APP_RAN', '');

require_once('../config.php');
require_once('../content_filters.php');
require_once('../Parsedown.php');
require_once('../ParsedownExtra.php');

// Get auth string from database

$authsql = $connsel->prepare("SELECT Option_Value FROM " . OPTIONS . " WHERE Option_Name = 'Auth' ");
$authsql->execute();
$authresult = mysqli_stmt_get_result($authsql);
$row = $authresult->fetch_assoc();
$dbauth = $row["Option_Value"];
$authsql->close();

// admin check

if (!$_SESSION['auth'] == $dbauth) {
	die("Private!");
}

// Update entry

if ( isset($_POST['updatepost']) && ($_POST['newcontent'] !='') ) {
    if ($_SESSION['auth'] == $dbauth) {
	    $newcontent = $_POST['newcontent'];
	    $date = $_POST['when'];
	    
	    setOption('Now_Text', $newcontent);
	    setOption('Now_Updated', $date);
	    
	    echo json_encode($newcontent);
	    exit();
	}
}

	$now = getOption('Now_Text');
	
	$content = $now;
	$raw_content = $content;
	$content = '@@ ' . $content;
	$content = filters($content);
	
	$Parsedown = new ParsedownExtra();
	$content = $Parsedown->text($content);
	$content = str_replace('@@', '<a><span style=" margin-right: 10px;">#</span></a>', $content);
	
	echo json_encode($content);
	exit();

?>