<?php

define('APP_RAN', '');

require_once('config.php');
require_once('content_filters.php');
require_once('Parsedown.php');
require_once('ParsedownExtra.php');

// Start Daily Feed

//start RSS feed

$root = $_SERVER['DOCUMENT_ROOT'];
$file = $root . '/dailyfeed.rss';

		if ( file_exists( $file ) ) {
		  unlink( $file );
		}

$xmlfile = fopen($file, 'w');

fwrite($xmlfile, '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL);
fwrite($xmlfile, '<rss version="2.0"'.PHP_EOL);
fwrite($xmlfile, 'xmlns:content="http://purl.org/rss/1.0/modules/content/"'.PHP_EOL);
fwrite($xmlfile, 'xmlns:wfw="http://wellformedweb.org/CommentAPI/"'.PHP_EOL);
fwrite($xmlfile, 'xmlns:dc="http://purl.org/dc/elements/1.1/"'.PHP_EOL);
fwrite($xmlfile, 'xmlns:atom="http://www.w3.org/2005/Atom"'.PHP_EOL);
fwrite($xmlfile, 'xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"'.PHP_EOL);
fwrite($xmlfile, 'xmlns:slash="http://purl.org/rss/1.0/modules/slash/"'.PHP_EOL);
fwrite($xmlfile, '>'.PHP_EOL);
fwrite($xmlfile, '<channel>'.PHP_EOL);
fwrite($xmlfile, '<title>' . NAME . ' — Daily Feed</title>'.PHP_EOL);
fwrite($xmlfile, '<description>Daily feed from ' . parse_url(BASE_URL)['host'] . '</description>'.PHP_EOL);
fwrite($xmlfile, '<link>' . BASE_URL . '</link>'.PHP_EOL);
fwrite($xmlfile, '<lastBuildDate>' . gmdate('D, d M Y H:i:s') . ' GMT</lastBuildDate>'.PHP_EOL);
fwrite($xmlfile, '<generator>(b)log-In</generator>'.PHP_EOL);
fwrite($xmlfile, '<language>en-GB</language>'.PHP_EOL);
fwrite($xmlfile, '<sy:updatePeriod>hourly</sy:updatePeriod>'.PHP_EOL);
fwrite($xmlfile, '<sy:updateFrequency>1</sy:updateFrequency>'.PHP_EOL);

// get posts

$today = date_create(date("Y/m/d"));
$dbdate = date('Y/m/d', strtotime($date .' -1 day'));
$feeddate = date('Y-m-d', strtotime($date .' -1 day'));
$pubdate = date_format($today,"D, d M Y H:i:s");
$normdate = date("d/m/Y", strtotime($date .'-1 day'));

$count = 0;
$post_title = '';

while ($count < 5) {

$sql = $connsel->prepare("SELECT ID, Permalink, Section, Title, Content, Date, Draft FROM " . POSTS . " WHERE Day=? AND Draft='' ORDER BY Section Asc");
$sql->bind_param("s", $dbdate);
$sql->execute();
$result = mysqli_stmt_get_result($sql);

while($row = $result->fetch_assoc()) {
		$post_title = '';
		$section_number = $row["Section"];
		$post_title = $row["Title"];
		$postdate = $row["Date"];
		$date = date_create($postdate);
		$main_link = $row["Permalink"];
		$content = stripslashes($row["Content"]);
		
  		$content = filters($content);

		$Parsedown = new ParsedownExtra();
		$feedcontent = $Parsedown->text($content);
		$feedcontent = substr($feedcontent, 3);
		if (substr($feedcontent, -4) == '</p>') {
			$feedcontent = substr($feedcontent, 0, -4);
		}
		
		if ($indent == 'true') {
			$indentSpan = ' style="margin-left: 25px;"';	
		}

		if ($post_title != '') {
			$h2 = '<span style="font-size: 24px; text-transform: uppercase;"><strong>' . $post_title . '</strong></span><br/>';
		}
		$feedcontent = '<p' . $indentSpan . '><a href="https://colinwalker.blog/?date=' . $feeddate . '#p' . $section_number . '" style="text-decoration: none; margin-right: 8px;">#</a> ' . $h2 . $feedcontent . '</p>'.PHP_EOL;

		$fullcontent .= $feedcontent;
			
		$h2 = '';
		$indentSpan = '';
}

        	//add post to RSS

        	fwrite($xmlfile, '<item>'.PHP_EOL);
        	fwrite($xmlfile, '<title>Posts for '.$normdate.'</title>'.PHP_EOL);
        	fwrite($xmlfile, '<link>' . $main_link . '</link>'.PHP_EOL);
        	fwrite($xmlfile, '<guid isPermaLink="false">' . $main_link . '</guid>'.PHP_EOL);
        	fwrite($xmlfile, '<pubDate>' . $fulldate . '</pubDate>'.PHP_EOL);
        	fwrite($xmlfile, '<description>Posts for '.$normdate.'</description>'.PHP_EOL);
        	fwrite($xmlfile, '<content:encoded><![CDATA[' . $fullcontent . ']]></content:encoded>'.PHP_EOL);
        	fwrite($xmlfile, '</item>'.PHP_EOL);

        	// end add post to RSS

$fullcontent = '';
$count++;
$sql->close();


$normdate = date("d/m/Y", strtotime($dbdate .'-1 day'));
$dbdate = date('Y/m/d', strtotime($dbdate .' -1 day'));
$feeddate = date('Y-m-d', strtotime($feeddate .' -1 day'));

} // end while

// close RSS

fwrite($xmlfile, '</channel>'.PHP_EOL);
fwrite($xmlfile, '</rss>'.PHP_EOL);
fclose($xmlfile);


// end feed

?>