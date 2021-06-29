<?php
/**
    Name: Join me
 */
 
define('APP_RAN', '');

require_once('../config.php');

?>

<!DOCTYPE html>
<html lang="en-GB">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#eeeeee">
	<title>Join Me</title>
	<meta name="description" content="<?php echo constant('DESCRIPTION'); ?>">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="stylesheet" href="../style.css" type="text/css" media="all">
	<link rel="stylesheet" href="../bigfoot/bigfoot-bottom.css" type="text/css" media="all">
	<link rel="webmention" href="<?php echo constant('BASE_URL'); ?>/endpoint.php"/>
	<link rel="http://webmention.org/" href="<?php echo constant('BASE_URL'); ?>/endpoint.php"/>	
    <link rel="home alternate" type="application/rss+xml" title="<?php echo constant('NAME'); ?> :: Daily Feed" href="<?php echo constant('BASE_URL'); ?>/dailyfeed.rss" />
    <link rel="alternate" type="application/rss+xml" title="<?php echo constant('NAME'); ?> :: Live Feed" href="<?php echo constant('BASE_URL'); ?>/livefeed.rss" />
    <link rel="me" href="mailto:<?php echo constant('MAILTO'); ?>" />
	<link rel="me" href="https://micro.blog/colinwalker" />
	
	<script type="text/javascript" src="../script.js"></script>
</head>

<body>
    <div id="page" class="hfeed h-feed site">
        <header id="masthead" class="site-header">
            <div class="site-branding">
                <h1 class="site-title">
                    <a href="<?php echo BASE_URL; ?>" rel="home">
                        <span class="p-name">Join Me</span>
                    </a>
                </h1>
            </div>
        </header>

	    <div id="primary" class="content-area">
		    <main id="main" class="site-main" role="main">
				<article>
					<div class="entry-content e-content pre-line">
					    <strong>Want to keep up with what’s going on?</strong>

                        I recommend subscribing to the <a href="<?php echo constant('BASE_URL'); ?>/dailyfeed.rss">Daily Update RSS feed</a> - get all posts from the previous day as one item, in chronological order, as if it were a "day page" on the blog itself.

                        For a more traditional "posts as they happen" RSS feed you can use the <a href="<?php echo constant('BASE_URL'); ?>/livefeed.rss">Live RSS Feed</a>.                 
                        <hr noshade width="33%" style="margin-bottom: -20px; margin-top: 25px;" size="1">   
                    </div>
                </article>
		    </main><!-- #main -->
	    </div><!-- #primary -->
	</div><!-- #page -->

    	<div class="linksDiv day-links"><a href="<?php echo BASE_URL; ?>">Today</a>|<a accesskey="s" style="text-decoration: none;" title="Search" href="../search/"><picture class="searchicon"><source srcset="../images/search_dark.png" media="(prefers-color-scheme: dark)"><img class="searchicon" src="../images/search_light.png" alt="Search the blog"></picture></a>|<a href="/joinme/" title="Subscribe to regular & daily RSS feeds & the muse-letter">Join me</a>
	</div>
    
    <style>
        @media screen and (min-width: 768px) {
            #page {
                min-height: calc(100vh - 162px) !important;
            }
        }
    </style>
    
    <div id="siteID" class="siteID">
        <span class="nameSpan">
            <a href="../about/"><?php echo constant("NAME"); ?></a>
        </span>
        <br/>
        <span class="licSpan">
            <?php if(getOption('Use_Now') == 'yes') { ?><a href="../now/">NOW</a> | <?php } ?><a href="../colophon/">Colophon</a> | Content: <a href="https://creativecommons.org/licenses/by-nc/2.0/uk/">CC BY-NC 2.0 UK</a>
        </span>
    </div>

    <div class="h-card p-author vcard author">
        <img class="u-photo" src="<?php echo constant('AVATAR'); ?>" alt="<?php echo constant("NAME"); ?>"/>
        <a class="u-url" rel="me" href="<?php echo constant('BASE_URL'); ?>"><?php echo constant("NAME"); ?></a>
        <a rel="me" class="u-email" href="mailto:<?php echo constant("MAILTO"); ?>"><?php echo constant("MAILTO"); ?></a>
        <p class="p-note"></p>
    </div>

</body>
</html>
