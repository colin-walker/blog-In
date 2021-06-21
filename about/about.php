<?php
define('APP_RAN', '');

require_once('../config.php');
require_once('../content_filters.php');
require_once('../Parsedown.php');
require_once('../ParsedownExtra.php');

	$sql = $connsel->prepare("SELECT Option_Value FROM " . OPTIONS . " WHERE Option_Name = 'About' ");
    $sql->execute();
    $sql_result = mysqli_stmt_get_result($sql);
    $row = $sql_result->fetch_assoc();
    $about = $row["Option_Value"];
    $sql->close();
    
    $Parsedown = new ParsedownExtra();
	$about = $Parsedown->text($about);

?>

<!DOCTYPE html>
<html lang="en-GB">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo constant('NAME'); ?> — About</title>
	<link rel="stylesheet" href="../style.css" type="text/css" media="all">
</head>

<body>
    <div id="page" class="hfeed h-feed site">
        <header id="masthead" class="site-header">
            <div class="site-branding">
                <h1 class="site-title">
                    <a href="<?php echo BASE_URL; ?>" rel="home">
                        <span class="p-name">About</span>
                    </a>
                </h1>
            </div>
        </header>

		<div id="primary" class="content-area">
			<main id="main" class="site-main today-container">
				<article>
					<div class="entry-content e-content page-content about-content">
						<?php echo $about; ?>
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