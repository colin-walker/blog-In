<?php
/*
 * Search
 *
 */

define('APP_RAN', '');

require_once('../config.php');
require_once('../content_filters.php');
require_once('../Parsedown.php');
require_once('../ParsedownExtra.php');

?>

<!DOCTYPE html>
<html lang="en-GB">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo constant('NAME'); ?> - Search</title>
	<meta name="description" content="<?php echo constant('DESCRIPTION'); ?>">
	<link rel="stylesheet" href="../style.css" type="text/css" media="all">
	<link rel="stylesheet" href="../bigfoot/bigfoot-bottom.css" type="text/css" media="all">
	<script type="text/javascript" src="../script.js"></script>
	<link rel="home alternate" type="application/rss+xml" title="<?php echo constant('NAME'); ?> :: Daily Feed" href="<?php echo constant('BASE_URL'); ?>/dailyfeed.rss" />
    <link rel="alternate" type="application/rss+xml" title="<?php echo constant('NAME'); ?> :: Live Feed" href="<?php echo constant('BASE_URL'); ?>/livefeed.rss" />
    <link rel="me" href="mailto:<?php echo constant('MAILTO'); ?>" />
</head>

    <div id="page" class="hfeed h-feed site">
        <header id="masthead" class="site-header">
            <div class="site-branding">
                <h1 class="site-title">
                    <a href="<?php echo BASE_URL; ?>" rel="home">
                        <span class="p-name">Search</span>
                    </a>
                </h1>
            </div>
        </header>

        <div id="primary" class="content-area">
			<main id="main" class="site-main today-container">

<?php

