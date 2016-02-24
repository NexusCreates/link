<?php
/**
 * TO RE-RUN THE INSTALL SCRIPT
 * Delete the config.php inside of the 'inc' folder then load install.php in your browser.
 */

$filename = "inc/config.php";
if(file_exists($filename)) {
    //If the site has already been installed, don't reinstall!
    header('Location: index.php');
    exit();
}

if($_POST) {
    $mysql_host = $_POST['db_host'];
    $mysql_user = $_POST['db_user'];
    $mysql_pass = $_POST['db_pass'];
    $mysql_db = $_POST['db'];
    $site_name = $_POST['name'];
    $domain = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $rewrite = (boolean)isset($_POST['rewrite']);
    $redirect = (boolean)isset($_POST['redirect']);
    $html = $_POST['html'];
    $delay = $_POST['delay'];
    $connection = mysqli_connect($mysql_host, $mysql_user, $mysql_pass, $mysql_db) or die('Failed to connect to MySQL Database: '.mysqli_connect_error().'<br /> Refresh the page and rerun the installer.');

    /* Setup the MySQL database */



    $query = "CREATE TABLE IF NOT EXISTS urls (id INT NOT NULL AUTO_INCREMENT, url VARCHAR(256), short VARCHAR(8), PRIMARY KEY(id))";
    mysqli_query($connection, $query);
    
    $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
    $domain = $protocol.str_replace("index.php", "", $domain);

    /* Write new Configuration file */

    $config = "
<?php

/* Start Configuration. Edit the variables below or use the automatic installer. */

define('MYSQL_HOST', '{$mysql_host}'); //IP address of the MySQL server. (Usually localhost)
define('MYSQL_USER', '{$mysql_user}'); //Username to log into the MySQL server.
define('MYSQL_PASS', '{$mysql_pass}'); //Password to authenticate with.
define('MYSQL_DB', '{$mysql_db}'); //DataBase to setup the URL shortener in.
define('SITE_NAME', '{$site_name}'); //The name to display on the site.
define('DOMAIN', '{$domain}'); //Location of the index page
define('REWRITE', '{$rewrite}'); //1 = true ; Wether or not mod_rewrite is enabled. Used for properly formatting urls
define('DONATE_URL', ''); //Optional Donation URL. Donate link will only appear if this is set.
define('AUTO_REDIRECT', '{$redirect}'); //Automatically redirect? 1 = redirect, 0 = Show landing page (for ads, etc.)
define('REDIRECT_DELAY', '{$delay}'); //Number of seconds to wait before automatically redirecting.
define('LANDING_HTML', \"{$html}\"); //HTML to display on the landing page of shortened URLs.

/* End of Configuration. Do not edit below this line */
    ";

    if(is_writable('inc/')) {
        file_put_contents($filename, $config);
        header('Location: index.php');
        exit();
    }else {
        echo 'The \'inc\' directory is not writable by PHP. Chmod the directory for guest write and retry.';
        exit();
    }

}

?>
<html>
    <head>
        <title>Auto Installer</title>
        <style>
            head, body {
                padding: 0;
                margin: 0;
                width: 100%;
            }
        </style>
    </head>
    <body>
        <div class="container" style="width: 25vw; text-align: left; margin: 40px auto 0 auto;">
            <h2>URL Shortener Auto Installer</h2>
            <form method="post">
                <table>
                    <tr>
                        <td><b>MySQL Host</b></td>
                        <td><input type="text" name="db_host" autocomplete="off"/></td>
                    </tr>
                    <tr>
                        <td><b>MySQL User</b></td>
                        <td><input type="text" name="db_user" autocomplete="off"/></td>
                    </tr>
                    <tr>
                        <td><b>MySQL Password</b></td>
                        <td><input type="password" name="db_pass" /></td>
                    </tr>
                    <tr>
                        <td><b>MySQL Database</b></td>
                        <td><input type="text" name="db" autocomplete="off"/></td>
                    </tr>
                    <tr>
                        <td><b>Site Name</b></td>
                        <td><input type="text" name="name" autocomplete="off"/></td>
                    </tr>
                    <tr>
                        <td><b>Redirect Delay<br/>Set to 0 for none</b></td>
                        <td><input type="text" name="delay" autocomplete="off" value="0"/></td>
                    </tr>
                    <tr>
                        <td><b>Landing Page HTML</b></td>
                        <td><textarea name="html" placeholder="HTML displayed on the landing page for URLs" style="width: 100%; height: 100px;"></textarea></td>
                    </tr>
                    <tr>
                        <td><b>mod_rewrite enabled</b><br />Check box if you plan<br />on installing the rewrite<br />rules for Apache or Nginx.</td>
                        <td style="text-align: right;"><input type="checkbox" name="rewrite" /></td>
                    </tr>
                    <tr>
                        <td><b>auto redirect enabled</b><br />Check box if you want<br />shortened urls to bypass<br />the landing page.</td>
                        <td style="text-align: right;"><input type="checkbox" name="redirect" /></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td style="text-align: right;"><input type="submit"></td>
                    </tr>
                </table>
            </form>
        </div>
    </body>
</html>