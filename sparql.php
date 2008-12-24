<?php
if (!file_exists(dirname(__FILE__).'/config.php')) {
    die("You need to create a config.php.  Please copy config-example.php and modify to suit your environment.");
}
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/arc/ARC2.php');

# TODO: also support oauth
session_start();
if (!$_SESSION['id']) {
    echo "<p>unauthorized.  Please login via the home page.</p>";
    die;
}


$ep = ARC2::getStoreEndpoint($arc_config);
$ep->go();

?>