<?php
require_once(dirname(__FILE__).'/header.php');

$id = $_GET['id'];
if ($_SESSION['id'] != $id) {
    #die("You are not authorized to edit info for this person");
}

?>
<form method="POST" action="?id=<?php echo $id?>" class="input_form">
<?php

$optionals = array(
    'foaf:name ?name',
    'foaf:mbox ?email',
    'vc:email ?email2',
    'foaf:homepage ?web',
    'vc:bday ?bday',
    'foaf:gender ?gender',
);
$q = $prefixes."SELECT *
WHERE {
    <$id> a foaf:Person .";
foreach ($optionals as $p_o) {
    $q .= " OPTIONAL { <$id> $p_o . } ";
}
$q .= "
    OPTIONAL { <$id> vc:homeAdr ?addr .
        ?addr vc:street-address ?street_address . }
    OPTIONAL { <$id> vc:homeAdr ?addr .
        ?addr vc:extended-address ?extended_address . }
    OPTIONAL { <$id> vc:homeAdr ?addr .
        ?addr vc:locality ?locality . }
    OPTIONAL { <$id> vc:homeAdr ?addr .
        ?addr vc:region ?region . }
    OPTIONAL { <$id> vc:homeAdr ?addr .
        ?addr vc:postal-code ?postal_code . }
}";

#echo $q, "<br><br>";

$r = $rdf->query($q, 'row');
if (!$r) die (print_r($rdf->getErrors(),true));
#print_r($r);


$shared_addr_q = $prefixes."SELECT *
    WHERE {
    ?p vc:homeAdr <{$r['addr']}>;
        foaf:name ?name .
    FILTER(?p != <$id>)
    }";
#echo "<pre>", htmlspecialchars($shared_addr_q), "</pre>";
$shared_addr = $rdf->query($shared_addr_q, 'rows');
#var_dump($shared_addr);
#if (!$shared_addr) die (print_r($rdf->getErrors(),true));


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
    <input type="text" name="bday" value="<?php echo htmlspecialchars($r['bday'])?>" style="width:7em"/>
</div>
<div>
    <label>Gender</label>
    <label style="width:auto;float:none"><input type="radio" name="gender" value="female"
        <?php if ($r['gender']=='female') echo "checked='checked'";?>> Female</label>
    <label style="width:auto;float:none"><input type="radio" name="gender" value="male"
        <?php if ($r['gender']=='male') echo "checked='checked'";?>> Male</label>
</div>
<fieldset>
    <legend>Address</legend>
    <div>
        <label for="street-address">Street Address</label>
        <input type="text" name="street-address" value="<?php echo htmlspecialchars($r['street_address'])?>"/>
    </div>
    <div>
        <label for="extended-address">Line 2</label>
        <input type="text" name="extended-address" value="<?php echo htmlspecialchars($r['extended_address'])?>"/>
    </div>
    <div>
        <label for="locality">City</label>
        <input type="text" name="locality" value="<?php echo htmlspecialchars($r['locality'])?>"/>
    </div>
    <div>
        <label for="region">State</label>
        <input type="text" name="region" value="<?php echo htmlspecialchars($r['region'])?>" style="width:2em" maxlength="2"/>
    </div>
    <div>
        <label for="postal-code">Zip</label>
        <input type="text" name="postal-code" value="<?php echo htmlspecialchars($r['postal_code'])?>" style="width:7em;" maxlength="10"/>
    </div>
    <?php if (sizeof($shared_addr) > 0) { ?>
        <div>
            <label style="width:auto">
            <input type="checkbox" value="1" name="update_shared_addr" checked="checked"/>
            Also update address for:
            <?php
            $names = array();
            foreach ($shared_addr as $addr) {
                $names[] = $addr['name'];
            }
            echo implode($names,", ");
            ?>
            </label>
        </div>
    <?php } ?>
</fieldset>
<div>
    <label>&nbsp;</label>
    <input type="submit" name="submit" value="Save"/>
    <a href="index.php" style="margin-left:2em">Cancel</a>
</div>
</form>