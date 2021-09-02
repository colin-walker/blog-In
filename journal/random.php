<?php
/*
	Name: Garden
*/

// Initialise session
session_start();

define('APP_RAN', '');

require_once('../config.php');
require_once('../content_filters.php');
require_once('../Parsedown.php');
require_once('../ParsedownExtra.php');

date_default_timezone_set('' . TIMEZONE . '');


// redirect if journal disabled

if (getOption('Journal') == 'no') {
    header("location: " . BASE_URL);
    exit;
}


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


?>

<!DOCTYPE html>
<html lang="en-GB">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#eeeeee">
	<title><?php echo NAME; ?> - random journal entry</title>
	<meta name="description" content="<?php echo constant('DESCRIPTION'); ?>">
	<link rel="stylesheet" href="../style.css" type="text/css" media="all">
	<link defer rel="stylesheet" href="../bigfoot/bigfoot-bottom.css" type="text/css" media="all">
	<script type="text/javascript" src="../script.js"></script>
</head>

<body>
	<div id="journalstreak"><a style="text-decoration: none;" href="/journal/">Today</a></div>
    <div id="page" class="hfeed h-feed site">
        <header id="masthead" class="site-header">
            <div class="site-branding">
                <h1 class="site-title">
                    <a href="<?php echo BASE_URL; ?>" rel="home">
                        <span class="p-name">Random journal entry</span>
                    </a>
                </h1>
            </div>
        </header>


		<div id="primary" class="content-area">
			<main id="main" class="site-main today-container">

<?php

	$post_sql = $connsel->prepare("SELECT Content, Day FROM " . JOURNAL . " ORDER BY RAND() LIMIT 1");
	$post_sql->execute();
	$result = mysqli_stmt_get_result($post_sql);
	
	while ($row = $result->fetch_assoc()) {
		$date = $row['Day'];
  		$content = $row['Content'];
  		$raw_content = $content;
		$content = '@@ ' . $content;
		$content = filters($content);
		
		$Parsedown = new ParsedownExtra();
		$content = $Parsedown->text($content);
		
		$content = str_replace('@@', '<a><span style=" margin-right: 10px;">#</span></a>', $content);
		
		$year = date('Y', strtotime($date));
		$month = date('m', strtotime($date));
		$day = date('d', strtotime($date));
		
		echo '<h3 style="margin-bottom: 20px;" class="dateSpan">' . $day . '/' . $month . '/' . $year . '</h3>';
		
		echo '<article id="post">' . PHP_EOL;		
		echo '<div class="section">' . PHP_EOL;
		echo '<div id="entry" class="entry-content e-content">' . PHP_EOL;
		echo $content . PHP_EOL;
		echo '</div>' . PHP_EOL; // entry-content
		echo '</div>' . PHP_EOL; // section
		echo '</article>' . PHP_EOL;
}  ?>

			</main><!-- #main -->
		</div><!-- #primary -->
	</div><!-- #page -->

	<nav class="navigation paging-navigation">
		<div class="nav-links">

			</div><!-- .nav-links -->
	</nav><!-- .navigation -->

	<div class="linksDiv day-links"><a accesskey="r" class="randomlink" style="text-decoration: none;" title="Random entry" href="random.php">?</a></div>

<?php
	$pageDesktop = "152";
	$pageMobile = "262";
	include('../footer.php');
?>