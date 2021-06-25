<?php

	define('APP_RAN', '');

	require_once('config.php');

	$c = $_GET['c'];
	$cArray = explode(':', $c);
	$section = $cArray[0];
	$comment_no = $cArray[1];
	
	$comment_sql = $connsel->prepare("SELECT Name, Photo, Website, Comment, InReplyTo FROM " . COMMENTS . " WHERE ID=?");
	$comment_sql->bind_param("i", $comment_no);
	$comment_sql->execute();
    $comment_result = mysqli_stmt_get_result($comment_sql);
    $row = $comment_result->fetch_assoc();
    $name = $row["Name"];
    $photo = $row["Photo"];
    $website = $row["Website"];
    $comment = stripcslashes($row["Comment"]);
    $InReplyTo = $row["InReplyTo"];
    $comment_result->close();
    
    $InReplyTo_sql = $connsel->prepare("SELECT Parent, Website FROM " . COMMENTS . " WHERE ID=?");
	$InReplyTo_sql->bind_param("s", $InReplyTo);
	$InReplyTo_sql->execute();
	$InReplyTo_result = mysqli_stmt_get_result($InReplyTo_sql);
    $row = $InReplyTo_result->fetch_assoc();
    $parent = $row["Parent"];
    $targetURL = $row["Website"];
	$InReplyTo_sql->close();
	
	$Parent_sql = $connsel->prepare("SELECT Section, Day FROM " . POSTS . " WHERE ID=?");
	$Parent_sql->bind_param("s", $parent);
	$Parent_sql->execute();
	$Parent_result = mysqli_stmt_get_result($Parent_sql);
	$row = $Parent_result->fetch_assoc();
	$section = $row["Section"];
	$day = $row["Day"];
	$Parent_sql->execute();
	
	if (parse_url($website)['host'] == parse_url(BASE_URL)['host']) {
		$photo = AVATAR;
	} else if ($photo == '') {
		$photo = 'https://icons.duckduckgo.com/ip2/' . parse_url($website)['host'] . '.ico';
	}
	$sourceURL = BASE_URL . '/?date=' . $day . '&c=' . $section . ':' . $comment_no;

?>
<!DOCTYPE htmL>
<html>
	<head>
		<title>Reply</title>
		<link rel="stylesheet" href="/style.css" type="text/css" media="all">

		<script type="text/javascript">
			<!--
			// redirect to comment-page and scroll to comment
			window.location = "<?php echo $sourceURL; ?>";
			//–>
		</script>
	</head>

	<body>
		<div id="page" class="hfeed h-feed site">
        <header id="masthead" class="site-header">
            <div class="site-branding">
                <h1 class="site-title">
                    <a href="<?php echo BASE_URL; ?>" rel="home">
                        <span class="p-name">Reply</span>
                    </a>
                </h1>
            </div>
        </header>
        
        <div id="primary" class="content-area">
			<main id="main" class="site-main today-container">
				<div>
				<?php
					if ($photo) {
            			echo '<object class="avatar" data="' . $photo . '" type="image/png"><img class="avatar" src="/images/avatar.png"/></object><span class="commentNameSpan" style="margin-left: 8px;">' . $name . '</span>';
        			} else {
            			echo '<div class="photo-box"><div class="box-content"><div><span>' . $name[0] . '</span></div></div></div><span class="commentNameSpan" style="margin-left: 8px;">' . $name . '</span>';
        			}
        		?>
        		</div>
				<article style="margin-left: 35px;" id="<?php echo $section.$comment_no; ?>" class="h-comment h-as-comment h-entry">
					<div class="e-content entry-content p-summary"><?php echo $comment; ?></div>
					<footer class="entry-meta">
						<address class="p-author h-card">
							<?php if ($photo) { ?>
							<img class="u-photo" src="<?php echo $photo; ?>">
							<?php } ?>
							<span class="p-name"><?php echo $name; ?></span>
						</address><!-- .comment-author .vcard -->
					
						Target URL: <a href="<?php echo $targetURL; ?>" rel="in-reply-to" class="u-in-reply-to"><?php echo $targetURL; ?></a>
					
						<div>
							<br/>Source URL: <a href="<?php echo $sourceURL; ?>"><?php echo $sourceURL; ?></a>
						</div>
					</footer>
				</article>
			</main>
		</div>
	</body>
</html>
