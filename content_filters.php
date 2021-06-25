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

	$content = preg_replace('/((?<!&|\'|\#|\)|\||\/|\[|[0-9]|[a-z]|=")#(?!\s|#|\*|\$|^[a-z]).*?)([^\s|^"|^\)|^\.<]+)/i', '<span class="hashtag"><a href="' . BASE_URL . '/search/?s=%23'.'$2">#$2</a></span>', $content);



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
	$linkscount = substr_count($content, $strike);

	$odd = 1;

	for ($i=0; $i < $linkscount; $i++) {
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
		
			//$replace = '<div class="aligncenter" style="position: relative; width: 100%; padding-top: 42.1875%;"><iframe style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; width: 100%; height: 100%" src="' . $linktext . '" allowfullscreen="false" frameborder="0">Click to watch...</iframe></div>';
			
			$replace = '<div class="aligncenter"><video width="90%" controls><source src="' . $linktext . '" type="video/mp4"><a href="' . $linktext . '">Click to watch...</a></video></div>';
			
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
		
			$replace = '<div class="aligncenter" style="position: relative; width: 100%; padding-top: 56.25%"><iframe style="position: absolute; top: 10px; bottom: 0; left: 0; right: 0; width: 100%; height: 100%" src="https://www.youtube.com/embed/' . $linktext . '" allowfullscreen="false" frameborder="0">Click to watch...</iframe></div>';
			
			$content = str_replace($orig, $replace, $content);
		}
	}
	
	
	
// highlight text

	$pattern = "/::(.*)::/i";
	$replace = "<span style='background-color: #f1fe19; color: #333; padding: 2px 5px; border-radius: 5px;'>$1</span>";
	$content = preg_replace($pattern,$replace,$content);
		
//	return $content;

// mark text

	$mark = "/==(.*)==/i";
	$replace = "<mark>$1</mark>";
	$content = preg_replace($mark,$replace,$content);
		
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
			$ok = curl_exec($ch);
			curl_close($ch);
			$result = ob_get_contents();
			ob_end_clean();

			$dom = new DOMDocument();
			libxml_use_internal_errors(true);
			$dom->loadHTML($result);
			libxml_clear_errors();
			$liked_title = $dom->getElementsByTagName('title')->item('0')->nodeValue;

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
			$ok = curl_exec($ch);
			curl_close($ch);
			$result = ob_get_contents();
			ob_end_clean();

			$dom = new DOMDocument();
			libxml_use_internal_errors(true);
			$dom->loadHTML($result);
			libxml_clear_errors();
			$reply_title = $dom->getElementsByTagName('title')->item('0')->nodeValue;
			//$reply_title = substr($reply_title, 0 , 30);

			if ($reply_title != '') {
				$length = 50;
				if (strlen($reply_title) > $length) {
				    $reply_title = wordwrap($reply_title, $length);
				    $reply_title = substr($reply_title, 0, strpos($reply_title, "\n"));
				}
				
				$reply_str = '<em>In reply to: <a class="u-in-reply-to" href="' . $linktext . '">' . $reply_title . '</a>...</em>';
				$content = str_replace($orig, $reply_str, $content);
			}
		}

		$content = substr($content, 2);
		return $content;
}



?>
