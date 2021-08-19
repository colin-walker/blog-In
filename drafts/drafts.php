<?php
/*
    Name: Blog home
*/

// Initialise session
session_start();

define('APP_RAN', '');

require_once('../config.php');
require_once('../content_filters.php');
require_once('../Parsedown.php');
require_once('../ParsedownExtra.php');

require_once('../MentionClient.php');
require_once('../MentionClientTest.php');


// HTML starts on line 153


date_default_timezone_set('' . TIMEZONE . '');

$post_order = getOption('Post_Order');
$post_titles = getOption('Post_Titles');
$post_meta = getOption('Post_Meta');


// Get auth string from database

$authsql = $connsel->prepare("SELECT Option_Value FROM " . OPTIONS . " WHERE Option_Name = 'Auth' ");
$authsql->execute();
$authresult = mysqli_stmt_get_result($authsql);
$row = $authresult->fetch_assoc();
$dbauth = $row["Option_Value"];
$authsql->close();

if ($_SESSION['auth'] != $dbauth) {
    header("location: " . BASE_URL);
    exit;
}


// Update existing post

if ( isset($_POST['updatepost']) ) {
    if ($_SESSION['auth'] == $dbauth) {
	    $newcontent = $_POST['newcontent'];
	    $PostID = $_POST['updatepost'];
        $status = $_POST['status'];
        if (isset($_POST['title'])) {
        	$title = $_POST['title'];
        } else {
        	$title = '';
        }
        $post_time = date("D, d M Y H:i:s");
        $post_day = date("Y/m/d");

        $draft_check_sql = $connsel->prepare("SELECT Permalink, Section, Title, Draft FROM " . POSTS . " WHERE ID=?");
        $draft_check_sql->bind_param("i", $PostID);
        $draft_check_sql->execute();
        $draft_check_result = mysqli_stmt_get_result($draft_check_sql);
        $row = $draft_check_result->fetch_assoc();
        $isdraft = $row["Draft"];
        $draft_permalink = $row["Permalink"];
        $draft_section = $row["Section"];
        $check_title = $row["Title"];
        $draft_check_sql->close();

        if ($isdraft == 'draft') {
            if ($status == 'publish') {
                $clear = '';
                
                $section_date = date("Y/m/d");
        		$section_check = $connsel->prepare("SELECT ID, Section, Day FROM " . POSTS . " WHERE Day=? AND Draft='' ORDER BY ID DESC LIMIT 1");
        		$section_check->bind_param("s", $section_date);
        		$section_check->execute();
        		$draft_result = mysqli_stmt_get_result($section_check);
        		$row = $draft_result->fetch_assoc();
        		$last_ID = $row["ID"];
        		$last_section = $row["Section"];
        		$last_day = $row["Day"];
        		if ($last_ID == $PostID) {
        			$section = $last_section;
        		} else {
	        		$section = $last_section + 1;
	        	}
        		$section_check->close();
        
                $post_date = date("Y-m-d");
        		$permalink = BASE_URL . '/?date=' . $post_date;
        		$post_time = date("D, d M Y H:i:s");
        		$post_day = date("Y/m/d");
                
        		if (isset($_POST['title']) && $title != $check_title) {
        			$updatesql = $conn->prepare("UPDATE " . POSTS . " SET Permalink=?, Section=?, Title=?, Content=?, Date=?, Day=?, Draft=? WHERE ID=?");
        			$updatesql->bind_param("sisssssi", $permalink, $section, $title, $newcontent, $post_time, $post_day, $clear, $PostID);
        		} else {
        			$updatesql = $conn->prepare("UPDATE " . POSTS . " SET Permalink=?, Section=?, Content=?, Date=?, Day=?, Draft=? WHERE ID=?");
	            	$updatesql->bind_param("sissssi", $permalink, $section, $newcontent, $post_time, $post_day, $clear, $PostID);
	           	}
	            
	            $client = new IndieWeb\MentionClient();

                $Parsedown = new ParsedownExtra();
	            $wmcontent = $Parsedown->text($newcontent);

                $targetURL = '';
                $sourceURL = $draft_permalink . '#p' . $draft_section;

                $urls = $client->findOutgoingLinks($wmcontent);
                foreach ($urls as $url) {
	                $targetURL = $url;
	                $endpoint = $client->discoverWebmentionEndpoint($targetURL);
	                if ($endpoint) {
	                    //echo 'Source: ' . $sourceURL . '<br/>';
	                    //echo 'Target: ' . $targetURL . '<br/>';
	                    //echo $endpoint . '<br/>';
	                    $response = IndieWeb\MentionClient::sendWebmentionToEndpoint($endpoint, $sourceURL, $targetURL);
    	            }
                }     
	        } else {
	        	if (isset($_POST['title']) && $title != $check_title) {
        			$updatesql = $conn->prepare("UPDATE " . POSTS . " SET Title=?, Content=?, Date=?, Day=? WHERE ID=?");
        			$updatesql->bind_param("ssssi", $title, $newcontent, $post_time, $post_day, $PostID);
        		} else {
        			$updatesql = $conn->prepare("UPDATE " . POSTS . " SET Content=?, Date=?, Day=? WHERE ID=?");
	            	$updatesql->bind_param("sssi", $newcontent, $post_time, $post_day, $PostID);
	            }
	        }
        } else {
	        	if (isset($_POST['title']) && $title != $check_title) {
	            	$updatesql = $conn->prepare("UPDATE " . POSTS . " SET Title=?, Content=? WHERE ID=?");
	            	$updatesql->bind_param("ssi", $title, $newcontent, $PostID);
	            } else {
	            	$updatesql = $conn->prepare("UPDATE " . POSTS . " SET Content=? WHERE ID=?");
	            	$updatesql->bind_param("si", $newcontent, $PostID);
	            }
	    }
	    $updatesql->execute();
	    $updatesql->close();

        include '../livefeed.php';
?>
    <script>
    	localStorage.removeItem("newcontent");
    </script>
<?php

	    $draftsql = $connsel->prepare("SELECT * FROM " . POSTS . " WHERE Draft='draft' ");
        $draftsql->execute();
        $draftresult = mysqli_stmt_get_result($draftsql);
        $draftcount = mysqli_num_rows($draftresult);
				
        if($draftcount == 0) {
            header("location: " . BASE_URL);
            exit;
        }
    } else {
    	die("Admin only!");
    }
}


