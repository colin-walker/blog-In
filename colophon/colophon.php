<?php
define('APP_RAN', '');

require_once('../config.php');

?>

<!DOCTYPE html>
<html lang="en-GB">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo constant('NAME'); ?> — Colophon</title>
	<link rel="stylesheet" href="../style.css" type="text/css" media="all">
</head>

<body>
    <div id="page" class="hfeed h-feed site">
        <header id="masthead" class="site-header">
            <div class="site-branding">
                <h1 class="site-title">
                    <a href="<?php echo BASE_URL; ?>" rel="home">
                        <span class="p-name">Colophon</span>
                    </a>
                </h1>
            </div>
        </header>

		<div id="primary" class="content-area">
			<main id="main" class="site-main today-container">
				<article>
				<h3 class="titleSpan" style="margin-bottom: 0px !important;">Hey there</h3>
					<div class="entry-content e-content pre-line">
						This is <strong>NOT</strong> a WordPress blog.

						There, I'm glad we got that out of the way. I've not got anything against WordPress <em>per se</em> but I wish it was easier and more intuitive to do simple things like post. It's outgrown being just a blogging platform so now has to try to be all things to all people.

						That's why I designed a custom layer over the top of it to enable inline posting and editing, as well as media uploads, without ever having to resort to wp-admin. As this was all essentially separate from the Wordpress core I started thinking about making it a standalone blogging engine and that's what we have here.

                      	The engine (although that's really too grand a term) is called "blog-In" which is an obvious play on blogging. the "In" is capitalised and the I replaced with an input cursor to reflect that the blog content is all managed inline with no admin system. (There is, however, an admin page.)

                      	<img src="https://colinwalker.blog/uploads/2021/01/blog-in.png" class="aligncenter" />
						It is a relatively simple PHP site, with a bunch of JavaScript, connected to a database in a similar fashion to WordPress, albeit, drastically simplified. It has a login system and inline posting/editing functionality that is only displayed when logged in, I can even delete posts and comments directly from the home page should I need to. The method for media uploads has also been ported over from my custom WordPress layer and operates in a similar manner dividing them into year &amp; month folders which are created on the fly if they don't exist.

						The blog itself is one core file (with a few includes, about, colophon and search pages) that displays posts for the day, in chronological order, with previous/next links to reload the page and change the day being viewed. It uses Emanuil Rusev's <a href="https://github.com/erusev/parsedown">Parsedown</a> &amp; <a href="https://github.com/erusev/parsedown-extra">ParsedownExtra</a> libraries for PHP so that posts can be written in Markdown Extra which I mainly use for footnotes and applying classes to images (width and alignment) but rendered in HTML. <a href="http://www.bigfootjs.com/">bigfoot.js</a> has been incorporated to handle footnotes.

						Custom RSS feeds for "posts as they happen" and a "daily feed" are built automatically when a post is submitted, edited or deleted and according to a daily schedule. The daily feed combines all posts for that day into a single, chronological item so reflects the view on the blog itself.

						Each post (or section) has inline comments visible via a toggle and I am using <a href="https://github.com/PHPMailer/PHPMailer">PHPMailer</a> to send myself notification emails whenever a comment is received. Outgoing webmentions are sent using the <a href="https://github.com/indieweb/mention-client-php">indieweb mention-client-php</a> library and I have built a webmention endpoint to receive mentions. This uses the <a href="https://github.com/microformats/php-mf2">mf2-php</a> library to parse remote HTML into easily handled JSON to help convert incoming mentions to comments based on mention type. Top level comments can be replied to and webmentions will be sent where appropriate. 
						
						The site does not collect any information just from your being here and there are no local cookies. On occasion I may embed third-party material such as YouTube videos so the usual caveats apply. Should you wish to leave a comment the only compulsory field is "Name" but that doesn't even have to be your real name. If you provide your website this will be used in an attempt to pull a favicon to display as an avatar next to the comment — this is pulled at display time and not stored. I only ask for your email address when you <a href="../joinme/">sign up to the muse-letter</a>.
						
						On receipt of webmentions the source URL will be recorded and the author's name and avatar location pulled from the source if available and stored in the database solely for the purpose of displaying the webmention as a comment.

						This is now my primary blogging platform, the muse-letter and journal have also been migrated, while the WordPress site will coexist to host the post archive and digital garden. At least for the time being.
					</div>
				</article>
			</main>
		</div><!-- #primary -->
	</div><!-- #page -->

	<div class="linksDiv day-links"><a href="<?php echo BASE_URL; ?>" title="Return to Today">Today</a>|<a href="../joinme/" title="Subscribe to regular & daily RSS feeds & the muse-letter">Join me</a>
	</div>

<?php
	include('../footer.php');
?>