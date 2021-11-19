<?php

define('APP_RAN', '');

require_once('config.php');


function filters($content) {

GLOBAL $indent;
$indent = 'false';

$two = substr($content, 0, 2);
if ($two == '>>') {
	$content = substr($content, 2);
	$indent = 'true';
}


// break

$content = str_replace('[hr]', '<hr noshade width="33%" style="margin-bottom: 25px; margin-top: 25px;" size="1">' ,$content);


// linked hashtags

	$content = preg_replace('/((?<!&|\'|\`|\#|\)|\||\/|\[|[0-9]|[a-z]|=")#(?!\s|#|\*|\$|^[a-z]).*?)([^\s|^"|^\)|^\.<]+)/i', '<span class="hashtag"><a href="' . BASE_URL . '/search/?s=%23'.'$2&source=blog">#$2</a></span>', $content);

	
// internal links

	$open = '[[';
	$close = ']]';
	$linkcount = substr_count($content, $open);

	for ($i=0; $i < $linkcount; $i++) {
		$opos = strpos($content, $open);
		$cpos = strpos($content, $close);
		$len = $cpos-$opos;
		if ($cpos - $opos != 2) {
			$orig = substr($content, $opos, $len+2);
			$linktext = substr($content, $opos+2, $len-2);
			$gardenlinkurl = strtolower(str_replace(' ', '-', $linktext));
			$replace = '<a href="/garden/page.php?t=' . $linktext . '">' . $linktext . '</a>';
			$content = str_replace($orig, $replace , $content);
		}
	}


// external links

	$open = '{{';
	$close = '}}';
	$linkcount = substr_count($content, $open);

	for ($i=0; $i < $linkcount; $i++) {
		$opos = strpos($content, $open);
		$cpos = strpos($content, $close);
		$len = $cpos-$opos;
		if ($cpos - $opos != 2) {
			$orig = substr($content, $opos, $len+2);
			$linktext = substr($content, $opos+2, $len-2);
			$gardenlinkurl = strtolower(str_replace(' ', '-', $linktext));
			$replace = '&#123;&#123;<a href="' . $linktext . '">' . $linktext . '</a>&#125;&#125;';
			$content = str_replace($orig, $replace, $content);
		}
	}


// details/summary

	$open = '!!';
	$close = '>!';
	$linkcount = substr_count($content, $open);

	for ($i=0; $i < $linkcount; $i++) {
		$opos = strpos($content, $open);
		$cpos = strpos($content, $close);
		$len = $cpos-$opos;
		if ($cpos - $opos != 2) {
			$orig = substr($content, $opos, $len);
			$summary = substr($content, $opos+2, $len-2);
			$replace = '<details><summary style="outline: none;">' . $summary. '</summary>';
			$content = str_replace($orig, $replace, $content);
		}
	}

	$open = '>!';
	$close = '!<';
	$linkcount = substr_count($content, $open);

	for ($i=0; $i < $linkcount; $i++) {
		$opos = strpos($content, $open);
		$cpos = strpos($content, $close);
		$len = $cpos-$opos;
		if ($cpos - $opos != 2) {
			$orig = substr($content, $opos, $len+2);
			$details = substr($content, $opos+2, $len-2);
			$replace = '<div style="margin-left: 17px;">' . $details . '</div></details>';
			$content = str_replace($orig, $replace, $content);
		}
	}
	
// End details/summary


// strikethrough

	$strike = '~~';
	$check = '/~~/i';
	$strikecount = substr_count($content, $strike);

	$odd = 1;

	for ($i=0; $i < $strikecount; $i++) {
		if ($odd == 1) {
			$replace = '<del>';
			$odd = 0;
		} else {
			$replace = '</del>';
			$odd = 1;
		}
		$content = preg_replace($check, $replace, $content, 1);
	}
	
	
// underline

	$under = '~';
	$check = '/(\~(?<!\s)(.+?)(?!\s)\~)/i';
	$linkscount = substr_count($content, $under);

	$odd = 1;

	for ($i=0; $i < $linkscount; $i++) {
		if ($odd == 1) {
			$replace = '<span style="text-decoration: underline;">';
			$odd = 0;
		} else {
			$replace = '</span>';
			$odd = 1;
		}
		
		$replace = "<span style='text-decoration: underline;'>$2</span>";
		$content = preg_replace($check, $replace, $content, 1);
	}	
	
	
// superscript

	$under = '^';
	$check = '/(\^(?<!\s)(.+?)(?!\s)\^)/i';
	$linkscount = substr_count($content, $under);

	$odd = 1;

	for ($i=0; $i < $linkscount; $i++) {
		if ($odd == 1) {
			$replace = '<sup>';
			$odd = 0;
		} else {
			$replace = '</sup>';
			$odd = 1;
		}
		
		$replace = "<sup>$2</sup>";
		$content = preg_replace($check, $replace, $content, 1);
	}	
	
	
	
// embedded audio

	$open = '[a[';
	$close = ']a]';
	$linkcount = substr_count($content, $open);

	for ($i=0; $i < $linkcount; $i++) {
		$opos = strpos($content, $open);
		$cpos = strpos($content, $close);
		$len = $cpos-$opos;
		if ($cpos - $opos != 3) {
			$orig = substr($content, $opos, $len+4);
			$linktext = substr($content, $opos+3, $len-3);
			$replace = '<audio controls="controls" preload="metadata" src="' . $linktext . '"></audio>';
			$content = str_replace($orig, $replace, $content);
		}
	}

	
	
// embedded video

	$open = '[v[';
	$close = ']v]';
	$linkcount = substr_count($content, $open);

	for ($i=0; $i < $linkcount; $i++) {
		$opos = strpos($content, $open);
		$cpos = strpos($content, $close);
		$len = $cpos-$opos;
		if ($cpos - $opos != 3) {
			$orig = substr($content, $opos, $len+4);
			$linktext = substr($content, $opos+3, $len-3);
			
			$replace = '<div class="aligncenter"><video width="90%" controls><source src="' . $linktext . '" type="video/mp4">Can\'t see the video? <a href="' . $linktext . '">Click here to watch...</a></video></div>';
			
			$content = str_replace($orig, $replace, $content);
		}
	}
	
	
	
	// embedded YouTube

	$open = '[y[';
	$close = ']y]';
	$linkcount = substr_count($content, $open);

	for ($i=0; $i < $linkcount; $i++) {
		$opos = strpos($content, $open);
		$cpos = strpos($content, $close);
		$len = $cpos-$opos;
		if ($cpos - $opos != 3) {
			$orig = substr($content, $opos, $len+4);
			$linktext = substr($content, $opos+3, $len-3);
			
			$linktext_array = explode(' "', $linktext);
			if ($linktext_array[1] != '') {
				$linktext_array[1] = '<strong>' . substr($linktext_array[1], 0 , -1) . '</strong><br/>';
			} else {
			  $linktext_array[1] = '';
			}
		
			$replace = '<div class="aligncenter" style="position: relative; width: 100%; padding-top: 56.25%"><iframe style="position: absolute; top: 10px; bottom: 0; left: 0; right: 0; width: 100%; height: 100%" src="https://www.youtube.com/embed/' . $linktext_array[0] . '" allowfullscreen="false" frameborder="0">' . $linktext_array[1] . '<a href="https://www.youtube.com/embed/' . $linktext_array[0] . '">Can\'t see the video? Click here to watch...</a></iframe></div>';
			
			$content = str_replace($orig, $replace, $content);
		}
	}
	
	
	
	
// highlight text

	$pattern = "/::(.*)::/i";
	$replace = "<span style='background-color: #f1fe19; color: #333; padding: 2px 5px; border-radius: 5px;'>$1</span>";
	$content = preg_replace($pattern,$replace,$content);


// mark text

	$mark = "/==(.*)==/i";
	$replace = "<mark>$1</mark>";
	$content = preg_replace($mark,$replace,$content);

	
// search text

//	$search = "/[$](.*)[$]/i";
	$search = '/(\$\$(?<!\s)(.+?)(?!\s)\$\$)/i';
	$match = preg_match($search,$content,$matches);
	$term = trim($matches[0], '$');
	$replace = "<a href='" . BASE_URL . "/search/?s=$term&source=blog'><span style='text-decoration: double underline !important;'>$term</span></a>";
	$content = preg_replace($search,$replace,$content);
	
	
// dictionary
	$check = "/\!(.*)\!/i";
	//$check = "/!!(?<!\s)(.*)(?!\s)!!/i";
	$match = preg_match_all($check,$content,$matches, PREG_SET_ORDER);
	
	for ($i=0; $i<$match; $i++) {
		foreach ($matches as $test) {
			$trim = trim($test[0], '!');
			/*
			$file = fopen("dictionary.txt", "r");
			while(! feof($file)) {
				$line = fgets($file);
				if(strpos($line, $trim) === 0) {
  					$explode = explode(',',$line);
					$replacement = $explode[1];
					$content = str_replace($test[0], $replacement, $content);
				}
			}
			fclose($file);
			*/
			
			$sqlconn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
			$sqlconn->set_charset("utf8mb4_unicode_ci");
			$dict_sql = $sqlconn->prepare("SELECT Replacement FROM nowp_dictionary WHERE Snippet=?");
			$dict_sql->bind_param("s", $trim);
			$dict_sql->execute();
			$dict_result = mysqli_stmt_get_result($dict_sql);
			$dict_row = $dict_result->fetch_assoc();
			$replacement = $dict_row["Replacement"];
			if($replacement != '') {
				$content = str_replace($test[0], $replacement, $content);
			}
			$dict_sql->close();
		}
	}
	

	return $content;
	
}


