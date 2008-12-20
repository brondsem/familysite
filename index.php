<?php
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
}

#$rdf->reset(); $rdf->query('BASE <.> LOAD </../brondsema.n3>') or die (print_r($rdf->getErrors(),true));

$prefixes = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/> .
PREFIX vc: <http://www.w3.org/2006/vcard/ns#> .
';

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
} else {
    if (!$_SESSION['id']) {
    
        $me = $rdf->query($prefixes."
            SELECT ?p ?name
            WHERE { ?p foaf:openid <{$_SESSION['openid']}>
                OPTIONAL { ?p foaf:name ?name . }
            }", 'row');
            
        if ($me['p']) {
            $_SESSION['id'] = $me['p'];
        }
        if ($me['name']) {
            $_SESSION['name'] = $me['name'];
        } else {
            $_SESSION['name'] = $_SESSION['openid'];
        }
        if (!$_SESSION['id']) {
            die("<br/>unauthorized {$_SESSION['openid']}");
        }
    }
    
    ?> <a style="float:right" href="?logout">Log out</a><?php
    
    echo "<p>Welcome, ", $_SESSION['name'], "</p>";
    /* TODO: use openid simple reg. info
    echo "<p>"; print_r($_SESSION); echo "</p>";
    */
}


$q = $prefixes.'SELECT ?p ?name ?email ?email2
WHERE {
    ?p a foaf:Person .
    OPTIONAL { ?p foaf:name ?name . }
    OPTIONAL { ?p foaf:mbox ?email . }
    OPTIONAL { ?p vc:email ?email2 . }
}
ORDER BY ?name';
$r = '';
if ($rows = $rdf->query($q, 'rows')) {
    foreach ($rows as $row) {
        $email = ($row['email'] ? $row['email'] : $row['email2']);
        $r .= '<tr><td>';
        if ($row['p'] == $_SESSION['id']) {
            $r .= '<a href="edit.php?id=' . $row['p'] . '">Edit</a>';
        }
        $r .= '</td><td>' . $row['name'] . '</td><td><a href="'.$email.'">' . str_replace('mailto:','',$email) . '</a></td></tr>';
    }
}

echo $r ? '<table>' . $r . '</table>' : 'nobody found';
  
?>
