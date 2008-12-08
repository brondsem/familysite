<?php
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/arc/ARC2.php');
require_once(dirname(__FILE__).'/openid.php');

if (isset($_GET['logout'])) {
    $_SESSION = array();
}

checkOpenID();



?>
<html>
<head>
<title>FamilySite</title>
<?php if ($_SESSION['openid'] == null) { ?>
    <link rel="stylesheet" href="openid-selector/css/openid.css" />
<?php } ?>
</head>
<body>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
<?php

echo @$error;

if ($_SESSION['openid'] == null) {
    ?>
    <form method="get" id="openid_form">
        <!--
        OpenID:
        <input type="text" name="openid_identifier" value="" />
        <input type="submit" name="login" value="login"/>
        -->
        <fieldset>
                <legend>Sign-in or Create New Account</legend>

                <div id="openid_choice">
                        <p>Please click your account provider:</p>
                        <div id="openid_btns"></div>
                        </div>

                        <div id="openid_input_area">
                                <input id="openid_identifier" name="openid_identifier" type="text" value="http://" style="background: url(images/openid.gif) left no-repeat; padding-left:18px;"/>
                                <input id="openid_submit" type="submit" value="Sign-In"/>
                        </div>
                        <noscript>
                        <p>OpenID is service that allows you to log-on to many different websites using a single indentity.
                        Find out <a href="http://openid.net/what/">more about OpenID</a> and <a href="http://openid.net/get/">how to get an OpenID enabled account</a>.</p>
                        </noscript>
        </fieldset>    
    </form>
    <script type="text/javascript" src="openid-selector/js/openid-jquery.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            openid.img_path='openid-selector/images/';
            openid.init('openid_identifier');
        });
    </script>
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