function like($content) {
		$open = '((';
		$close = '))';

		$content = '..' . $content;
		$linkopos = strpos($content, $open);
		$linkcpos = strpos($content, $close);
		$len = $linkcpos-$linkopos;
		$orig = substr($content, $linkopos, $len+2);
		$linktext = substr($content, $linkopos+2, $len-2);

		if ( $linkopos && $linkcpos && $linktext !="" ) {
			ob_start();
            $ch = curl_init($linktext);
			curl_setopt($ch,CURLOPT_USERAGENT,parse_url(BASE_URL)['host']);
			curl_setopt($ch,CURLOPT_HEADER,0);
      		curl_setopt($ch, CURLOPT_ENCODING, "");
			$ok = curl_exec($ch);
			curl_close($ch);
			$result = ob_get_contents();
			ob_end_clean();

			$dom = new DOMDocument();
			libxml_use_internal_errors(true);
			$dom->loadHTML($result);
			libxml_clear_errors();
			$liked_title = $dom->getElementsByTagName('title')->item('0')->nodeValue;
			$liked_title = trim($liked_title);

			if ($liked_title != '') {
				$length = 75;
				if (strlen($liked_title) > $length) {
				    $liked_title = wordwrap($liked_title, $length);
				    $liked_title = substr($liked_title, 0, strpos($liked_title, "\n"));
				}

				$like_str = '<p><em>Liked: <a class="u-like-of" href="' . $linktext . '">' . $liked_title . '</a>...</em></p>';
				$content = str_replace($orig, $like_str, $content);
			}
		}

		$content = substr($content, 2);
		return $content;
}



