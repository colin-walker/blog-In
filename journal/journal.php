<?php
/*
	Name: Journal
*/

// Initialise session
session_start();

define('APP_RAN', '');

require_once('../config.php');
require_once('../content_filters.php');
require_once('../Parsedown.php');
require_once('../ParsedownExtra.php');

date_default_timezone_set('' . TIMEZONE . '');


// redirect if now disabled

if (getOption('Journal') == 'no') {
    header("location: " . BASE_URL);
    exit;
}


// Get auth string from database

$authsql = $connsel->prepare("SELECT Option_Value FROM " . OPTIONS . " WHERE Option_Name = 'Auth' ");
$authsql->execute();
$authresult = mysqli_stmt_get_result($authsql);
$row = $authresult->fetch_assoc();
$dbauth = $row["Option_Value"];
$authsql->close();


// admin check

if (!$_SESSION['auth'] == $dbauth) {
	die("Private!");
}

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$prev_date = date('Y-m-d', strtotime($date .' -1 day'));
$next_date = date('Y-m-d', strtotime($date .' +1 day'));

$year = date('Y', strtotime($date));
$month = date('m', strtotime($date));
$day = date('d', strtotime($date));

$dbdate = $year . '-' . $month . '-' . $day;


// Submit entry

if ( isset($_POST['dopost']) && ($_POST['content'] !='') ) {
    if ($_SESSION['auth'] == $dbauth) {
        $date = date("Y-m-d");
        $content = $_POST['content'];
        $post_sql = $conn->prepare("INSERT INTO " . JOURNAL . "(Content, Day) VALUES (?, ?)");
        $post_sql->bind_param("ss", $content, $date);
        $post_sql->execute();
        $post_sql->close();
    }
}


// Update entry

if ( isset($_POST['updatepost']) && ($_POST['newcontent'] !='') ) {
    if ($_SESSION['auth'] == $dbauth) {
	    $newcontent = $_POST['newcontent'];
	    $ID = $_POST['id'];
	    $updatesql = $conn->prepare("UPDATE " . JOURNAL . " SET Content=? WHERE ID=?");
	    $updatesql->bind_param("si", $newcontent, $ID);
	    $updatesql->execute();
	    $updatesql->close();
	}
}


// Journal streak

$jdate = date('Y-m-d');
$check_date = $date;
$step = 0;
$count = 0;

$streak_sql = $connsel->prepare("SELECT Day FROM " . JOURNAL . " ORDER BY ID DESC");
$streak_sql->execute();
$streak_result = mysqli_stmt_get_result($streak_sql);
$rowcount = mysqli_num_rows($streak_result);

while($row = $streak_result->fetch_assoc()) {
	$postdate = date('Y-m-d', strtotime($row["Day"]));

    if ($postdate == $jdate && $step == 0) {
	    $break = 'Keep going!';
    }

    if ( $postdate != $jdate && $step == 0) {
	    $none_today = 'true';
	    $check_date = date('Y-m-d', strtotime($check_date .' -1 day'));
	    $step++;
    } else {
	    $step++;
    }

    if ( $postdate == $check_date && $step > 0 ) {
	    $count++;
	    $check_date = date('Y-m-d', strtotime($check_date .' -1 day'));
    }  else {
	    if ($break == '') {
		    $break = 'Start again.';
	    }
	    break;
    }
}

if ($none_today == 'true') {
    $break = 'Don\'t break it!';
}

if ($break == '.') {
  $break = '';
}

if ($rowcount == 0) {
    $break = "Get started!";
}

if ($_SESSION['auth'] == $dbauth) {
	$streakStr = '<div id="journalstreak">Streak: ' . $count . ' - ' . $break . '</div>' . PHP_EOL;
}

?>

