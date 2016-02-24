<?php

/* Run Installer if Needed */
$filename = "inc/config.php";
if(!file_exists($filename)) {
    include('install.php');
    exit();
}

require_once 'inc/config.php';

if($_GET && isset($_GET['url'])) {
    $url = $_GET['url'];
    if (strpos($url, DOMAIN) !== false) {
        echo json_encode(array("status" => "okay", "url" => $url));
        exit;
    }
    
    if (true) {
        if (strpos($url, "http://") === false && strpos($url, "https://") === false) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://'.$url);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1000); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $d = curl_exec($ch);
            if (curl_errno($ch) > 0) {
                $url = "http://".$url;
            }else {
                $url = "https://".$url;
            }   
            curl_close($ch);
        }
        
        $con = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
        $stmt = $con->prepare("SELECT short FROM urls WHERE url=?");
        $stmt->bind_param('s', $url);
        $stmt->execute();
        $stmt->bind_result($short);
        if($stmt->fetch()) {
            //old url
            if(REWRITE) {
                $short = DOMAIN.$short;
            }else {
                $short = DOMAIN.'?id='.$short;
            }
            echo json_encode(array("status" => "okay", "url" => $short));
            exit;
        }else {
            //new url
            $stmt = $con -> prepare('INSERT INTO urls (url, short) VALUES(?, ?)');
            $short = genShort($con);
            $stmt->bind_param('ss', $url, $short);
            $stmt->execute();
            if(REWRITE) {
                $short = DOMAIN.$short;
            }else {
                $short = DOMAIN.'?id='.$short;
            }
            echo json_encode(array("status" => "okay", "url" => $short));
            exit;
        }
        echo mysqli_error($con);
        exit; 
    }else {
        echo json_encode(array("status" => "error", "error" => "That is not a valid URL"));
        exit;
    }
}

function genShort($con) {
    while(true) {
        $short = substr(md5(uniqid()),0,8);
        $res = mysqli_query($con, "SELECT * FROM urls WHERE short='".$short."'");
        if(mysqli_num_rows($res) == 0) { 
            return $short;
        }
    }
}

if(isset($_GET['id'])) {
    $con = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
    $stmt = $con->prepare("SELECT url FROM urls WHERE short=?");
    $stmt->bind_param('s', $_GET['id']);
    $stmt->execute();
    $stmt->bind_result($short);
    if($stmt->fetch()) {
        $url = $short;
    }else {
        header('location: ./');
    }
    if(AUTO_REDIRECT) {
        header('location: '.$url);
        exit();
    }
?>
<html>
<head>

    <style>
        @import url(http://fonts.googleapis.com/css?family=Open+Sans);

        body,head {
            font-family: 'Open Sans', sans-serif;
            padding: 0;
            margin: 0;
            background: #cfcfcf;
        }
        .head {

            width: 100%;
            height: 60px;
            background: #252525;
            font-size:  20px;
            line-height: 60px;
            color: #FFFFFF;
        }

        .head a {
            background: #FFF;
            display: inline-block;
            width: 200px;
            height: 40px;
            color: #000;
            line-height: 40px;
            text-align: center;
            text-decoration: none;
            border: 2px solid #2db5ed;
            float: right;
            margin-right: 15px;
            margin-top: 8px;
        }

        .head a:not(:first-of-type) {
            font-size: 14px;
            border: none;
            margin-top: 10px;
            width: auto;
            padding-left: 10px;
            padding-right: 10px;
        }

        .head a:hover {
            background: #fafafa;
        }

        .body {
            width: 70%;
            margin: auto;
            text-align: center;
        }

        .body img{
            width: 100%;
        }
    </style>
</head>
<body onload="startCountdown()">
    <div class="head">
        <span style="padding-left: 15px;">
            You are about to be forwarded to <?php echo $url; ?>
        </span>
        <a href="<?php echo $url; ?>">Proceed To URL</a>
        <a href="<?php echo DOMAIN; ?>">Shorten URLS</a>
        <?php if(DONATE_URL != '')
        { ?><a href="<?php echo DONATE_URL; ?>">Donate</a><?php }?>
    </div>
    <div class="body">
        <p id="redirect"></p>
        <?php echo LANDING_HTML; ?>
        <p style="font-size: 70%;">Continue to the website at your own risk.</p>
    </div>
    <?php
        if(REDIRECT_DELAY > 0) {
            ?>
            <script>
                var seconds = <?php echo REDIRECT_DELAY ?>;
                
                function doCountdown() {
                    if(seconds == 0) {
                        document.location = "<?php echo $url; ?>";
                        return;
                    }
                    var d = document.getElementById('redirect');
                    d.innerHTML = "Redirecting in " + seconds + " seconds.";
                    seconds -= 1;
                    setTimeout("doCountdown()",1000)
                }
                
                function startCountdown() {
                    doCountdown();
                }
            </script>
            <?php
        }
    ?>
</body>
</html>
<?php
}else {
    //Display Home Page
?>

    <html>
    <head>

        <style>
            @import url(http://fonts.googleapis.com/css?family=Open+Sans);

            body,head {
                font-family: 'Open Sans', sans-serif;
                padding: 0;
                margin: 0;
                background: #cfcfcf;
            }
            .head {
                width: 100%;
                height: 60px;
                background: #252525;
                font-size:  20px;
                line-height: 60px;
                color: #FFFFFF;
            }

            .head a {
                background: #2db5ed;
                display: inline-block;
                width: 200px;
                height: 40px;
                color: #FFF ;
                line-height: 40px;
                text-align: center;
                text-decoration: none;
                float: right;
                margin-right: 15px;
                margin-top: 10px;
            }

            .head a:hover {
                background: #29a5d8;
            }

            .body {
                width: 100%;
                margin: auto;
                text-align: center;
            }

            .body img{
                width: 100%;
            }

            input[type="submit"]:hover {
                background: #4aa2f9;
                cursor: pointer;
            }
            
            div.container {
                margin-top: 80px;
                width: 60%; 
                margin-left: auto; 
                margin-right: auto;   
            }
            
            @media screen and (max-width: 1000px) {
                div.container {
                    width: 80%;   
                }
            }
            
            @media screen and (max-width: 500px) {
                div.container {
                    width: 100%;
                }
            }
        </style>
    </head>
    <body>
    <div class="head">
        <span style="padding-left: 15px;">
            <?php echo SITE_NAME; ?>
        </span>
        <?php if(DONATE_URL != '') { ?><a href="<?php echo DONATE_URL; ?>">Donate</a><?php }?>
    </div>
    <div class="body">
        <div class="container">
            <form method="post" style="font-size: 0px;">
                <input type="text" autocomplete="off" id="url" style="width: 85%; height: 40px; border: none; margin: 0; padding-left: 10px; padding-right: 10px; outline: none;" placeholder="Paste URL here.." name="url">
                <input type="submit" id="sub" value="Shorten" style="width: 15%; height: 40px; background: #4B8DFA; border: none; color: #FFF;">
            </form>
        </div>
    </div>
    </body>
    </html>
    <script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>
    <script>
        $('form').submit(function(e) {
        e.preventDefault();
        if($('#url').val() != "") {
          $.getJSON("<?php echo DOMAIN; ?>", {
            url: $('#url').val()
          }).done(function(data) {
            if(data.status == "okay") {
                $('#url').val(data.url);
                $('#url').select();
            }else {
                $('#url').val(data.error);
                $('#url').select();
            }
          });
        }
      });
    </script>

<?php
}
?>