if ( (isset($_POST['s']) && $_POST['s'] != '' ) || (isset($_GET['s']) && $_GET['s'] != '') ) {

	if (isset($_POST['s'])) {
		$query = $connsel->real_escape_string($_POST['s']);
	} else {
		$query = $connsel->real_escape_string($_GET['s']);
	}

    $query = strtolower($query);

	$sql = $connsel->prepare("SELECT ID, Permalink, Section, Content, Date, Draft FROM " . POSTS . " WHERE Content REGEXP '[^[:punct:]][a-zA-Z0-9();,\'\"]?$query' AND Draft='' ORDER BY ID Desc");
	// output data of each row

	$pagenum = htmlspecialchars($_GET["pagenum"]);

	if ($pagenum == '') {
		$pagenum = 1;
	}

	echo '<div class="searchTitle" style="font-weight: bold; font-size: 20px; margin: 20px auto 20px; text-align: center;">Search: <span style="text-transform: uppercase;">' . stripslashes($query) . '</span>';
if($sql){
	$sql->execute();
	$sql->bind_result($db_ID, $db_permalink, $db_section, $db_content, $db_date, $db_draft);
	$result = mysqli_stmt_get_result($sql);
	$rows = mysqli_num_rows($result);

	$page_rows = 5;

	$sql->close();
}


if($rows > 5){
  echo ', page ' . $pagenum . '</div>';
} else {
  echo '</div>';
}
	$last = ceil($rows/$page_rows);
	if ($pagenum < 1) {
		$pagenum = 1;
	} elseif ($pagenum > $last) {
		$pagenum = $last;
	}

	$max = 'limit ' .($pagenum - 1) * $page_rows .',' .$page_rows;

	$sql = $connsel->prepare("SELECT ID, Permalink, Section, Title, Content, Date, Draft, ReplyURL, Reply_Title FROM " . POSTS . " WHERE Content REGEXP '[^[:punct:]][a-zA-Z0-9();,\'\"]?$query' AND Draft='' ORDER BY ID Desc $max");

  if($sql) {
	$sql->execute();
	//$sql->bind_result($db_ID, $db_permalink, $db_section, $db_title, $db_content, $db_date, $db_draft);
	$result = mysqli_stmt_get_result($sql);
  }
	if (mysqli_num_rows($result) != 0) {
  		while($row = $result->fetch_assoc()) {
			$status = $row["Draft"];
    		$ID = $row["ID"];
    		$permalink = $row["Permalink"];
    		$section_number = $row["Section"];
    		$post_title = $row["Title"];
    		$post_time = $row["Date"];
    		$replyURL = $row["ReplyURL"];
    		$reply_title = $row["Reply_Title"];
  			$content = stripslashes($row["Content"]);
  			$raw = $content;
  			
  			$post_array = explode("\n", $content);
    		$size = sizeof($post_array);
			if (substr($post_array[0], 0, 2) == "# ") {
				$length = strlen($post_array[0]);
				$required = $length - 2;
				$post_title = substr($post_array[0], 2, $required);
				$content = '';
				for ($i = 2; $i < $size; $i++) {
					$content .= $post_array[$i];
				}
			}
			
			if($replyURL != '') {
    			$reply_Str = '<p class="replyto"><em>In reply to: <a class="u-in-reply-to" href="' . $replyURL . '">' . $reply_title . '</a>...</em></p>';
		    	$content = substr($content, (strlen($replyURL)+9));
    		} else {
    			$reply_Str = '';
    		}
	
  			$content = filters($content);

			$Parsedown = new ParsedownExtra();
			$content = $Parsedown->text($content);

			$content = substr($content, 3);

			if (substr($content, -3) == '<p>') {
				$content = substr($content, 0, -3);
			}

            $pattern = "/(?<!&|\'|\#|\)|\||\.|\/|\[|-|=|\")(?<=[a-z]|[A-Z]|\(|\s)$query(?![^<]*\>)(?!\/|\"\>)/i";
            $replace = '<span class="result">' . stripslashes($query) . '</span>';
			$content = preg_replace($pattern, $replace, $content);
			
			if ($post_title != '') {
    			$openStr = '<h2 class="p-name postTitle">' . $post_title . '</h2>' . $reply_Str . '<div class="entry-content e-content"><p>';
    		} else {
    			$openStr = $reply_Str;
    		}

			echo '<article class="h-entry"><div class="entry-content e-content">';
			echo '<div id="post' . $ID . '">';
			echo '<div class="section"><a style="float: left;" class="u-url search-u-url" name="p' . $section_number . '" href="' . $permalink . '#p' . $section_number . '" class="postCount">#</a>' . $openStr . $content . "</div>";
			echo '<span style="font-size: 12px; position: relative; top: -5px;">→ <a href="' . $permalink . '#p' . $section_number . '" style="text-decoration: none;">' . date(DATE_META, strtotime($post_time)) . '</a></span>';
			echo '</div><!-- .entry-content --></article>';
		}

		echo "<div class='paging-navigation' style='text-align: center; margin-top: 30px;'>";
		if ($pagenum == 1) {
		} else {
			echo "<a title='First' href='{$_SERVER['PHP_SELF']}?pagenum=1&s=$query'><<</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			$previous = $pagenum-1;
			echo "<a title='Previous' href='{$_SERVER['PHP_SELF']}?pagenum=$previous&s=$query'><</a>";
		}
		if ($pagenum != 1 && $pagenum != $last) {
			echo '<span style="margin: 0 20px;"> </span>';
		}
		if ($pagenum == $last) {
		} else {
			$next = $pagenum+1;
			echo "<a title='Next' href='{$_SERVER['PHP_SELF']}?pagenum=$next&s=$query'>></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			echo "<a title='Last' href='{$_SERVER['PHP_SELF']}?pagenum=$last&s=$query'>>></a>";
		}
		echo '</div>';
		echo '<div class="day-links"><a accesskey="s" href="/search/">New search</a>|<a href="' . BASE_URL . '/wp/?s=' . stripslashes($query) . '&post_type=post">Search archive</a></div>';
	} else { ?>
		<br/>
        <h3>Nothing found.</h3>
        <p class="entry-content" style="padding-bottom: 10px;">
        <a accesskey="a" style="font-family: Helvetica, Arial, sans-serif;" href="<?php echo constant('BASE_URL'); ?>/wp/?s=<?php echo stripslashes($query); ?>&post_type=post">Try the archive</a>?
        </p>
		<div style="text-align: center; margin: 7.4vh auto 0">
		<form role="search" method="post" class="search-form" action="">
			<input type="search" class="search-field" placeholder="Search &hellip;" value="" name="s" autofocus />
			<input accesskey="s" type="submit" class="search-submit" value="Go" style="font-size: 14px; position: relative; top: 0px;" />
		</form>
        </div>
	<?php
	}
} else { ?>
		<div style="margin-top: 30vh; text-align: center;">
			<form role="search" method="post" class="search-form" action="">
				<input type="search" class="search-field" placeholder="Search &hellip;" value="" name="s" autofocus />
				<input accesskey="s" type="submit" class="search-submit" value="Go" style="font-size: 14px; position: relative; top: 0px;" />
			</form>
		</div>

<?php
}

?>

			</main><!-- #main -->
		</div><!-- #primary -->
	</div><!-- #page -->
	
<?php
	$pageDesktop = "102";
	$pageMobile = "147";
	include('../footer.php');
?>