<!DOCTYPE html>
<html lang="en-GB">
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#eeeeee">
	<title>Journal - <?php echo isset($_GET['date']) ? date('M j, Y', strtotime($_GET['date'])) : date('M j, Y'); ?></title>
	<link rel="stylesheet" href="../style.css" type="text/css" media="all">
	<script type="text/javascript" src="../script.js"></script>
</head>

<body>
<?php
	if ($date == $jdate) {
		echo $streakStr;
	}		
?>
    <div id="page" class="hfeed h-feed site">
        <header id="masthead" class="site-header">
            <div class="site-branding">
                <h1 class="site-title">
                    <a href="<?php echo BASE_URL; ?>" rel="home">
                        <span class="p-name">Journal</span>
                    </a>
                </h1>
            </div>
        </header>


		<div id="primary" class="content-area">
			<main id="main" class="site-main today-container">

<?php
	echo '<h3 style="margin-bottom: 20px; cursor: pointer;" class="dateSpan" onclick="toggleUpdate()" accesskey="e">' . $day . '/' . $month . '/' . $year . '</h3>';
	
	$journal_sql = $connsel->prepare("SELECT ID, Content, Day FROM " . JOURNAL . " WHERE DAY=? ORDER BY ID DESC");
	$journal_sql->bind_param("s", $dbdate);
	$journal_sql->execute();
	$journal_result = mysqli_stmt_get_result($journal_sql);
	while ($row = $journal_result->fetch_assoc()) {
		$ID = $row["ID"];
		$day = $row["Day"];
		$content = $row["Content"];
		$journal_sql->close();
		$raw_content = $content;
		$content = '@@ ' . $content;
		$content = filters($content);
		
		$Parsedown = new ParsedownExtra();
		$content = $Parsedown->text($content);
		
		$content = str_replace('@@', '<a><span style=" margin-right: 10px;">#</span></a>', $content);
	
		if ($day == $date) {
			$post = true;
			echo '<article id="post">';		
			echo '<div class="section">';
			echo '<div id="entry" class="entry-content e-content">';
			echo $content;
			echo '</div>'; // entry-content
			echo '</div>'; // section
			echo '</article>';
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

			break;
		}  else {
			$post = false;
		}
	}
?>



<?php	
	if ( $post == false ) {
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

	<nav class="navigation paging-navigation">
		<div class="nav-links">

<?php
if ($date != '2020-12-15') {
echo '<div class="nav-previous"><a href="/journal/?date=' . $prev_date . '"><<</a></div>';
}
if ($date != date('Y-m-d')) {
	if ($date == date('Y-m-d', strtotime($today .' -1 day'))) {
		echo '<div class="nav-next"><a href="/journal/">>></a></div>';
	} else {
		echo '<div class="nav-next"><a href="/journal/?date=' . $next_date . '">>></a></div>';
	}
}
?>

			</div><!-- .nav-links -->
	</nav><!-- .navigation -->

<?php
if ($date != date('Y-m-d')) {
?>
	<div class="linksDiv day-links"><a href="/journal/">Today</a></div>
<?php } else { ?>
  <div class="linksDiv day-links">&nbsp;</div>
<?php } ?>

	<script src="../jquery-3.6.0.min.js"></script>
	
	        <script>
	        
            $(document).ready(function(){
  				$("#submit").click(function(e){
                    //Stop the form from submitting itself to the server.
                    e.preventDefault();
                    
                    var pid = $("#id").val();
                    var content = $("#newcontent").val();
                    $.post("updatejournal.php",
                    	{
                    		updatepost: "true",
                    		newcontent: content,
                    		id: pid,
                    	},
						function(data, status){		
							var XHR = new XMLHttpRequest();
							XHR.open("GET", "updatejournal.php", true);
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
                        if(contentArea.value.slice(-3) != '@@ ') {
                        	contentArea.value = contentArea.value + '\n\n@@ ';
                        }
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
	
<?php
	$pageDesktop = "212";
	$pageMobile = "277";
	include('../footer.php');
?>