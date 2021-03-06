<?php

if(!defined('APP_RAN')){ die(); }

date_default_timezone_set('' . TIMEZONE . '');

// require_once "config.php";
// require_once "Parsedown.php";
// require_once "ParsedownExtra.php";

// Start Posts as they happen

//start RSS feed

$root = $_SERVER['DOCUMENT_ROOT'];
$file = $root . '/livefeed.rss';

$count = 0;

		if ( file_exists( $file ) ) {
		  unlink( $file );
		}

$xmlfile = fopen($file, 'w');

fwrite($xmlfile, '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL);
fwrite($xmlfile, '<rss version="2.0"'.PHP_EOL);
fwrite($xmlfile, 'xmlns:content="http://purl.org/rss/1.0/modules/content/"'.PHP_EOL);
fwrite($xmlfile, 'xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"'.PHP_EOL);
fwrite($xmlfile, '>'.PHP_EOL);
fwrite($xmlfile, '<channel>'.PHP_EOL);
fwrite($xmlfile, '<title>'. NAME . ' — Live Feed</title>'.PHP_EOL);
fwrite($xmlfile, '<description>Posts as they happen from ' . parse_url(BASE_URL)['host'] . '</description>'.PHP_EOL);
fwrite($xmlfile, '<link>' . BASE_URL . '</link>'.PHP_EOL);
fwrite($xmlfile, '<lastBuildDate>' . gmdate('D, d M Y H:i:s') . ' GMT</lastBuildDate>'.PHP_EOL);
fwrite($xmlfile, '<generator>(b)log-In</generator>'.PHP_EOL);
fwrite($xmlfile, '<language>en-GB</language>'.PHP_EOL);
fwrite($xmlfile, '<sy:updatePeriod>hourly</sy:updatePeriod>'.PHP_EOL);
fwrite($xmlfile, '<sy:updateFrequency>1</sy:updateFrequency>'.PHP_EOL);

// get posts

$feedsql = $connsel->prepare("SELECT ID, Permalink, Section, Title, Content, Date, ReplyURL, Reply_Title, Draft FROM " . POSTS . " WHERE Draft='' ORDER BY ID Desc");
$feedsql->execute();
$feed_result = mysqli_stmt_get_result($feedsql);

while($row = $feed_result->fetch_assoc()) {
	$replyURL = '';
	if($count < 10) {
		$section_number = $row["Section"];
		$post_title = $row["Title"];
		$postdate = $row["Date"];
		$date = date_create($postdate, timezone_open("Europe/London"));
		$main_link = $row["Permalink"];
		$replyURL = $row["ReplyURL"];
		$reply_title = $row["Reply_Title"];
		$content = stripslashes($row["Content"]);
		
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

  		$content = filters($content);
  		if($replyURL != '') {
  			$content = substr($content, (strlen($replyURL)+9));
  			$content = '<p><em>In reply to: <a class="u-in-reply-to" href="' . $replyURL . '">' . $reply_title . '</a>...</em></p>' . $content;
  		}
  		
  		$trunc_content ='';
  		$trunc = strip_tags($content);
  		$explode = explode(' ', $trunc);
  		for ($x = 0; $x < 10; $x++) {
  			$trunc_content .= $explode[$x] . ' ';
  		}

		$Parsedown = new ParsedownExtra();
		$feedcontent = $Parsedown->text($content);
		$trunc_content = substr($trunc_content, 0, 50);
		$day = date_format($date,"Y/m/d");
		$postdate = gmdate("D, d M Y H:i:s", strtotime($postdate));

        	//add post to RSS

        	fwrite($xmlfile, '<item>'.PHP_EOL);
        	fwrite($xmlfile, '<link>' . $main_link . '#p' . $section_number . '</link>'.PHP_EOL);
        	fwrite($xmlfile, '<guid isPermaLink="false">' . $main_link . '#p' . $section_number . '</guid>'.PHP_EOL);
        	fwrite($xmlfile, '<pubDate>' . $postdate . ' GMT</pubDate>'.PHP_EOL);
        	if ($post_title != '') {
        		fwrite($xmlfile, '<title>' . $post_title . '</title>'.PHP_EOL);
        	}
        	fwrite($xmlfile, '<description><![CDATA[' . rtrim($trunc_content) . '...]]></description>'.PHP_EOL);
        	fwrite($xmlfile, '<content:encoded><![CDATA[' . $feedcontent . ']]></content:encoded>'.PHP_EOL);
        	fwrite($xmlfile, '</item>'.PHP_EOL);

        	// end add post to RSS

        $count++;
	}
}


// close RSS

fwrite($xmlfile, '</channel>'.PHP_EOL);
fwrite($xmlfile, '</rss>'.PHP_EOL);
fclose($xmlfile);


// end posts as they happen

?>