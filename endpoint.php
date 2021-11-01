<?php

define('APP_RAN', '');

require_once('config.php');
require_once('Mf2/Parser.php');

require_once('PHPMailer.php');
require_once('SMTP.php');
require_once('Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


if ( !isset( $_POST['source'] ) ) {
	http_response_code(400);
	exit ('"source" is missing');
}

if ( !isset( $_POST['target'] ) ) {
	http_response_code(400);
	exit ('"target" is missing');
}

if ($_POST['source'] != $_POST['target']) {
	if (!empty($_POST['source']) && !empty($_POST['target'])) {

		if ( (parse_url($_POST['source'])['scheme'] == 'http') || (parse_url($_POST['source'])['scheme'] == 'https') ) {
			$sourceURL = filter_var($_POST['source'], FILTER_SANITIZE_URL);
		} else {
			http_response_code(400);
			exit ('Source not a supported URL scheme');
		}

		if ( (parse_url($_POST['target'])['scheme'] == 'http') || (parse_url($_POST['target'])['scheme'] == 'https') ) {
			$targetURL = filter_var($_POST['target'], FILTER_SANITIZE_URL);
		} else {
			http_response_code(400);
			exit ('Target not a supported URL scheme');
		}

		$explode = explode("#p", $targetURL);
		$Permalink = $explode[0];
		$Section = $explode[1];

		if(strpos($sourceURL, parse_url(BASE_URL)['host'])) {
			$explode = explode("#p", $sourceURL);
			$mainlink = $explode[0];
			$sourceSection = $explode[1];
			$s = $sourceSection -1;
			//echo $sourceSection . PHP_EOL;  // uncomment for testing
			//echo $s . PHP_EOL;
			if ($s == -1) {
				$explode = explode("&p=", $sourceURL);	
				$sourceSection = $explode[1];
				$s = $sourceSection -1;
				//echo $s;
			}
		}

		$post_check = $connsel->prepare("SELECT ID FROM " . POSTS . " WHERE Permalink=? AND Section=?");
		$post_check->bind_param("si", $Permalink, $Section);
		$post_check->execute();
		$post_result = mysqli_stmt_get_result($post_check);
		while($row = $post_result->fetch_assoc()) {
  			$ID = $row["ID"];
		}

		if(!$ID) {
			http_response_code(400);
			exit ("Target URL not found.");
		}
		$post_check->close();

		$curl = curl_init($sourceURL);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		$contents = curl_exec($curl);
		curl_close($curl);

		$pos = strpos( $contents, $targetURL );
		if ( !$pos ) {
			http_response_code(400);
			exit ("Can't find target link.");
		}

		if ( !$contents ) {
			http_response_code(400);
			exit ("Source URL not found.");
		} else {
    		http_response_code(202);
    		echo 'Accepted';
		}

		if ($pos = strpos($targetURL, '##')) {
			$fragmention = substr($targetURL, $pos+2);
			$fragmention = str_replace('+', ' ', $fragmention);
		} else {
			$fragmention = '';
		}

		$jsonmf = new Mf2\Parser($contents, $sourceURL, true);
		$mf = $jsonmf->parse();

		//print_r( $mf ); // uncomment for testing via something like POSTMAN

		$Name = $mf['items']['0']['children']["$s"]['properties']['author']['0']['properties']['name'][0];
	  	if ( !$Name ) {
			$Name = $mf['items']['0']['properties']['author']['0']['properties']['name'][0];
		}
	  	if ( !$Name ) {
			$Name = $mf['items']['0']['children']['0']['properties']['author']['0']['properties']['name'][0];
		}

		if ( !$Name ) {
			if ( strpos($sourceURL, BASE_URL) ) {
				$Name = NAME;
			}	
		}

		$Photo = $mf['items']['0']['children']["$s"]['properties']['author']['0']['properties']['photo']['0'];
		if ( !$Photo ) {
			$Photo = 	$mf['items']['0']['properties']['author']['0']['properties']['photo']['0'];
		}
		if ( !$Photo ) {
			$Photo = 	$mf['items']['0']['children']['0']['properties']['author']['0']['properties']['photo']['0'];
		}
		
		if ( strpos($sourceURL, BASE_URL) == 0 ) {
		  $Photo = AVATAR;
		}
		
		if(!(strpos($sourceURL, 'https://micro.blog/') === false)) {
			$Photo = 'https://micro.blog/' . $Name . '/avatar.jpg';	
		}
		
		if ( !$Photo ) {
			$Photo = "";
		}

		$Comment = $mf['items']['0']['children']["$s"]['properties']['content']['0']['value'];
		if ( !$Comment ) {
			$Comment = $mf['items']['0']['properties']['content']['0']['value'];
		}

		$Reply = $mf['items']['0']['children']["$s"]['properties']['in-reply-to']['0'];
		if (!$Reply) {
  			$Reply = $mf['items']['0']['properties']['in-reply-to']['0'];
		}
		if ($Reply) {
			$Reply = 'true';
		} else {
			$Reply = '';
		}

		$Like = $mf['items']['0']['children']["$s"]['properties']['like-of']['0'];
		if (!$Like) {
  			$Like = $mf['items']['0']['properties']['like-of']['0'];
		}
		if ($Like) {
			$Like = 'true';
		} else {
			$Like = '';
		}

		$Parent = $ID;
		$Website = $sourceURL;
		$Mention = 'true';

		/*	
		echo PHP_EOL;	 		// uncomment for testing via something like POSTMAN
		echo $Parent . PHP_EOL;
		echo $Name . PHP_EOL;
		echo $sourceURL . PHP_EOL;
		echo $Comment . PHP_EOL;
		echo $Like . PHP_EOL;
		echo $Reply . PHP_EOL;
        echo $targetURL . PHP_EOL;
        echo $fragmention . PHP_EOL;
        */

		$comment_sql = $conn->prepare("INSERT INTO " . COMMENTS . " (Parent, Name, Photo, Website, Comment, Mention, isLike, isReply, Fragmention) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$comment_sql->bind_param("issssssss", $Parent, $Name, $Photo, $Website, $Comment, $Mention, $Like, $Reply, $fragmention);
		if($comment_sql) {
			$comment_sql->execute();
	    	$comment_ID = $comment_sql->insert_id;
	    	$cid = $comment_ID;
			$comment_sql->close();

			$parent_sql = $connsel->prepare("SELECT Permalink, Section FROM " . POSTS . " WHERE ID=?");
			$parent_sql->bind_param("i", $Parent);
    		$parent_sql->execute();
    		$parent_sql->bind_result($db_permalink, $db_section);
    		$parent_result = mysqli_stmt_get_result($parent_sql);
    		$row = $parent_result->fetch_assoc();
    		$permalink = $row["Permalink"];
    		$section = $row["Section"];
    		$postlink = $permalink . '&c=' . $section . ':' . $cid; //'#p' . $section;
    		$parent_sql->close();

    		$body .= '<p>A new webmention has been received on the blog.</p>';
    		$body .= '<p>\'' . $Name . '\' mentioned <a href="' . $postlink . '">this post</a> saying:</p>';
   			$body .= '<blockquote>' . $Comment . '</blockquote>';
   			$body .= '<p>From: <a href="' . $Website . '">' . $Website . '</a></p>';

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
            		$mail->Subject = 'New webmention';
            		$mail->Body = $body;
	
        	    	$mail->send();
        	} catch (Exception $e) {
            		echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        	}

		}
	}
} else {
	http_response_code(400);
	exit ('"source" cannot equal "target"');

}

?>
