<?php

// Initialise session
session_start();

define('APP_RAN', '');

require_once('config.php');

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


$year = date('Y');
$month = date('m');
$target_dir = "uploads/" . $year . "/";

if(!file_exists("uploads")) {
    mkdir("uploads");
}
opendir("uploads/");
if(!file_exists($target_dir)) {
    mkdir($target_dir);
}
opendir($target_dir);
$target_dir = "uploads/" . $year . "/" . $month . "/";
if(!file_exists($target_dir)) {
    mkdir($target_dir);
}

$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
  if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    //echo "The file ". htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])). " has been uploaded.";
    $ext_file_path = BASE_URL . '/' . $target_file;
  } else {
    echo "Sorry, there was an error uploading your file.";
  }
  
  $upload_path = '<input type="submit" id="copy_button" style="border-radius: 40px; border: 1px solid #aaa; color: #aaa; position: absolute; top: 0px; float: left; left: 0px; padding: 0.3em 1.5em; font-size: 12px; font-family: Arial;" onclick="do_copy();" value="Copy file path"><input style="float: right; border: none; position: absolute; top: 50px; left: 150px; font-size: 12px; font-family: Arial; word-wrap: break-word;" id="filepath" value="' . $ext_file_path . '">';
  echo $upload_path;
  echo "<style>.button, #upload {display: none;}</style>";
    
}


?>

<html>
<head>
	<link rel="stylesheet" href="/style.css" type="text/css" media="all">
</head>
<body>

<form action="" method="post" enctype="multipart/form-data" style="width: 100%; margin-top: 10px;" onsubmit="document.getElementById('upload').value='Uploading'; document.getElementById('upload').style.border='0px'; document.getElementById('upload').style.display='none'; document.getElementById('is_uploaded').style.display='none'; document.getElementById('is_uploaded').style.fontSize='12px'; document.getElementById('is_uploaded').style.paddingTop='7px'; document.getElementById('choose_button').innerText='Uploading...';">
<form action="" method="post" enctype="multipart/form-data" style="width: 100%; margin-top: 10px;" onsubmit="document.getElementById('upload').value='Uploading';">
      <label id="choose_button" class="button" for="fileToUpload" style="color: #999; opacity: 1;">Choose File</label><span id="is_uploaded" style="display:none; font-size: 18px; position: relative; left: 110px; top: -10px;">✓</span><input onchange="uploaded();" style="display: none; opacity: 0; color: #999; width: 30%;" type="file" name="fileToUpload" id="fileToUpload" required></span><input id="upload" type="submit" value="Upload" name="submit" style="display: none; position: absolute; top: 0px; right: 0px;">
</form>


    <style>
        #is_uploaded {
	        color: #999;
        }

        @media screen and (prefers-color-scheme: dark) {
	        #is_uploaded {
	            color: #ccc;
            }
        }
    </style>

<script>
    function do_copy() {
        var copyText = document.getElementById("filepath");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        document.getElementById("copy_button").value = "Copied";
        window.getSelection().removeAllRanges();
        setTimeout(function () { location.href="/uploader.php"; }, 2000);
    }
    
    function uploaded() {
        document.getElementById("is_uploaded").style.display = "block";
        document.getElementById("upload").style.display = "block";
    }
</script>

</body>
</html>