<?php

	define('APP_RAN', '');

	require_once('config.php');
	require_once('content_filters.php');
	require_once('Parsedown.php');
	require_once('ParsedownExtra.php');

	$getdate = $_GET['date'];
	$p = $_GET['p'];
	
	$date = str_replace('-', '/', $getdate);
	
	$post_sql = $connsel->prepare("SELECT * FROM " . POSTS . " WHERE Day=? and Section=?");
	$post_sql->bind_param("si", $date, $p);
	$post_sql->execute();
    $post_result = mysqli_stmt_get_result($post_sql);
    while($row = $post_result->fetch_assoc()) {
    	$ID = $row["ID"];
    	$permalink = $row["Permalink"];
    	$section_number = $row["Section"];
    	$post_title = $row["Title"];
    	$post_time = $row["Date"];
    	$replyURL = $row["ReplyURL"];
    	$reply_title = $row["Reply_Title"];
  		$content = $row["Content"];
  	
  		$raw = $db_content;
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
	}
		$post_result->close();

?>
<!DOCTYPE htmL>
<html>
	<head>
		<title>Post</title>
		<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
		<link rel="stylesheet" href="/style.css" type="text/css" media="all">

		<script type="text/javascript">
			<!--
			// redirect to post in situ
			window.location = "<?php echo $sourceURL . '/?date=' . $getdate . '#p' . $p; ?>";
			//–>
		</script>
	</head>

	<body>
		<div id="page" class="hfeed h-feed site">
        <header id="masthead" class="site-header">
            <div class="site-branding">
                <h1 class="site-title">
                    <a href="<?php echo BASE_URL; ?>" rel="home">
                        <span class="p-name">Post</span>
                    </a>
                </h1>
            </div>
        </header>
        
        <div id="primary" class="content-area">
			<main id="main" class="site-main today-container">
				<div>
				<?php
					echo '<article id="p' . $section_number . '"class="h-entry hentry" ' . $indentSpan . '>' . PHP_EOL;
    				if ($post_title != '') {
    					$openStr = '<h2 class="p-name postTitle">' . $post_title . '</h2>' . $reply_Str . '<div class="entry-content e-content"><p>';
    				} else {
    					echo '<h6 class="post_title p-name">' . $post_time . '#p' . $section_number . '</h6>' . PHP_EOL;
   						$openStr = $reply_Str . '<div class="entry-content e-content  p-name"><p>';
    				}
					echo '<div id="post' . $ID . '">' . PHP_EOL;
					echo '<div class="section">' . PHP_EOL . '<a style="float: left;" class="u-url hash"  href="' . $permalink . '#p' . $section_number . '">#</a>' . PHP_EOL;
				?>
					
				<?php echo $openStr . $content; ?></div>
					<?php
						echo '<time class="dt-published" datetime="' . date("c", strtotime($post_time)) . '"></time>' . PHP_EOL;
						?>
					    <div class="h-card p-author vcard author">
        					<img class="u-photo" src="<?php echo constant('AVATAR'); ?>" alt="<?php echo constant('NAME'); ?>"/>
        					<a class="u-url" href="<?php echo constant('BASE_URL'); ?>"><span class="p-name"><?php echo constant('NAME'); ?></span></a>
    					</div>
    				</div>
				</article>
				</div>
			</main>
		</div>
	</body>
</html>
