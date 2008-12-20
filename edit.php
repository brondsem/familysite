<?php
require_once(dirname(__FILE__).'/header.php');

$id = $_GET['id'];
if ($_SESSION['id'] != $id) {
    die("You are not authorized to edit info for this person");
}

?>
<form method="POST" action="?id=<?php echo $id?>" class="input_form">
<?php

$q = $prefixes."SELECT *
WHERE {
    <$id> a foaf:Person .
    OPTIONAL { <$id> foaf:name ?name . }
    OPTIONAL { <$id> foaf:mbox ?email . }
    OPTIONAL { <$id> vc:email ?email2 . }
}";

$r = $rdf->query($q, 'row');
if (!$r) die (print_r($rdf->getErrors(),true));

?>
<label>Full Name <input type="text" name="foaf:name" value="<?php echo htmlspecialchars($r['name'])?>"/></label>
<label>Email Address <input type="text" name="email" value="<?php echo htmlspecialchars($r['email'] ? $r['email'] : $r['email2'])?>"/></label>
</form>