// Delete Posts

if (isset($_POST['deletepost'])) {
    if ($_SESSION['auth'] == $dbauth) {
	    $delete_id = $_POST['deletepost'];
	    $delete_sql = $conn->prepare("DELETE FROM " . POSTS . " WHERE ID=?");
	    $delete_sql->bind_param("i", $delete_id );
	    $delete_sql->execute();
	    $delete_sql->close();

	    include '../livefeed.php';
	    
	    $draftsql = $connsel->prepare("SELECT * FROM " . POSTS . " WHERE Draft='draft' ");
        $draftsql->execute();
        $draftresult = mysqli_stmt_get_result($draftsql);
        $draftcount = mysqli_num_rows($draftresult);
				
        if($draftcount == 0) {
            header("location: " . BASE_URL);
            exit;
        }
    } else {
    	die("Admin only!");
    }
}

?>


<!DOCTYPE html>
<html lang="en-GB">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#eeeeee">
	<title><?php echo NAME; ?> - Draft Posts</title>
	<meta name="description" content="<?php echo constant('DESCRIPTION'); ?>">
	<link rel="icon" type="image/png" sizes="32x32" href="../favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../favicon-16x16.png">
	<link defer rel="stylesheet" href="../bigfoot/bigfoot-bottom.css" type="text/css" media="all">
	<link defer rel="stylesheet" href="../style.css" type="text/css" media="all">
	
	<script type="text/javascript" src="../script.js"></script>

</head>

