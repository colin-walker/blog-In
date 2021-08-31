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

// Get auth string from database

$authsql = $connsel->prepare("SELECT Option_Value FROM " . OPTIONS . " WHERE Option_Name = 'Auth' ");
$authsql->execute();
$authresult = mysqli_stmt_get_result($authsql);
$row = $authresult->fetch_assoc();
$dbauth = $row["Option_Value"];
$authsql->close();

?>

<!DOCTYPE html>
<html lang="en-GB">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#eeeeee">
	<title><?php echo NAME; ?> - random post</title>
	<meta name="description" content="<?php echo constant('DESCRIPTION'); ?>">
	<link rel="stylesheet" href="../style.css" type="text/css" media="all">
	<link defer rel="stylesheet" href="../bigfoot/bigfoot-bottom.css" type="text/css" media="all">
	<script type="text/javascript" src="../script.js"></script>
</head>

<body>
    <div id="page" class="hfeed h-feed site">
        <header id="masthead" class="site-header">
            <div class="site-branding">
                <h1 class="site-title">
                    <a href="<?php echo BASE_URL; ?>" rel="home">
                        <span class="p-name">Random post</span>
                    </a>
                </h1>
            </div>
        </header>


		<div id="primary" class="content-area">
			<main id="main" class="site-main today-container">

<?php

	$post_sql = $connsel->prepare("SELECT * FROM " . POSTS . " ORDER BY RAND() LIMIT 1");
	$post_sql->execute();
	$result = mysqli_stmt_get_result($post_sql);
	
	while ($row = $result->fetch_assoc()) {
	$status = $row["Draft"];
    $ID = $row["ID"];
    $permalink = $row["Permalink"];
    $section_number = $row["Section"];
    $post_title = $row["Title"];
    $post_time = $row["Date"];
    $replyURL = $row["ReplyURL"];
    $reply_title = $row["Reply_Title"];
  	$content = $row["Content"];
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
  	
  	if($status == 'draft') {
  	    header("location: " . BASE_URL . "/random/");
    	exit;
	} else {
        $statusStr = '';   
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

  	if (substr($content, 0, 3) != '<p>') {
		$content = '<p>' . $content;
	}

	if (substr($content, -3) == '<p>') {
		$content = substr($content, 0, -3);
	}
	
	$content = str_replace('img src', 'img loading="lazy" src', $content);

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

    echo '<article id="p' . $section_number . '"class="h-entry hentry">' . PHP_EOL;
    $indentSpan = '';
    if ($post_title != '') {
    	$openStr = '<h2 class="p-name postTitle">' . $post_title . '</h2>' . $reply_Str . '<div class="entry-content e-content"><p>';
    } else {
    	echo '<h6 class="post_title">' . $dbdate . '#p' . $section_number . '</h6>' . PHP_EOL;
   		$openStr = $reply_Str . '<div class="entry-content e-content p-name"><p>';
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
        echo '<a onclick="toggleComments(' . $ID . ')" title="' . $numrows . $numstring . ': click to read or leave your own" class="toggleComments"><picture class="commenticonpicture"><source srcset="/images/hascommentdark.png" media="(prefers-color-scheme: dark)"><img id="commenticon' . $ID . '" class="commenticon" src="/images/hascomment.png" alt="' . $numrows . $numstring . ': click to read or leave your own"></picture></a>' . $statusStr . $openStr . $content . "\n</div>" . PHP_EOL;
    } else {
      $numstring = " comments";
        echo '<a onclick="toggleComments(' . $ID . ')" title="' . $numrows . $numstring . ': click to read or leave your own" class="toggleComments"><picture class="commenticonpicture"><source srcset="/images/commentdark.png" media="(prefers-color-scheme: dark)"><img id="commenticon' . $ID . '" class="commenticon" src="/images/comment.png" alt="' . $numrows . $numstring . ': click to read or leave your own"></picture></a>' . PHP_EOL . $statusStr . $openStr . $content . "\n</div><!-- .entry-content -->" . PHP_EOL;
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
	            $photoStr .= '<object class="avatar" data="' . $photo . '" type="image/png" style="position: relative; left: 13px;"><img class="avatar" src="/images/avatar.png"/></object>';
	        } else {
	            $photoStr .= '<div class="photo-box" style="position: relative; left: 13px;"><div class="box-content"><div><span>' . $name[0] . '</span></div></div></div>';
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
}  ?>

				<div style="clear: both;"></div>
			</main><!-- #main -->
		</div><!-- #primary -->
	</div><!-- #page -->

	<nav class="navigation paging-navigation">
		<div class="nav-links">

			</div><!-- .nav-links -->
	</nav><!-- .navigation -->

	<div class="linksDiv day-links"><a href="<?php echo BASE_URL; ?>">Today</a>|<a accesskey="s" style="text-decoration: none;" title="Search" href="/search/"><picture class="searchicon"><source srcset="/images/search_dark.png" media="(prefers-color-scheme: dark)"><img class="searchicon" src="/images/search_light.png" alt="Search the blog"></picture></a><?php if(getOption("Use_Random") == "yes") {?>|<a accesskey="r" class="randomlink" style="text-decoration: none;" title="Random post" href="/random/">?</a><?php } ?>|<a href="/joinme/" title="Subscribe to regular & daily RSS feeds & the muse-letter">Join me</a>
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
    	 
     </script>

<?php
	$pageDesktop = "152";
	$pageMobile = "262";
	include('../footer.php');
?>