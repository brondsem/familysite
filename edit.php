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
    OPTIONAL { <$id> foaf:homepage ?web . }
    OPTIONAL { <$id> vc:bday ?bday . }
}";

$r = $rdf->query($q, 'row');
if (!$r) die (print_r($rdf->getErrors(),true));

?>
<div>
    <label for="name">Full Name</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($r['name'])?>"/></label>
</div>

<?php
$email = $r['email'] ? $r['email'] : $r['email2'];
$email = str_replace('mailto:','',$email);
?>
<div>
    <label for="email">Email Address</label>
    <input type="text" name="email" value="<?php echo htmlspecialchars($email)?>"/>
</div>
<div>
    <label for="web">Website</label>
    <input type="text" name="web" value="<?php echo htmlspecialchars($r['web'])?>"/>
</div>
<div>
    <label for="bday">Birthdate</label>
    <input type="text" name="bday" value="<?php echo htmlspecialchars($r['bday'])?>"/>
</div>
<div>
    <label>&nbsp;</label>
    <input type="submit" name="submit" value="Save"/>
    <a href="index.php">Cancel</a>
</div>
</form>