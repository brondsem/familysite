<?php
require_once(dirname(__FILE__).'/common.php');

# TODO: also support oauth
if (!$_SESSION['id']) {
    echo "<p>unauthorized.  Please login via the home page.</p>";
    die;
}


$ep = ARC2::getStoreEndpoint($arc_config);
$ep->go();

?>