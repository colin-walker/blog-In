<?php
/*
	Name: Update Journal
*/

// Initialise session
session_start();

define('APP_RAN', '');

require_once('config.php');
require_once('content_filters.php');
require_once('Parsedown.php');
require_once('ParsedownExtra.php');

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$year = date('Y', strtotime($date));
$month = date('m', strtotime($date));
$day = date('d', strtotime($date));

$dbdate = $year . '-' . $month . '-' . $day;

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
	    $ID = $_POST['id'];
	    $updatesql = $conn->prepare("UPDATE " . JOURNAL . " SET Content=? WHERE ID=?");
	    $updatesql->bind_param("si", $newcontent, $ID);
	    $updatesql->execute();
	    $updatesql->close();
	    mysqli_refresh($conn, MYSQLI_REFRESH_TABLES);
	    
	    echo json_encode($newcontent);
	    exit();
	}
}

$journal_sql = $connsel->prepare("SELECT ID, Content, Day FROM " . JOURNAL . " WHERE DAY=? ORDER BY ID DESC");
	$journal_sql->bind_param("s", $dbdate);
	$journal_sql->execute();
	$journal_result = mysqli_stmt_get_result($journal_sql);
	while ($row = $journal_result->fetch_assoc()) {
		$ID = $row["ID"];
		$day = $row["Day"];
		$content = $row["Content"];
		$journal_sql->close();
		$raw_content = $content;
		$content = '@@ ' . $content;
		$content = filters($content);
		
		$Parsedown = new ParsedownExtra();
		$content = $Parsedown->text($content);
		
		$content = str_replace('@@', '<a><span style="margin-right: 10px;">#</span></a>', $content);
	}
	
	echo json_encode($content);
	exit();

?>