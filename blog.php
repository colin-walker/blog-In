<?php
/*
    Name: Blog home
*/

// Initialise session
session_start();

define('APP_RAN', '');

require_once('config.php');
require_once('content_filters.php');
require_once('Parsedown.php');
require_once('ParsedownExtra.php');

require_once('MentionClient.php');
require_once('MentionClientTest.php');

require_once('PHPMailer.php');
require_once('SMTP.php');
require_once('Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


// HTML starts on line 415


date_default_timezone_set('' . TIMEZONE . '');

$post_order = getOption('Post_Order');
$post_titles = getOption('Post_Titles');
$post_meta = getOption('Post_Meta');
	    
$moderate_comments = getOption('Moderate_Comments');
if($moderate_comments == 'yes') {
	GLOBAL $moderate;
	$moderate = '1';
} else {
	GLOBAL $moderate;
	$moderate = '0';
}


// Get auth string from database

$authsql = $connsel->prepare("SELECT Option_Value FROM " . OPTIONS . " WHERE Option_Name = 'Auth' ");
$authsql->execute();
$authresult = mysqli_stmt_get_result($authsql);
$row = $authresult->fetch_assoc();
$dbauth = $row["Option_Value"];
$authsql->close();


// Process comments

if (isset($_POST['PostID']) && !isset($_POST['deletecomment']) && !isset($_POST['approvecomment']) && $_POST['check'] == '' && $_POST['email'] == '') {
	    $Parent = $_POST['PostID'];
	    $Name = addslashes($_POST['name']);
	    $Website = addslashes($_POST['website']);
	    $Comment = $_POST['comment'];
	    $InReplyTo = $_POST['InReplyTo'];
	    if ($InReplyTo !='') {
	    	$thread = $Parent;
	        $Parent = 0;    
	    }

	    $comment_sql = $conn->prepare("INSERT INTO " . COMMENTS . " (Parent, Name, Website, Comment, InReplyTo, Moderated) VALUES (?, ?, ?, ?, ?, ?)");
	    $comment_sql->bind_param("isssii", $Parent, $Name, $Website, $Comment, $InReplyTo, $moderate);
	    $comment_sql->execute();
	    $comment_ID = $comment_sql->insert_id;
	    $cid = $comment_ID;
	    $comment_sql->close();

	    $parent_sql = $connsel->prepare("SELECT Permalink, Section FROM " . POSTS . " WHERE ID=?");
	    if ($Parent == 0) {
	      $parent_sql->bind_param("i", $thread);
	    } else {
	      $parent_sql->bind_param("i", $Parent);
	    }
	    $parent_sql->execute();
	    $parent_sql->bind_result($db_permalink, $db_section);
        $parent_result = mysqli_stmt_get_result($parent_sql);
        $row = $parent_result->fetch_assoc();
        $permalink = $row["Permalink"];
        $section = $row["Section"];
        $sid = $section;
        $postlink = $permalink . '&c=' . $section . ':' . $cid; //'#p' . $section;
        $parent_sql->close();

        $body .= '<p>A comment has been received on the blog.</p>';
        $body .= '<p>\'' . $Name . '\' replied to <a href="' . $postlink . '">this post</a> saying:</p>';
        $body .= '<blockquote>' . $Comment . '</blockquote>';
        $body .= '<br/>';
        $body .= '<a href="' . $postlink . '">Check it out.</a>';

	    $mail = new PHPMailer(true);

        try {
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->isSMTP();
            $mail->Host = '' . SMTPHOST . '';
            $mail->SMTPAuth = true;
            $mail->Username = '' . SMTPUSER . '';
            $mail->Password = '' . SMTPPASS . '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = SMTPPORT;

            //Recipients
            $mail->setFrom('' . MAILTO . '', '' . parse_url(BASE_URL)['host'] . '');
            $mail->addAddress('' . MAILTO . '', '' . NAME . '');

            //Content
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);
            $mail->Subject = 'New blog comment';
            $mail->Body = $body;

            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
        
        $parent_sql = $connsel->prepare("SELECT Section FROM " . POSTS . " WHERE ID=?");
	    $parent_sql->bind_param("i", $thread);
	    $parent_sql->execute();
        $parent_result = mysqli_stmt_get_result($parent_sql);
        $row = $parent_result->fetch_assoc();
        $section = $row["Section"];
        $parent_sql->close();
        
        $InReplyTo_sql = $connsel->prepare("SELECT Website FROM " . COMMENTS . " WHERE ID=?");
		$InReplyTo_sql->bind_param("s", $InReplyTo);
		$InReplyTo_sql->execute();
		$InReplyTo_result = mysqli_stmt_get_result($InReplyTo_sql);
	    $row = $InReplyTo_result->fetch_assoc();
	    $parent = $row["Parent"];
	    $targetURL = $row["Website"];
		$InReplyTo_sql->close();

        $sourceURL = BASE_URL . '/reply.php?c=' . $section . ':' . $comment_ID;
                	
    	$client = new IndieWeb\MentionClient();
        $endpoint = $client->discoverWebmentionEndpoint($targetURL);
        if ($endpoint) {
            //echo 'Source: ' . $sourceURL . '<br/>';
            //echo 'Target: ' . $targetURL . '<br/>';
            //echo $endpoint . '<br/>';
            $response = IndieWeb\MentionClient::sendWebmentionToEndpoint($endpoint, $sourceURL, $targetURL);
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

	    include 'livefeed.php';
    } else {
    	die("Admin only!");
    }
}


// Delete Comments

if (isset($_POST['deletecomment'])) {
    if ($_SESSION['auth'] == $dbauth) {
	    $delete_id = $_POST['deletecomment'];
	    $Parent = $_POST['PostID'];
	    $delete_sql = $conn->prepare("DELETE FROM " . COMMENTS . " WHERE ID=?");
	    $delete_sql->bind_param("i", $delete_id );
	    $delete_sql->execute();
	    $delete_sql->close();
    } else {
    	die("Admin only!");
    }
}


// Approve Comment

if (isset($_POST['approvecomment'])) {
    if ($_SESSION['auth'] == $dbauth) {
    	$approve_id = $_POST['approvecomment'];
    	$approvesql = $conn->prepare("UPDATE " . COMMENTS . " SET Moderated=0 WHERE ID=?");
    	$approvesql->bind_param("i", $approve_id );
    	$approvesql->execute();
    	$approvesql->close();
    }
}


// Submit new post

if ( isset($_POST['dopost']) ) {
    if ($_SESSION['auth'] == $dbauth) {
        $section_date = date("Y/m/d");
        $section_check = $connsel->prepare("SELECT Section FROM " . POSTS . " WHERE Day=? ORDER BY ID DESC");
        $section_check->bind_param("s", $section_date);
        $section_check->execute();
        $section_check->bind_result($db_section);
        $draft_result = mysqli_stmt_get_result($section_check);
        $row = $draft_result->fetch_assoc();
        $last_section = $row["Section"];
        $section = $last_section + 1;
        $section_check->close();

        $post_date = date("Y-m-d");
        $permalink = BASE_URL . '?date=' . $post_date;
        $post_time = date("D, d M Y H:i:s");
        $post_day = date("Y/m/d");
        $status = $_POST['status'];
        if (isset($_POST['title'])) {
        	$title = $_POST['title'];
        } else {
        	$title = '';
        }
        $content = $_POST['content'];
        $content = like($content);
        $content = reply($content);
        $wmcontent = $content;

        if ($status == 'publish') {
            $sql = $conn->prepare("INSERT INTO " . POSTS . " (Permalink, Section, Title, Content, Date, Day) VALUES (?, ?, ?, ?, ?, ?)");
            $sql->bind_param("sissss", $permalink, $section, $title, $content, $post_time, $post_day);
        } else {
            $sql = $conn->prepare("INSERT INTO " . POSTS . " (Permalink, Section, Title, Content, Date, Day, Draft) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $sql->bind_param("sisssss", $permalink, $section, $title, $content, $post_time, $post_day, $status);
            $draft = 'true';
        }
        $sql->execute();
        $sql->close();

        include 'livefeed.php';

        if ($draft != 'true') {
            $Parsedown = new ParsedownExtra();
	        $wmcontent = $Parsedown->text($wmcontent);

            $targetURL = '';
            $sourceURL = $permalink . '#p' . $section;
			
			$client = new IndieWeb\MentionClient();
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
        }
    } else {
    	die("Admin only!");
    }
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
                
        		if (isset($_POST['title']) && $title != $check_title) {
        			$updatesql = $conn->prepare("UPDATE " . POSTS . " SET Title=?, Content=?, Date=?, Day=?, Draft=? WHERE ID=?");
        			$updatesql->bind_param("sssssi", $title, $newcontent, $post_time, $post_day, $clear, $PostID);
        		} else {
        			$updatesql = $conn->prepare("UPDATE " . POSTS . " SET Content=?, Date=?, Day=?, Draft=? WHERE ID=?");
	            	$updatesql->bind_param("ssssi", $newcontent, $post_time, $post_day, $clear, $PostID);
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
	            	$updatesql = $conn->prepare("UPDATE " . POSTS . " SET Title=?, Content=? WHERE ID=?");
	            	$updatesql->bind_param("ssi", $title, $newcontent, $PostID);
	            } else {
	            	$updatesql = $conn->prepare("UPDATE " . POSTS . " SET Content=? WHERE ID=?");
	            	$updatesql->bind_param("si", $newcontent, $PostID);
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

        include 'livefeed.php';
    } else {
    	die("Admin only!");
    }
}

// Journal streak

$date = date('Y-m-d');
$check_date = $date;
$step = 0;
$count = 0;

$streak_sql = $connsel->prepare("SELECT Day FROM " . JOURNAL . " ORDER BY ID DESC");
$streak_sql->execute();
$streak_result = mysqli_stmt_get_result($streak_sql);

while($row = $streak_result->fetch_assoc()) {
	$postdate = date('Y-m-d', strtotime($row["Day"]));

    if ($postdate == $date && $step == 0) {
	    $break = '.';
    }

    if ( $postdate != $date && $step == 0) {
	    $none_today = 'true';
	    $check_date = date('Y-m-d', strtotime($check_date .' -1 day'));
	    $step++;
    } else {
	    $step++;
    }

    if ( $postdate == $check_date && $step > 0 ) {
	    $count++;
	    $check_date = date('Y-m-d', strtotime($check_date .' -1 day'));
    }  else {
	    if ($break == '') {
		    $break = '.';
	    }
	    break;
    }
}

if ($none_today == 'true') {
    $break = '<sup>*</sup>';
}

if ($break == '.') {
  $break = '';
}	

if ($_SESSION['auth'] == $dbauth) {
	if (getOption('Journal') == 'yes') {
		$streakStr = '<div id="streak"><a style="text-decoration: none;" href="/journal/">Streak: ' . $count . $break . '</a></div>' . PHP_EOL;
	} else {
		$streakStr = '';
	}
} else {
    $streakStr = '<div id="streak" class="blogin"><a style="text-decoration: none;" href="/login/?return=blog">(b)log-<picture style="width: 7px; position: relative; top: 1.5px;" class="insertimg"><source srcset="/images/blogin_dark.png" media="(prefers-color-scheme: dark)"><img alt="insert image" style="width: 7px; position: relative; top: 1.5px;" id="insertimg" class="insertimg" src="/images/blogin_light.png" /></picture>n</a></div>' . PHP_EOL;
}
?>



<!DOCTYPE html>
<html lang="en-GB">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#eeeeee">
	<title><?php echo constant('NAME'); ?> — <?php echo isset($_GET['date']) ? date('M j, Y', strtotime($_GET['date'])) : date('M j, Y'); ?></title>
	<meta name="description" content="<?php echo constant('DESCRIPTION'); ?>">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link defer rel="stylesheet" href="/bigfoot/bigfoot-bottom.css" type="text/css" media="all">
	<link defer rel="stylesheet" href="/style.css" type="text/css" media="all">
	<link rel="webmention" href="<?php echo constant('BASE_URL'); ?>/endpoint.php"/>
	<link rel="http://webmention.org/" href="<?php echo constant('BASE_URL'); ?>/endpoint.php"/>	
    	<link rel="home alternate" type="application/rss+xml" title="<?php echo constant('NAME'); ?> :: Daily Feed" href="<?php echo constant('BASE_URL'); ?>/dailyfeed.rss" />
    	<link rel="alternate" type="application/rss+xml" title="<?php echo constant('NAME'); ?> :: Live Feed" href="<?php echo constant('BASE_URL'); ?>/livefeed.rss" />
    	<link rel="me" href="mailto:<?php echo constant('MAILTO'); ?>" />
	
	<script type="text/javascript" src="/script.js"></script>

</head>

<body>
<?php echo $streakStr; ?>
    <div id="page" class="hfeed h-feed site">
        <header id="masthead" class="site-header">
            <div class="site-branding">
                <h1 class="site-title">
                    <a href="<?php echo BASE_URL; ?>" rel="home">
                        <span class="p-name"><?php echo constant("NAME"); ?></span>
                    </a>
                </h1>
            </div>
        </header>
        
<!-- Display toggle -->

<?php if ($_SESSION['auth'] == $dbauth) { ?>
		
        <a class="admin" accesskey="a" href="/admin/"><picture style="
            position: fixed;
            bottom: 45px;
            right: 22px;
            cursor: pointer;
            color: #333;
            z-index: 100;
            width: 20px;">
            <source srcset="/images/admin_dark.png" media="(prefers-color-scheme: dark)">
            <img style="
                position: fixed;
                cursor: pointer;
                color: #333;
                z-index: 100;
                width: 20px;" src="/images/admin_light.png" />
            </picture>
        </a>
        
        <a id="toggle" tabindex="1" class="toggle" onclick="toggleForm()" accesskey="e"><picture style="
            position: fixed;
            right: 22px;
            cursor: pointer;
            color: #333;
            z-index: 100;
            width: 23px;">
            <source srcset="/images/add_dark.png" media="(prefers-color-scheme: dark)">
            <img style="
                position: fixed;
                right: 22px;
                cursor: pointer;
                color: #333;
                z-index: 100;
                width: 23px;" src="/images/add_light.png" />
            </picture>
        </a>
        <a id="cancel" class="cancel" onclick="toggleForm()" accesskey="e"><img  style="
            position: fixed;
            right: 22px;
            font-size: 23px;
            cursor: pointer;
            color: #333;
            z-index: 100;
            width: 23px;" src="/images/cancel.png" />
        </a>
<?php } ?>

		<div id="primary" class="content-area">
			<main id="main" class="site-main today-container">

<?php

$now = date("Y-m-d H:i:s");
$today = date('Y-m-d');

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$prev_date = date('Y-m-d', strtotime($date .' -1 day'));
$next_date = date('Y-m-d', strtotime($date .' +1 day'));

$year = date('Y', strtotime($date));
$month = date('m', strtotime($date));
$day = date('d', strtotime($date));

$dbdate = $year . '/' . $month . '/' . $day;

if ($_SESSION['auth'] == $dbauth) {
    $sql = $connsel->prepare("SELECT Date FROM " . POSTS . " WHERE Day=? ORDER BY ID Desc");
    $sql->bind_param("s", $dbdate);
} else {
    $sql = $connsel->prepare("SELECT Date FROM " . POSTS . " WHERE Day=? AND Draft='' ORDER BY ID Desc");
    $sql->bind_param("s", $dbdate);
}

$sql->execute();
$sql->bind_result($db_date);
$result = mysqli_stmt_get_result($sql);
$rowcount=mysqli_num_rows($result);

$row = $result->fetch_assoc();
$lastdate = $row["Date"];
$then = strtotime($lastdate);
$last = date('g:ia', strtotime($lastdate));
$then_dt = new DateTime($lastdate);
$now_dt = new DateTime($now);
$diff_h = $then_dt->diff($now_dt)->format("%h");
$diff_m = $then_dt->diff($now_dt)->format("%i");

if ($diff_h == '1') {
    $diffh = $then_dt->diff($now_dt)->format("%h hr");
} else if($diff_h != '0') {
    $diffh = $then_dt->diff($now_dt)->format("%h hrs");
} else {
    $diffh = '';
}

if ($diff_m == '1') {
    $diffm = $then_dt->diff($now_dt)->format("%i min");
} else if($diff_m != '0') {
    $diffm = $then_dt->diff($now_dt)->format("%i mins");
} else if($diff_m == '0' && $diff_h == '0') {
    $diffm = '0 mins';
} else {
    $diffm = '';
}

if ($result->num_rows > 0) {
    if ($date == $today) {
 ?>
				<h2><span class="dateSpan">Today</span><?php //: <span class="daySpan"><?php echo date(DAY_SLASH); . '</span></span>' ?>

<?php
	    if($rowcount > 1) {
	    	if ($post_order == 'ASC') {
	    		$direction = 'down';
	    	} else {
	    		$direction = 'up';
	    	}
?>
		<a href="#p<?php echo $rowcount; ?>"><span class="today-count"><?php echo $rowcount; ?><i class="arrow <?php echo $direction; ?>"></i></span></a>
<?php
	    }
        $toss = rand(0,1);
        if ( $toss == 1 ) {
	        echo '<br/><span class="updatedSpan">(Updated: ' . $last . ')</span>';
	    } else {
	       echo '<br/><span class="updatedSpan">(Updated ' . $diffh . ' ' . $diffm . ' ago)</span>';
	    }
    } else {
?>
				<h2><span class="dateSpan"><span class="daySpan"><?php echo $day . '/' . $month . '/' . $year; ?></span></span>
<?php } ?>
				</h2>
				
				<noscript>
				  <input id="dismiss" type="checkbox" checked><div class="warning">
				    Javascript appears to be disabled or not supported, this means that you will not be able to read or leave comments.<label for="dismiss">X</label>
				    </div>
				</noscript>

<?php

if ($_SESSION['auth'] == $dbauth) { ?>

<div id="editdiv" class="editdiv" style="height: 0px;">
    <iframe id="upload_frame" loading="lazy" src='/uploader.php' style="display: none; width: 100%; height: 30px; border: none; overflow: hidden;"></iframe>
    <form name="form" method="post">
		<?php if ($post_titles == 'yes') { ?>
    		<input type="text" name="title" class="text" style="max-height: 34px;" placeholder="Title">
		<?php } ?>
        <input type="hidden" id="dopost" name="dopost" value="true">
        <textarea rows="10" id="content" name="content" class="text"></textarea>
        <input style="float:right; font-size: 75%" type="submit" name="submit" id="submit" value="Post"><a accesskey="c" onclick="hideForm();"><img  style="width: 20px; float:left; position: relative; top: -1px; cursor: pointer;" src="/images/cancel.png" /></a><span style="float: left; padding-left: 15px; font-size: 75%;">Draft: <input type="radio" name="status" value="draft">&nbsp;&nbsp;Publish: <input type="radio" name="status" value="publish" checked="checked"></span><picture style="height: 17px; float: right; position: relative; right: 13px; top: 6px; cursor: pointer;"><source srcset="/images/image_dark.png" media="(prefers-color-scheme: dark)"><img onclick="toggleImage();" style="height: 17px; float: right; position: relative; cursor: pointer;" src="/images/image_light.png" /></picture>
    </form>
</div>

<?php } ?>

<?php

if ($_SESSION['auth'] == $dbauth) {

$sql = $connsel->prepare("SELECT ID, Permalink, Section, Title, Content, Date, Draft FROM " . POSTS . " WHERE Day=? ORDER BY ID " . $post_order);
$sql->bind_param("s", $dbdate);
} else {
$sql = $connsel->prepare("SELECT ID, Permalink, Section, Title, Content, Date, Draft FROM " . POSTS . " WHERE Day=? AND Draft='' ORDER BY ID " . $post_order);
$sql->bind_param("s", $dbdate);
}
  // output data of each row

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

    if($status == 'draft') {
        $statusStr = '<span style="cursor: pointer;" onclick="toggleEdit(' . $ID . ')" class="statusStr">Draft:&nbsp;</span>';
    } else {
        $statusStr = '';   
    }

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
		$editSpan = '<form class="delicon" method="post">' . PHP_EOL;
  		$editSpan .= '<input type="hidden" name="deletepost" value="' . $ID . '">' . PHP_EOL;
 		$editSpan .= '<input onClick="javascript: return confirm(\'Are you sure?\');" type="image" src="/images/red-cross.png" style="width: 16px;">' . PHP_EOL;
		$editSpan .= '</form>' . PHP_EOL;
	    $editSpan .= '<a class="editicon" onclick="toggleEdit(' . $ID . ')"><picture style="width: 12px; position: relative; top: 0.5px;"><source srcset="/images/edit_dark.png" media="(prefers-color-scheme: dark)"><img  style="width: 12px; position: relative; top: 0.5px;" src="/images/edit_light.png" /></picture></a>' . PHP_EOL;
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

    $numrows = 0;
	$fetch_comment_sql = $connsel->prepare("SELECT ID FROM " . COMMENTS . " WHERE Parent=? ORDER BY ID ASC");
    $fetch_comment_sql->bind_param("i", $ID);
    $fetch_comment_sql->execute();
    $fetch_comment_sql->bind_result($db_ID);
    $comment_result = mysqli_stmt_get_result($fetch_comment_sql);
	while($row = $comment_result->fetch_assoc()) {
		$first = $row["ID"];
		$replies_sql = $connsel->prepare("SELECT ID FROM " . COMMENTS . " WHERE InReplyTo=? ORDER BY ID ASC");
		$replies_sql->bind_param("i", $first);
		$replies_sql->execute();
		$replies_result = mysqli_stmt_get_result($replies_sql);
		while($reply = $replies_result->fetch_assoc()) {
			$numrows++;	
		}
		$replies_sql->close();
	    $numrows++;
	}

    if($numrows > 0) {
    	if ($numrows == 1) {
    		$numstring = ' comment';
    	} else {
    		$numstring = ' comments';
    	}
        echo '<a onclick="toggleComments(' . $ID . ')" title="' . $numrows . $numstring . ': click to read or leave your own" class="toggleComments"><picture class="commenticonpicture"><source srcset="/images/hascommentdark.png" media="(prefers-color-scheme: dark)"><img id="commenticon' . $ID . '" class="commenticon" src="/images/hascomment.png" alt="Click to read or leave comments"></picture></a>' . $statusStr . $openStr . $content . "\n</div>" . PHP_EOL;
    } else {
        echo '<a onclick="toggleComments(' . $ID . ')" title="Click to read or leave comments" class="toggleComments"><picture class="commenticonpicture"><source srcset="/images/commentdark.png" media="(prefers-color-scheme: dark)"><img id="commenticon' . $ID . '" class="commenticon" src="/images/comment.png" alt="Click to read or leave comments"></picture></a>' . PHP_EOL . $statusStr . $openStr . $content . "\n</div><!-- .entry-content -->" . PHP_EOL;
    }
    $fetch_comment_sql->close();
	echo '</div>' . PHP_EOL; // End section
	echo '<time class="dt-published" datetime="' . date("c", strtotime($post_time)) . '"></time>' . PHP_EOL;
	if ($post_order == 'DESC' && getOption('Post_Meta') == 'yes') {
		echo '<span style="font-size: 12px; position: relative; top: -5px;">→ ' . date(DATE_META, strtotime($post_time)) . '</span>';
	}
?>
    <div class="h-card p-author vcard author">
        <img class="u-photo" src="<?php echo constant('AVATAR'); ?>" alt="<?php echo constant('NAME'); ?>"/>
        <a class="u-url" href="<?php echo constant('BASE_URL'); ?>"><span class="p-name"><?php echo constant('NAME'); ?></span></a>
    </div>
<?php
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
        <input style="float:right; font-size: 75%" type="submit" name="submit" id="submit<?php echo $ID; ?>" value="Update"><picture style="height: 17px; float: right; position: relative; right: 13px; top: 6px; cursor: pointer;"><source srcset="/images/image_dark.png" media="(prefers-color-scheme: dark)"><img onclick="toggleImage_edit(<?php echo $ID; ?>);" style="height: 17px; float: right; position: relative; cursor: pointer;" src="/images/image_light.png" /></picture>
    </form>
<?php
	echo '</div>';

/* Comments */

	echo '<div id="replies' . $ID . '" class="replies" style="height: 0px;">';
	echo '<div id="comment_section_' . $section_number . '">';

	$fetch_comment_sql = $connsel->prepare("SELECT ID, Name, Photo, Website, Comment, Mention, isLike, isReply, Fragmention, Moderated FROM " . COMMENTS . " WHERE Parent=? ORDER BY ID ASC");
	$fetch_comment_sql->bind_param("i", $ID);
	$fetch_comment_sql->execute();
	$comment_result = mysqli_stmt_get_result($fetch_comment_sql);
	while($row = $comment_result->fetch_assoc()) {
	    $CommentID = $row["ID"];
		$name = stripslashes($row["Name"]);
		$photo = stripslashes($row["Photo"]);
		$website = stripslashes($row["Website"]);
		$mention = stripslashes($row["Mention"]);
		$like = stripslashes($row["isLike"]);
		$reply = stripslashes($row["isReply"]);
		$sourceURL = $website;
		$fragmention = $row["Fragmention"];
		$moderated = $row["Moderated"];
		
		$comment = stripslashes($row["Comment"]);
		$Parsedown = new Parsedown();
	    $comment = $Parsedown->text($comment);

	    if (!$photo && $website) {
            $photo = 'https://icons.duckduckgo.com/ip2/' . parse_url($website)['host'] . '.ico';
	    }

	    $photoStr = '';
        if ($photo) {
            $photoStr .= '<object class="avatar" data="' . $photo . '" type="image/png"><img class="avatar" src="/images/avatar.png"/></object>';
        } else {
            $photoStr .= '<div class="photo-box"><div class="box-content"><div><span>' . $name[0] . '</span></div></div></div>';
        }


        if ($_SESSION['auth'] == $dbauth) {
        	if ($moderated == '1' && $moderate_comments == 'yes') {
        		$delSpan  = '<form class="commentDelForm" id="approve_form' . $CommentID . '" method="post" style="right: 25px; top: 0px;">';
        		$delSpan .= '<input type="hidden" name="PostID" value="' . $ID . '">';
            	$delSpan .= '<input type="hidden" name="approvecomment" value="' . $CommentID . '">';
            	$delSpan .= '<div style="clear: both;"></div>';
				$delSpan .= '<span title="Approve comment" onClick="javascript: document.getElementById(\'approve_form' . $CommentID . '\').submit();" style="font-size: 13px; cursor: pointer;">✅</span>';
        		$delSpan .= '</form>';
        	} else {
        		$delSpan = '';
        	}
            $delSpan .= '<form class="commentDelForm" method="post">';
            $delSpan .= '<input type="hidden" name="PostID" value="' . $ID . '">';
            $delSpan .= '<input type="hidden" name="deletecomment" value="' . $CommentID . '">';
            $delSpan .= '<div style="clear: both;"></div>';
            $delSpan .= '<input title= "Delete comment" onClick="javascript: return confirm(\'Are you sure?\');" type="image" src="/images/red-cross.png" style="width: 16px;">';
            $delSpan .= '</form>';
	    } else {
	        $delSpan = '';
        }
        

if ($indent == 'true') {
	$indentComment = 'indentComment';
	$indentReply = 'indentReply';
}        
        
if ($moderated != '1' || $_SESSION['auth'] == $dbauth) {        
       
        if($mention != 'true' || $reply) {
		    if ($website != '') {
			    echo $photoStr;
			    echo '<div class="comment ' . $indentComment . '" id="p' . $section_number . $CommentID . '">';
			    echo $delSpan;
			    echo '<span class="commentNameSpan"><a class="website_link" href="' . $website . '">' . $name . '</a> says:</span><picture style="cursor: pointer; margin-left: 20px; width:15px; position: relative; top: 3px;"><source srcset="/images/doreplydark.png" media="(prefers-color-scheme: dark)"><img alt="Reply to ' . $name . '" title="Reply to ' . $name . '" style="width: 15px; cursor: pointer;" src="/images/doreply.png" onclick="setInReplyTo(\'' . $ID . ',' . $CommentID . ',' . $name . '\')" /></picture><br/>';
			    echo '<span class="comment_body e-content">' . $comment . '</span></div>';
			    echo '<div style="clear: both;"></div>';
		    } else {
			    echo $photoStr;
			    echo '<div class="comment" id="p' . $section_number . $CommentID . '">';
			    echo $delSpan;
			    echo '<span class="commentNameSpan">' . $name . ' says:</span><picture style="cursor: pointer; margin-left: 20px; width:15px; position: relative; top: 3px;"><source srcset="/images/doreplydark.png" media="(prefers-color-scheme: dark)"><img title="Reply to this comment" style="width: 15px; cursor: pointer;" src="/images/doreply.png" onclick="setInReplyTo(\'' . $ID . ',' . $CommentID . ',' . $name . '\')" /></picture><br/>';
			    echo '<span class="comment_body e-content">' . $comment . '</div>';
			    echo '<div style="clear: both;"></div>';
		    }
		} else {
		    if ($like) {
                $likeStr .= $photoStr;
		        $likeStr .= '<div class="comment">';
                $likeStr .= $delSpan;
                $likeStr .= '<span class="commentNameSpan"><a class="website_link" href="' . $website . '">' . $name . '</a> liked this.</span></div>';
                $likeStr .= '<div style="clear: both;"></div>';
		    } else if ($reply) {
		        $replyStr .= $photoStr;
		        $replyStr .= '<div class="comment h-as-reply u-comment h-cite" id="' . $section_number . $CommentID . '">';
                $replyStr .= $delSpan;
                $replyStr .= '<span class="commentNameSpan h-card p-author comment-author"><a class="website_link u-url" href="' . $website . '"><span class="p-name">' . $name . '</span></a> replied:</span><picture style="cursor: pointer; margin-left: 20px; width:15px; position: relative; top: 3px;"><source srcset="/images/doreplydark.png" media="(prefers-color-scheme: dark)"><img title="Reply to this comment" style="width: 15px; cursor: pointer;" src="/images/doreply.png" onclick="setInReplyTo(\'' . $ID . ',' . $CommentID . ',' . $name . '\')" /></picture>';
                $replyStr .= '<span class="comment_body e-content">' . $comment . '</span></div>';
                $replyStr .= '<div style="clear: both;"></div>';
		    } else if (strpos($website, parse_url(BASE_URL)['host'])) {
		        $related .= '<div class="related comment" style="margin-bottom: 5px;">';
		        $related .= $delSpan;
		        $related .= '<span class="commentNameSpan"><a class="website_link" style="text-decoration: none; color: #1b597b;" href="' . $website . '">> Related post</a></span></div>';
		        $related .= '<div style="clear: both; margin-bottom: 20px;"></div>';
		        ?>
		        <script>backlink("<?php echo $sourceURL;?>","<?php echo $fragmention;?>");</script>
		        <?php
		    } else {
                $mentionStr .= $photoStr;
		        $mentionStr .= '<div class="comment">';
                $mentionStr .= $delSpan;
                $mentionStr .= '<span class="commentNameSpan"><a class="website_link" href="' . $website . '">' . $name . '</a> mentioned this post.</span></div>';
                $mentionStr .= '<div style="clear: both;"></div>';
            }
		}
		
		$fetch_replies_sql = $connsel->prepare("SELECT ID, Name, Photo, Website, Comment, Mention, isLike, isReply, Fragmention, Moderated FROM " . COMMENTS . " WHERE  InReplyTo=? ORDER BY ID ASC");
	    $fetch_replies_sql->bind_param("i", $CommentID);
	    $fetch_replies_sql->execute();
	    $replies_result = mysqli_stmt_get_result($fetch_replies_sql);
	    while($row = $replies_result->fetch_assoc()) {
	        $CommentID = $row["ID"];
			$name = stripslashes($row["Name"]);
			$photo = stripslashes($row["Photo"]);
			$website = stripslashes($row["Website"]);
	        $reply_comment = stripslashes($row["Comment"]);
	        $moderated = $row["Moderated"];
	        
	        $Parsedown = new Parsedown();
	    	$reply_comment = $Parsedown->text($reply_comment);
	        
    	    if (!$photo && $website) {
	            $photo = 'https://icons.duckduckgo.com/ip2/' . parse_url($website)['host'] . '.ico';                
		    }

		    $photoStr = '';
	        if ($photo) {
	            $photoStr .= '<object class="avatar" data="' . $photo . '" type="image/png" style="position: relative; left: 15px;"><img class="avatar" src="/images/avatar.png"/></object>';
	        } else {
	            $photoStr .= '<div class="photo-box" style="position: relative; left: 15px;"><div class="box-content"><div><span>' . $name[0] . '</span></div></div></div>';
	        }
	        
            if ($_SESSION['auth'] == $dbauth) {
        		if ($moderated == '1' && $moderate_comments == 'yes') {
        			$delSpan  = '<form class="commentDelForm" id="approve_form' . $CommentID . '" method="post" style="right: 45px; top: 0px;">';
	        		$delSpan .= '<input type="hidden" name="PostID" value="' . $ID . '">';
    	        	$delSpan .= '<input type="hidden" name="approvecomment" value="' . $CommentID . '">';
        	    	$delSpan .= '<div style="clear: both;"></div>';
					$delSpan .= '<span title="Approve comment" onClick="javascript: document.getElementById(\'approve_form' . $CommentID . '\').submit();" style="font-size: 13px; cursor: pointer;">✅</span>';
	        		$delSpan .= '</form>';
    	    	} else {
        			$delSpan = '';
        		}
	            $delSpan .= '<form class="commentDelForm" method="post" style="margin-right: -5px;">';
	            $delSpan .= '<input type="hidden" name="PostID" value="' . $ID . '">';
	            $delSpan .= '<input type="hidden" name="deletecomment" value="' . $CommentID . '">';
	            $delSpan .= '<div style="clear: both;"></div>';
	            $delSpan .= '<input title="Delete comment" onClick="javascript: return confirm(\'Are you sure?\');" type="image" src="/images/red-cross.png" style="width: 16px;">';
	            $delSpan .= '</form>';
		    } else {
		        $delSpan = '';
	        }
	        
	        if ($moderated != '1' || $_SESSION['auth'] == $dbauth) { 
        	
        	echo $photoStr;
	        echo '<div class="nested comment ' . $indentReply . '" style="position: relative; left: 20px;" id="p' . $section_number . $CommentID . '">';
	        echo $delSpan;
	        echo '<span class="commentNameSpan h-card p-author comment-author"><span class="p-name">' . $name . '</span></a> replied:</span><br />';
	        echo '<span class="comment_body e-content">' . $reply_comment . '</div>';
			echo '<div style="clear: both;"></div>';
			}
	    }
	    $fetch_replies_sql->close();	
	}	
}

    if ($mentionStr || $likeStr || $related) {
	    echo '<div class="mentionsDiv"></div>';
	}

	echo $replyStr;
	echo $mentionStr;
	echo $likeStr;
    echo $related;

    $replyStr = $mentionStr = $likeStr = $related = '';

	echo '</div><!-- #comments -->';

	echo '<div id="leave_reply' . $ID . '" class="leave_reply">Leave a reply</div>';

	if ($_SESSION['auth'] == $dbauth) {
	    $comment_name = NAME;
	    $comment_website = BASE_URL;
	}
?>

	    <form method="post" name="comments" style="margin-bottom: 60px;">
		    <input type="hidden" name="PostID" value="<?php echo $ID; ?>">
		    <input type="hidden" name="InReplyTo" id="InReplyTo<?php echo $ID; ?>" value="">

		    <input type="text" id="check<?php echo $ID; ?>" name="check" value="" style="display: none;">
		    <input type="email" name="email" value="" placeholder="Email" style="visibility: hidden; height: 0px;"><br/>

		    <input id="name<?php echo $ID; ?>" type="text" name="name" value="<?php echo $comment_name; ?>" placeholder="Name *" size="30" style="font-family: Helvetica, Arial, sans-serif; font-size: 15px; margin-bottom: 10px; padding: 5px 7px; color: #777; border: 1px solid #ccc; border-radius: 5px;" required><br/>
		    <input type="url" name="website" value="<?php echo $comment_website; ?>" placeholder="Website" size="30" style="font-family: Helvetica, Arial, sans-serif; font-size: 15px; margin-bottom: 10px; padding: 5px 7px; color: #777; border: 1px solid #ccc; border-radius: 5px;"><br/>
		    <textarea id="comment_text<?php echo $ID; ?>" name="comment" rows="7" class="comment_text" style="color: #777; border: 1px solid #ccc; border-radius: 5px; font-family: Helvetica, Arial, sans-serif; font-size: 15px; margin-bottom: 5px; padding: 6px 7px;" required <?php if($moderate_comments == 'yes') {?>placeholder="Comments are moderated"<?php } else {?>placeholder="Comment *"<?php } ?>></textarea>
		    <div style="clear: both;"></div>
		    <a onclick="toggleComments(<?php echo $ID; ?>)" title="Cancel comment"><img src="/images/red-cross.png" alt="Cancel comment" style="float: left; width: 20px; cursor: pointer; margin-top: 2px;" /></a>
		    <input style="float:right; margin-top: 2px; margin-right: 16px;" type="submit" name="submit" id="comment_submit<?php echo $ID; ?>" value="Comment">
    	</form>
	</div><!-- #replies -->

<?php
    echo '</article>' . PHP_EOL;
  }
} else {
	$noposts = 'true';
	if (isset($_GET['date'])) {
        $thisDate = date(DAY_SLASH, strtotime($_GET['date']));
        echo '<h3 class="pagetitle"><span class="dateSpan">No posts for: ' . $thisDate . '</span>';
    } else {
        echo '<h3 class="pagetitle"><span class="dateSpan">No posts yet today</span>'; //: ' . date(DAY_SLASH) . '</span>';
    }
/*
    if (date('N') >= 6) {
        echo '<br/><span class="weekend">I will often not post on weekends</span>';
    }
*/
	echo '</h3>';

if ($_SESSION['auth'] == $dbauth) { ?>

    <div id="editdiv" class="editdiv_no_posts" style="display: block; margin-top: 10px; margin-bottom: 50px;">
        <iframe id="upload_frame" loading="lazy" src='/uploader.php' style="display: none; width: 100%; height: 30px; border: none; overflow: hidden;"></iframe>
        <form name="form" method="post">
			<?php if ($post_titles == 'yes') { ?>
    			<input type="text" name="title" class="text" style="max-height: 34px;" placeholder="Title">
			<?php } ?>
            <input type="hidden" id="dopost" name="dopost" value="true">
            <textarea rows="15" id="content" name="content" class="text"></textarea>
            <span style="float: left; padding-left: 15px; font-size: 75%;">Draft: <input type="radio" name="status" value="draft">&nbsp;&nbsp;Publish: <input type="radio" name="status" value="publish" checked="checked"></span><input style="float:right; font-size: 75%" type="submit" name="submit" id="submit_no_posts" value="Post"><picture style="height: 17px; float: right; position: relative; right: 20px; top: 6px; cursor: pointer;"><source srcset="/images/image_dark.png" media="(prefers-color-scheme: dark)"><img onclick="toggleImage();" style="height: 17px; float: right; position: relative; cursor: pointer;" src="/images/image_light.png" /></picture>
        </form>
    </div>

    <script>
        document.getElementById('toggle').style.display = 'none';
    </script>

<?php
    }
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

<?php
if ($date != INSTALL_DATE) {
echo '<div class="nav-previous"><a href="' . BASE_URL . '?date=' . $prev_date . '"><<</a></div>';
}
if ($date != date('Y-m-d')) {
	if ($date == date('Y-m-d', strtotime($today .' -1 day'))) {
		echo '<div class="nav-next"><a href="' . BASE_URL . '">>></a></div>';
	} else {
		echo '<div class="nav-next"><a href="' . BASE_URL . '?date=' . $next_date . '">>></a></div>';
	}
}
?>

			</div><!-- .nav-links -->
	</nav><!-- .navigation -->

	<div class="linksDiv day-links"><a href="<?php echo BASE_URL; ?>">Today</a>|<a accesskey="s" style="text-decoration: none;" title="Search" href="/search/"><picture class="searchicon"><source srcset="/images/search_dark.png" media="(prefers-color-scheme: dark)"><img class="searchicon" src="/images/search_light.png" alt="Search the blog"></picture></a>|<a href="/joinme/" title="Subscribe to regular & daily RSS feeds">Join me</a>
	</div>

    <script>
        var PostID = '<?php echo $Parent; ?>';
        if(PostID != '') {
            toggleComments(PostID);
        }

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
	    var hash;
	    var id;
	    var replies;
	    var repliesArray;
	    var section;
	    var sectionArray;
	    var trueHeight;
	    
	    hash = window.location.hash;
	    if (hash != '') {
	        hash = hash.slice(2);
	        id = "p" + hash;
	        replies = document.getElementById(id).getElementsByClassName('replies')[0];
	        replies.style.transition = "all 0s";
	        
            trueHeight = replies.scrollHeight+30;
            replies.style.height = trueHeight + "px";
            replies.style.marginTop = "30px";
            replies.style.marginBottom = "30px";
            replies.style.padding = "20px 15px 0px";
            section = document.getElementById(id);            
	        var top = section.offsetTop;
	        window.scrollTo(0, top-50);
	    }
        
	    $(window).bind('hashchange', function() {
	        hash = window.location.hash;
	        hash = hash.slice(2);
	        id = "p" + hash;
	        repliesArray = document.getElementsByClassName("replies");  
	        for (var i = 0; i < repliesArray.length; i++) {
                repliesArray[i].style.height = "0px";
                repliesArray[i].style.marginTop = "0px";
                repliesArray[i].style.marginBottom = "0px";
                repliesArray[i].style.padding = "0px";
            }
	        replies = document.getElementById(id).getElementsByClassName('replies')[0];
	        replies.style.transition = "all 0s";
            trueHeight = replies.scrollHeight;
            replies.style.height = trueHeight + "px";
            replies.style.marginTop = "30px";
            replies.style.marginBottom = "30px";
            replies.style.padding = "20px 15px 0px";
            id = "p" + hash;
            section = document.getElementById(id);            
	        var top = section.offsetTop;
	        window.scrollTo(0, top-50);
            
	    });
	    
	    queryString = window.location.search;
	    urlParams = new URLSearchParams(queryString);
	    comment = urlParams.get("c");
	    if (comment != null) {
	        scArray = comment.split(":");
	        section = scArray[0];
	        comment_no = scArray[1];
	        cid = "p" + section + comment_no;
	        id = "p" + section;
	        replies = document.getElementById(id).getElementsByClassName('replies')[0];

            replies.style.transition = "all 0s";
            trueHeight = replies.scrollHeight;
            replies.style.height = trueHeight + "px";
            replies.style.marginTop = "30px";
            replies.style.marginBottom = "30px";
            replies.style.padding = "20px 15px 0px";
    
            section_no = "comment_section_"+section;
            if (document.getElementById(cid)) {
	            commentid = document.getElementById(cid);
	        } else {
	        	commentArray = document.getElementById(section_no).querySelectorAll(".comment");
	            commentid = commentArray[comment_no-1];
	        }
	        console.log(commentid.classList.value);
            commentid.classList.add("linkedComment");
            rect = commentid.getBoundingClientRect();
    	    recttop = rect.top;
    	    window.scrollTo(0, recttop-100);
	    }
	    
	    function setInReplyTo(post) {
	        res = post.split(',');
	        var postid = "name"+res[0];
	        var irt = "InReplyTo"+res[0];
	        var replyspan = "leave_reply"+res[0];
	        replyto = document.getElementById(replyspan);
	        replyto.innerText = "Replying to "+res[2];
	        document.getElementById(irt).value = res[1];
	        document.getElementById(postid).setSelectionRange(0, 0);
	        document.getElementById(postid).focus();
	        rect = replyto.getBoundingClientRect();
	        recttop = rect.top;
    	    window.scrollTo(0, recttop);
    	    
    	    replyto.classList.add("flash");
    		setTimeout(function(){ replyto.classList.remove("flash"); }, 1000);
    	}
    	
		var clickable = document.getElementsByClassName("clickable");
		var x = new Array();
    	for (i = 0; i < clickable.length; i++) { 
  			x[i] = clickable[i].innerHTML;
  			clickable[i].innerHTML = x[i] + "<div style='height: 0px; position: relative; width: 50%; margin: 0 auto;'><img src='/images/expand.png' class='overlay noradius'></div>";
  		}
  		
  		// autosave - thanks Jan-Lukas – https://jlelse.blog/dev/form-cache-localstorage
  		
  		var content = document.getElementById("content");
		var cached = localStorage.getItem("content");
		
		if (cached != null) {
			content.value = cached;
		}
		content.addEventListener("input", function () {
			localStorage.setItem("content", content.value);
		})
		
		document.addEventListener("submit", function () {
             localStorage.removeItem("content");
         })
    	 
     </script>

<?php
/*
if ($_SESSION['auth'] == $dbauth) { ?>
    <style>
        @media screen and (min-width: 768px) {
            #page {
                min-height: calc(100vh - 217px) !important;
            }
        }
    </style>
<?php
} 
*/
include('footer.php');
?>
