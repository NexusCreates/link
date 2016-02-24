<?php
$ad1 = '<a href="http://www.mc-market.org/threads/39341/" target="_blank"><img style="width:468px;height:60px;" src="img/ad1.png" alt="MC Auctions" title="MC Auctions"></a>';
$ad2 = '<a href="https://nexus-node.com/" target="_blank"><img style="width:468px;height:60px;" src="img/ad2.png" alt="Nexus-Node" title="Nexus-Node"></a>';
$ad3 = '<a href="https://twitter.com/NexusCreates" target="_blank"><img style="width:468px;height:60px;" src="img/ad3.png" alt="NexusCreates" title="NexusCreates"></a>';
$ad4 = '<a href="mailto:ads@nexus-node.com?Subject=Nexus-Node%20Ad%20Posting" target="_blank"><img style="width:468px;height:60px;" src="img/ad4.png" alt="Nexus-Node Ads" title="Nexus-Node Ads"></a>';
$ad5 = '<a target="_blank" href="http://payments.reliablesite.net/aff.php?aff=543"><img style="width:468px;height:60px;" src="https://payments.reliablesite.net/images/banners/high_728x90.jpg"></a>';
$ads = array($ad1, $ad2, $ad3, $ad4, $ad5);
shuffle($ads);

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
        $short = substr(md5(uniqid()),0,5);
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
    <link rel="apple-touch-icon" sizes="57x57" href="/apple-touch-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="/apple-touch-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="/apple-touch-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="/apple-touch-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120" href="/apple-touch-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-180x180.png">
	<link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32">
	<link rel="icon" type="image/png" href="/favicon-194x194.png" sizes="194x194">
	<link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96">
	<link rel="icon" type="image/png" href="/android-chrome-192x192.png" sizes="192x192">
	<link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16">
	<link rel="manifest" href="/manifest.json">
	<meta name="apple-mobile-web-app-title" content="Nexus-Node">
	<meta name="application-name" content="Nexus-Node">
	<meta name="msapplication-TileColor" content="#2d89ef">
	<meta name="msapplication-TileImage" content="/mstile-144x144.png">
	<meta name="theme-color" content="#ffffff">
<title>Nexus-Node | Redirecting to <?php echo $url; ?></title>

    <style>
        @import url(https://fonts.googleapis.com/css?family=Open+Sans);

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
            Redirecting to <?php echo $url; ?>
        </span>
        <a href="<?php echo DOMAIN; ?>">Shorten a URL</a>
        <?php if(DONATE_URL != '')
        { ?><a href="<?php echo DONATE_URL; ?>">Donate</a><?php }?>
    </div>
    <div class="body">
        <p id="redirect"></p>
		<br />
		<?php print $ads[0] ?>
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
<footer>
<center><p><br />&copy; Copyright Nexus-Node <?php echo date("Y") ?></p></center>
</footer>
</html>
<?php
}else {
    //Display Home Page
?>

    <html>
	<link rel="apple-touch-icon" sizes="57x57" href="/apple-touch-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="/apple-touch-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="/apple-touch-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="/apple-touch-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120" href="/apple-touch-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="152x152" href="/apple-touch-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon-180x180.png">
	<link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32">
	<link rel="icon" type="image/png" href="/favicon-194x194.png" sizes="194x194">
	<link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96">
	<link rel="icon" type="image/png" href="/android-chrome-192x192.png" sizes="192x192">
	<link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16">
	<link rel="manifest" href="/manifest.json">
	<meta name="apple-mobile-web-app-title" content="Nexus-Node">
	<meta name="application-name" content="Nexus-Node">
	<meta name="msapplication-TileColor" content="#2d89ef">
	<meta name="msapplication-TileImage" content="/mstile-144x144.png">
	<meta name="theme-color" content="#ffffff">
    <head>
	<title>Nexus-Node | URL Shortner</title>

        <style>
            @import url(https://fonts.googleapis.com/css?family=Open+Sans);

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
		<br />
		<?php print $ads[0] ?>
    </div>
    </body>
    <footer>
    <center><p><br />&copy; Copyright Nexus-Node <?php echo date("Y") ?></p></center>
    </footer>
    </html>
    <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
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