<body>
    <div id="page" class="hfeed h-feed site">
        <header id="masthead" class="site-header">
            <div class="site-branding">
                <h1 class="site-title">
                    <a href="<?php echo BASE_URL; ?>" rel="home">
                        <span class="p-name">Draft Posts</span>
                    </a>
                </h1>
            </div>
        </header>

		<div id="primary" class="content-area">
			<main id="main" class="site-main today-container">
                <div id="editdiv" class="editdiv" style="height: 0px;">
                </div>
                <br />

<?php

// output data of each row

$sql = $connsel->prepare("SELECT * FROM " . POSTS . " WHERE Draft='draft' ORDER BY ID " . $post_order);
$sql->execute();
$result = mysqli_stmt_get_result($sql);

  while($row = $result->fetch_assoc()) {
	$status = $row["Draft"];
    $ID = $row["ID"];
    $permalink = $row["Permalink"];
    $section_number = $row["Section"];
    $post_title = $row["Title"];
    $post_time = $row["Date"];
  	$content = $row["Content"];
  	$raw = $content;
  	
  	        $post_array = explode("\n", $content);
        $size = sizeof($post_array);
		if (substr($post_array[0], 0, 2) == "# ") {
			$length = strlen($post_array[0]);
			$required = $length - 2;
			$post_title = substr($post_array[0], 2, $required);
			$title_in_body = true;
			$content = '';
			for ($i = 2; $i < $size; $i++) {
				$content .= $post_array[$i];
			}
		}

    $statusStr = '<span style="cursor: pointer;" onclick="toggleEdit(' . $ID . ')" class="statusStr">Draft:&nbsp;</span>';
    
  	$content = filters($content);

	$Parsedown = new ParsedownExtra();
	$content = $Parsedown->text($content);

  	if (substr($content, 0, 3) != '<p>') {
		$content = '<p>' . $content;
	}

	if (substr($content, -3) == '<p>') {
		$content = substr($content, 0, -3);
	}
	
	$content = str_replace('img src', 'img loading="lazy" src', $content);
	
	if ($indent == 'true') {
		$indentSpan = 'style="margin-left: 25px;"';	
	}

  	$permalink = $row['Permalink'];
	$section_number = $row['Section'];
	$content = substr($content, 3);
	if ($_SESSION['auth'] == $dbauth) {
		$editSpan = '<form class="delicon_draft" method="post">' . PHP_EOL;
  		$editSpan .= '<input type="hidden" name="deletepost" value="' . $ID . '">' . PHP_EOL;
 		$editSpan .= '<input onClick="javascript: return confirm(\'Are you sure?\');" type="image" src="/images/red-cross.png" style="width: 16px;">' . PHP_EOL;
		$editSpan .= '</form>' . PHP_EOL;
	    $editSpan .= '<a class="editicon" style="display: block;" onclick="toggleEdit(' . $ID . ')"><picture style="width: 12px; position: relative; top: 1.5px;"><source srcset="/images/edit_dark.png" media="(prefers-color-scheme: dark)"><img  style="width: 12px; position: relative; top: 0.5px;" src="/images/edit_light.png" /></picture></a>' . PHP_EOL;
	} else {
	    $editSpan = '';
    }

    echo '<article id="p' . $section_number . '"class="h-entry hentry" ' . $indentSpan . '>' . PHP_EOL;
    $indentSpan = '';
    if ($post_title != '') {
    	$openStr = '<h2 class="p-name postTitle">' . $post_title . '</h2><div class="entry-content e-content"><p>';
    } else {
    	echo '<h6 class="post_title">' . $dbdate . '#p' . $section_number . '</h6>' . PHP_EOL;
   		$openStr = '<div class="entry-content e-content p-name"><p>';
    }
	echo '<div id="post' . $ID . '">' . PHP_EOL;
	echo '<div class="section">' . PHP_EOL . $editSpan . '<a style="float: left;" class="u-url hash"  href="' . $permalink . '#p' . $section_number . '">#</a>' . PHP_EOL;

	echo $openStr . $content . "\n</div><!-- .entry-content -->" . PHP_EOL;
    
	echo '</div>' . PHP_EOL; // End section
	echo '<time class="dt-published" datetime="' . date("c", strtotime($post_time)) . '"></time>' . PHP_EOL;
	if ($post_order == 'DESC' && getOption('Post_Meta') == 'yes') {
		echo '<span style="font-size: 12px; position: relative; top: -5px;">→ ' . date(DATE_META, strtotime($post_time)) . '</span>';
	}

    echo '</div>' . PHP_EOL;
	echo '<div id="edit' . $ID . '" class="editdivs">' . PHP_EOL;

?>
    <iframe id="edit_upload_frame<?php echo $ID; ?>" loading="lazy" src='/uploader.php' style="display: none; width: 100%; height: 30px; border: none; overflow: hidden;"></iframe>
    <form name="form" method="post">
		<?php if ($post_titles == 'yes') { ?>
    		<input type="text" name="title" class="text" style="max-height: 34px;" value="<?php echo $post_title; ?>">
		<?php } ?>
        <input type="hidden" id="updatepost<?php echo $ID; ?>" name="updatepost" value="<?php echo $ID; ?>">
        <textarea rows="10" id="newcontent<?php echo $ID; ?>" name="newcontent" class="newcontent text"><?php echo $raw; ?></textarea>
        <a onclick="quit(<?php echo $ID; ?>);"><img  style="width: 20px; float: left; position: relative; top: -1px; cursor: pointer;" src="/images/cancel.png" /></a><span style="float: left; padding-left: 15px; font-size: 75%;">Draft: <input type="radio" name="status" value="draft" <?php if($status == 'draft') { echo 'checked="checked"'; }?>>&nbsp;&nbsp;Publish: <input type="radio" name="status" value="publish" <?php if($status != 'draft') { echo 'checked="checked"'; }?>></span>
        <input style="float:right; font-size: 75%" type="submit" name="submit" id="submit<?php echo $ID; ?>" value="Update"><picture style="height: 17px; float: right; position: relative; right: 13px; top: 6px; cursor: pointer;"><source srcset="/images/media_dark.png" media="(prefers-color-scheme: dark)"><img onclick="toggleImage_edit(<?php echo $ID; ?>);" style="height: 17px; float: right; position: relative; cursor: pointer;" src="/images/media_light.png" /></picture>
    </form>
    
    <script>
        // autosave - thanks Jan-Lukas – https://jlelse.blog/dev/form-cache-localstorage
  		
  		var newcontent = document.getElementById("newcontent<?php echo $ID; ?>");
		var cached = localStorage.getItem("newcontent");
		
		if (cached != null) {
			newcontent.value = cached;
		}
		newcontent.addEventListener("input", function () {
			localStorage.setItem("newcontent", newcontent.value);
		})
	</script>
	
<?php
	echo '</div>';
    echo '</article>' . PHP_EOL;
}
$sql->close();
$conn->close();
?>

				<div style="clear: both;"></div>
			</main><!-- #main -->
		</div><!-- #primary -->
	</div><!-- #page -->

	<nav class="navigation paging-navigation">
		<div class="nav-links">
		</div><!-- .nav-links -->
	</nav><!-- .navigation -->

	<div class="linksDiv day-links">
		<a href="<?php echo BASE_URL; ?>">Today</a>
	</div>

    <script>
        fragmention();
    </script>
    
	<script type="text/javascript" src="/jquery.slim.min.js"></script>
	<script type="text/javascript" src="/bigfoot/bigfoot.min.js"></script>
	<script type="text/javascript">
	    var bigfoot = $.bigfoot( {
	        positionContent: true,
	        preventPageScroll: true
	    } );
	</script>
	
	<script>
    	
		var clickable = document.getElementsByClassName("clickable");
		var x = new Array();
    	for (i = 0; i < clickable.length; i++) { 
  			x[i] = clickable[i].innerHTML;
  			clickable[i].innerHTML = x[i] + "<div style='height: 0px; position: relative; width: 50%; margin: 0 auto;'><img src='/images/expand.png' class='overlay noradius'></div>";
  		}

     </script>

<?php
	$pageDesktop = "157";
	$pageMobile = "262";
include('../footer.php');
?>