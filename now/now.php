<?php
/*
	Name: Now
*/

// Initialise session
session_start();

define('APP_RAN', '');

require_once('../config.php');
require_once('../content_filters.php');
require_once('../Parsedown.php');
require_once('../ParsedownExtra.php');

date_default_timezone_set('' . TIMEZONE . '');

// Get auth string from database

$authsql = $connsel->prepare("SELECT Option_Value FROM " . OPTIONS . " WHERE Option_Name = 'Auth' ");
$authsql->execute();
$authresult = mysqli_stmt_get_result($authsql);
$row = $authresult->fetch_assoc();
$dbauth = $row["Option_Value"];
$authsql->close();

$date = date('Y-m-d');

$when = getOption('Now_Updated');


// Submit entry

if ( isset($_POST['dopost']) && ($_POST['content'] !='') ) {
    if ($_SESSION['auth'] == $dbauth) {
        $content = $_POST['content'];
        setOption('Now_Text', $content);
        setOption('Now_Updated', $date);
    }
}


// Update entry

if ( isset($_POST['updatepost']) && ($_POST['newcontent'] !='') ) {
    if ($_SESSION['auth'] == $dbauth) {
	    $newcontent = $_POST['newcontent'];
	    setOption('Now_Text', $newcontent);
        setOption('Now_Updated', $date);
	}
}


?>

<!DOCTYPE html>
<html lang="en-GB">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#eeeeee">
	<title>Now</title>
	<link rel="stylesheet" href="../style.css" type="text/css" media="all">
	<script type="text/javascript" src="../script.js"></script>
</head>

<body>
    <div id="page" class="hfeed h-feed site">
        <header id="masthead" class="site-header">
            <div class="site-branding">
                <h1 class="site-title">
                    <a href="<?php echo BASE_URL; ?>" rel="home">
                        <span class="p-name">Now</span>
                    </a>
                </h1>
            </div>
        </header>


		<div id="primary" class="content-area">
			<main id="main" class="site-main today-container">

<?php

if ($_SESSION['auth'] == $dbauth) {
	echo '<h2><span style="margin-bottom: 20px; cursor: pointer;" class="dateSpan" onclick="toggleUpdate()" accesskey="e">What I\'m doing now:</span><br/><span class="updatedSpan">(Updated: ' . $when . ')</span></h2>';
} else {
	echo '<h3 style="margin-bottom: 20px;" class="dateSpan">What I\'m doing now:<br/><span class="updatedSpan">(Updated: ' . $when . ')</span></h2>';
}

	$now = getOption('Now_Text');
	
	$content = $now;
	$raw_content = $content;
	$content = '@@ ' . $content;
	$content = filters($content);
	
	$Parsedown = new ParsedownExtra();
	$content = $Parsedown->text($content);	
	$content = str_replace('@@', '<a><span style=" margin-right: 10px;">#</span></a>', $content);
	
	if($now != '') {	
		echo '<article id="post">';		
		echo '<div class="section">';
		echo '<div id="entry" class="entry-content e-content">';
		echo $content;
		echo '</div>'; // entry-content
		echo '</div>'; // section
		echo '</article>';
	}
		
		echo '<div id="editdiv" class="editdivs">' . PHP_EOL;

?> 
    <form name="form" id="updateform">
        <input type="hidden" id="updatepost" name="updatepost">
        <input type="hidden" id="id" name="id" value="<?php echo $ID; ?>">
        <textarea rows="10" id="newcontent" name="newcontent" class="newcontent text"><?php echo $raw_content; ?></textarea>
        <a onclick="toggleUpdate();"><img  style="width: 20px; float: left; position: relative; top: -1px; cursor: pointer;" src="../images/cancel.png" /></a>
        <input style="float:right; font-size: 75%" type="submit" name="submit" id="submit" value="Update" accesskey="s">
    </form>
<?php
	echo '</div>';

?>



<?php	
	if ( $now == "" ) {
?>
    	<div id="editdiv" class="editdiv_no_posts">
        	<form name="form" method="post">
            	<input type="hidden" id="dopost" name="dopost" value="true">
            	<textarea rows="15" id="content" name="content" class="text"></textarea><input style="float:right; font-size: 75%" type="submit" name="submit" id="submit_no_posts" value="Post" accesskey="s">
        	</form>
    	</div>

<?php
	}
?>

			</main><!-- #main -->
		</div><!-- #primary -->
	</div><!-- #page -->
	
	<div class="linksDiv day-links">
		<a href="<?php echo BASE_URL; ?>">Today</a>|<a accesskey="s" style="text-decoration: none;" title="Search" href="../search/"><picture class="searchicon"><source srcset="../images/search_dark.png" media="(prefers-color-scheme: dark)"><img class="searchicon" src="../images/search_light.png" alt="Search the blog"></picture></a>|<a href="../joinme/" title="Subscribe to regular & daily RSS feeds & the muse-letter">Join me</a>
	</div>

	<script src="../jquery-3.6.0.min.js"></script>
	
	        <script>
	        
            $(document).ready(function(){
  				$("#submit").click(function(e){
                    //Stop the form from submitting itself to the server.
                    e.preventDefault();
                    
                    var pid = $("#id").val();
                    var content = $("#newcontent").val();
                    
                    var d = new Date();
					var month = d.getMonth() + 1;
					if (month < 10) {
						month = "0" + month;
					}
					var newdate = d.getDate() + "/" + month + "/" + (d.getYear()-100);
                    
                    $.post("updatenow.php",
                    	{
                    		updatepost: "true",
                    		newcontent: content,
                    		when: newdate,
                    	},
						function(data, status){		
							var XHR = new XMLHttpRequest();
							XHR.open("GET", "updatenow.php", true);
							XHR.send();
							XHR.onreadystatechange = function() {
								if (this.readyState == 4 && this.status == 200) {
									var data = JSON.parse(this.responseText);									document.getElementById("entry").innerHTML = data;
    								post.style.display = 'block';
    								edit.style.display = 'none';
    							}
							}
						})
                });
            });
            
        </script>
        
        

    <script>
    	function toggleUpdate() {
    		post = document.getElementById("post");
    		edit = document.getElementById("editdiv");
    		
    		if (post.style.display != 'none') {
    			post.style.display = 'none';
    			edit.style.display = 'block';
    			var contentArea = document.getElementById('newcontent');
                        var areaLen = contentArea.value.length;
                        contentArea.setSelectionRange(areaLen, areaLen);
                        contentArea.focus();
                        contentArea.scrollTop = contentArea.scrollHeight;
    		} else {
    			post.style.display = 'block';
    			edit.style.display = 'none';
    		}
    	}
    </script>
	
    <style>
    
        @media screen and (max-width: 767px) {
            #page {
                min-height: calc(100vh - 257px) !important;
            }
        }
        
        @media screen and (min-width: 768px) {
            #page {
                min-height: calc(100vh - 157px) !important;
            }
        }
    </style>

    <div id="siteID" class="siteID">
        <span class="nameSpan">
            <a href="../about/"><?php echo constant("NAME"); ?></a>
        </span>
        <br/>
        <span class="licSpan">
            <a href="/now/">NOW</a> | <a href="/colophon/">Colophon</a> | Content: <a href="https://creativecommons.org/licenses/by-nc/2.0/uk/">CC BY-NC 2.0 UK</a>
        </span>
    </div>
    
</body>
</html>