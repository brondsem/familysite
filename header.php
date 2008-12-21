<?php
if (!file_exists(dirname(__FILE__).'/config.php')) {
    die("You need to create a config.php.  Please copy config-example.php and modify to suit your environment.");
}
require_once(dirname(__FILE__).'/config.php');
require_once(dirname(__FILE__).'/arc/ARC2.php');
require_once(dirname(__FILE__).'/openid.php');

if (isset($_GET['logout'])) {
    $_SESSION = array();
}

checkOpenID();

$rdf = ARC2::getStore($arc_config);
if (!$rdf->isSetUp()) {
    $rdf->setUp();
    if (sizeof($rdf->getErrors()) > 0) {
        die ("Couldn't set up RDF database:<pre> ". print_r($errors,true)."</pre>");
    }
}

#$rdf->reset(); $rdf->query('BASE <.> LOAD </../brondsema.n3>') or die (print_r($rdf->getErrors(),true));

$prefixes = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/> .
PREFIX vc: <http://www.w3.org/2006/vcard/ns#> .
';



if ($_SESSION['openid'] and !$_SESSION['id']) {
    $q = $prefixes."
        SELECT ?p ?name
        WHERE { ?p foaf:openid <{$_SESSION['openid']}>
            OPTIONAL { ?p foaf:name ?name . }
        }";
    $me = $rdf->query($q, 'row');
    
    # set up a new account
    if ($_SESSION['openid'] == $admin_openid and !isset($me['p'])) {
        $r = $rdf->query($prefixes."INSERT INTO <$rdf_graph_uri> { [ a foaf:Person; foaf:openid <$admin_openid>; foaf:name 'New Admin User - Please change to your name' ] . }");
        if (!$r) die (print_r($rdf->getErrors(),true));
        # requery
        $me = $rdf->query($q, 'row');
    }
    
    if ($me['p']) {
        $_SESSION['id'] = $me['p'];
    }
    if ($me['name']) {
        $_SESSION['name'] = $me['name'];
    } else {
        $_SESSION['name'] = $_SESSION['openid'];
    }
}

?>
<html>
<head>
<title><?php echo $website_title ?></title>

<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.6.0/build/reset-fonts-grids/reset-fonts-grids.css"> 
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.6.0/build/base/base-min.css"> 
<?php if ($_SESSION['openid'] == null) { ?>
    <link rel="stylesheet" href="openid-selector/css/openid.css" />
<?php } ?>

<style type="text/css">
fieldset {
    background: #F6F6F6;
    border: 1px solid lightGrey;
}
fieldset legend {
    margin-left: 1em;
    padding-left: 0.3em;
    padding-right: 0.3em;
}
.input_form {
    margin-left: 3em;
}
.input_form div {
    padding-top: .5em;
    padding-bottom: .5em;
}
.input_form label {
    float:left;
    width:15em;
    text-align:right;
    margin-right:1em;
}
.input_form div * {
    vertical-align: bottom;
}
.input_form fieldset {
    width:40em;
}
</style>
</head>
<body>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.2.6/jquery.min.js"></script>
<div id="doc4" class="yui-t7">
    <div id="hd">
        <?php if ($_SESSION['openid']) { ?>
                <a style="float:right" href="?logout">Log out</a>
        <?php } ?>
        <h1><?php echo $website_title ?></h1>
    </div>
    <div id="bd">
        <div class="yui-g">


<?php

if ($_SESSION['openid'] == null) {
    echo @$error;
    ?>
    <form method="get" id="openid_form">
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
    require_once(dirname(__FILE__).'/footer.php');
    die;
}

if (!$_SESSION['id']) {
    echo "<p>unauthorized {$_SESSION['openid']}</p>";
    require_once(dirname(__FILE__).'/footer.php');
    die;
}
?>