function reply($content) {
		$open = '(r(';
		$close = ')r)';

		$content = '..' . $content;
		$linkopos = strpos($content, $open);
		$linkcpos = strpos($content, $close);
		$len = $linkcpos-$linkopos;
		$orig = substr($content, $linkopos, $len+4);
		$linktext = substr($content, $linkopos+3, $len-3);

		if ( $linkopos && $linkcpos && $linktext !="" ) {
            ob_start();
            $ch = curl_init($linktext);
			curl_setopt($ch,CURLOPT_USERAGENT,parse_url(BASE_URL)['host']);
			curl_setopt($ch,CURLOPT_HEADER,0);
      		curl_setopt($ch, CURLOPT_ENCODING, "");
			$ok = curl_exec($ch);
			curl_close($ch);
			$result = ob_get_contents();
			ob_end_clean();

			$dom = new DOMDocument();
			libxml_use_internal_errors(true);
			$dom->loadHTML($result);
			libxml_clear_errors();
			$reply_title = $dom->getElementsByTagName('title')->item('0')->nodeValue;
			$reply_title = trim($reply_title);

			if ($reply_title != '') {
				$length = 50;
				if (strlen($reply_title) > $length) {
				    $reply_title = wordwrap($reply_title, $length);
				    $reply_title = substr($reply_title, 0, strpos($reply_title, "\n"));
				}
			}
		} else {
			$linktext = '';
			$reply_title = '';
		}

		$content = substr($content, 2);
		//return $content;
		return array($linktext, $reply_title, $content);
}



function bookmark($content) {
		$open = '!(';
		$close = ')!';

		$content = '..' . $content;
		$linkopos = strpos($content, $open);
		$linkcpos = strpos($content, $close);
		$len = $linkcpos-$linkopos;
		$orig = substr($content, $linkopos, $len+2);
		$linktext = substr($content, $linkopos+2, $len-2);

		if ( $linkopos && $linkcpos && $linktext !="" ) {
			ob_start();
            $ch = curl_init($linktext);
			curl_setopt($ch,CURLOPT_USERAGENT,parse_url(BASE_URL)['host']);
			curl_setopt($ch,CURLOPT_HEADER,0);
			$ok = curl_exec($ch);
			curl_close($ch);
			$result = ob_get_contents();
			ob_end_clean();

			$dom = new DOMDocument();
			libxml_use_internal_errors(true);
			$dom->loadHTML($result);
			libxml_clear_errors();
			$book_title = $dom->getElementsByTagName('title')->item('0')->nodeValue;

			if ($book_title != '') {
				$length = 75;
				if (strlen($book_title) > $length) {
				    $book_title = wordwrap($book_title, $length);
				    $book_title = substr($book_title, 0, strpos($book_title, "\n"));
				}

				$book_str = '<p><em>Bookmarked: <a class="u-bookmark-of" href="' . $linktext . '">' . $book_title . '</a>...</em></p>';
				$content = str_replace($orig, $book_str, $content);
			}
		}

		$content = substr($content, 2);
		return $content;
}

?>