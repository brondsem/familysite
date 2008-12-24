<?php
if (!file_exists(dirname(__FILE__).'/config.php')) {
    die("You need to create a config.php.  Please copy config-example.php and modify to suit your environment.");
}
require_once(dirname(__FILE__).'/config.php');

require_once(dirname(__FILE__).'/arc/ARC2.php');
require_once(dirname(__FILE__).'/openid.php');

session_start();

$prefixes = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>
PREFIX vc: <http://www.w3.org/2006/vcard/ns#>
';

$rdf = ARC2::getStore($arc_config);
if (!$rdf->isSetUp()) {
    $rdf->setUp();
    if (sizeof($rdf->getErrors()) > 0) {
        die ("Couldn't set up RDF database:<pre> ". print_r($errors,true)."</pre>");
    }
}

function get_next_uri($rdf, $type) {
    global $prefixes, $rdf_uri_prefix;
    $q = $prefixes . "PREFIX mysql: <http://web-semantics.org/ns/mysql/>
            SELECT * WHERE {
              ?p a ?anything .
            FILTER regex(str(?p), '^$rdf_uri_prefix/$type/')
            }
            # get largest one; numbers aren't 0-padded
            ORDER by  DESC(mysql:char_length(str(?p))) DESC(str(?p))
            LIMIT 1";
    #echo htmlspecialchars($q);
    $row = $rdf->query($q, 'row');
    #print_r($row);
    if ($rdf->getErrors()) throw new Exception (print_r($rdf->getErrors(),true));
    $next_num = str_replace("$rdf_uri_prefix/$type/","",$row['p'])+1;
    return "$rdf_uri_prefix/$type/$next_num";
}
?>