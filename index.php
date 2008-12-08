<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/arc/ARC2.php');
require_once(dirname(__FILE__).'/openid.php');

if (isset($_GET['logout'])) {
    $_SESSION = array();
}

checkOpenID();

echo @$error;

if ($_SESSION['openid'] == null) {
    ?>
    <form method="get">
        OpenID:
        <input type="text" name="openid_identifier" value="" />
        <input type="submit" name="login" value="login"/>
    </form>
    <?php
    return;
} else if ($_SESSION['openid'] == 'http://brondsema.net/') {
    echo "Welcome, ", $_SESSION['openid'];
    ?> <a href="?logout">Log out</a><?php
}

$store = ARC2::getStore($arc_config);
if (!$store->isSetUp()) {
	$store->setUp();
}

$store->query('BASE <.> LOAD </../brondsema.n3>')
	or die (print_r($store->getErrors(),true));

$q = '
  PREFIX foaf: <http://xmlns.com/foaf/0.1/> .
    SELECT ?person ?name WHERE {
        ?person a foaf:Person ; foaf:name ?name .
	  }
	  ';
	  $r = '';
	  if ($rows = $store->query($q, 'rows')) {
	    foreach ($rows as $row) {
	        $r .= '<li>' . $row['name'] . '</li>';
		  }
		  }

		  echo $r ? '<ul>' . $r . '</ul>' : 'no named persons found';
  
?>
