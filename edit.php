<?php
require_once(dirname(__FILE__).'/header.php');

$id = $_GET['id'];
if ($_SESSION['id'] != $id) {
    #die("You are not authorized to edit info for this person");
}

$optionals = array(
    'foaf:openid ?openid',
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


if ($_POST) {

    $r = $rdf->query($q, 'row');
    if (!$r) die (print_r($rdf->getErrors(),true));
    
    function insert($rdf, $s, $s_type, $p, $o) {
        global $prefixes;
        # a lot of rigamarole to insert
        # full graph name is required
        # since its a bnode, it's prone to make a new one
        if (substr($o,0,1) == '<' and substr($o,-1) == '>') {
            # url
            # TODO: check escaping
        } else {
            # literal
            $o = "'" . str_replace("'","\\'",$o) . "'";
        }
        
        $q = $prefixes.
        "INSERT INTO <$rdf_graph_uri>
        { ?s $p $o . }
        WHERE {
        ?s a $s_type .
        FILTER(?s = <$s>)
        }";
        #echo $q;
        $rmod = $rdf->query($q);
        #print_r($rmod);
        if (!$rmod) die (print_r($rdf->getErrors(),true));
    }
    
    # this deletes all previous occurrences of $s $p _
    function replace($rdf, $s, $s_type, $p, $o, $old_o) {
        global $prefixes;
        
        $q = $prefixes."DELETE { <$s> $p ?o . }";
        #echo $q;
        $rmod = $rdf->query($q);
        #print_r($rmod);
        if (!$rmod) die (print_r($rdf->getErrors(),true));
        
        insert($rdf, $s, $s_type, $p, $o);
    }
    
    if ($r['name'] != $_POST['name']) {
        replace($rdf, $id, 'foaf:Person', 'foaf:name', $_POST['name'], $r['name']);
        if ($id == $_SESSION['id']) {
            $_SESSION['name'] = $_POST['name'];
        }
    }
    
    $email = $r['email'] ? $r['email'] : $r['email2'];
    $email = str_replace('mailto:','',$email);
    if ($email != $_POST['email']) {
        # TODO: check when to do vc:email
        replace($rdf, $id, 'foaf:Person', 'foaf:mbox', '<mailto:'.$_POST['email'].'>', '<mailto:'.$email.'>');
    }
    if ($r['web'] != $_POST['web']) {
        replace($rdf, $id, 'foaf:Person', 'foaf:homepage', '<'.$_POST['web'].'>', '<'.$r['web'].'>');
    }
    if ($r['bday'] != $_POST['bday']) {
        # FIXME types
        replace($rdf, $id, 'foaf:Person', 'foaf:bday', $_POST['bday'].'^^xs:date', $r['bday'].'^^xs:date');
    }
    if ($r['gender'] != $_POST['gender']) {
        replace($rdf, $id, 'foaf:Person', 'foaf:gender', $_POST['gender'], $r['gender']);
    }
}



?>
<form method="POST" action="?id=<?php echo $id?>" class="input_form">
<?php
/*
echo "<pre>";
print_r($rdf->query($prefixes."DESCRIBE <$id>",'raw'));
echo "</pre>";
*/

$r = $rdf->query($q, 'row');
if ($rdf->getErrors()) die (print_r($rdf->getErrors(),true));
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
    <legend>Home Address</legend>
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

<?php require_once(dirname(__FILE__).'/footer.php');?>