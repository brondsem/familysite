<?php
require_once(dirname(__FILE__).'/header.php');

$id = @$_GET['id'];
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
    'vc:mobileTel ?mobileTel',
    'vc:homeTel ?homeTel',
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

    function insert($rdf, $s, $p, $o) {
        global $prefixes, $rdf_uri_prefix;
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

        $q = $prefixes . " INSERT INTO <$rdf_uri_prefix/graph> { <$s> $p $o . } ";
        #echo $q;
        $rmod = $rdf->query($q);
        #print_r($rmod);
        if ($rdf->getErrors()) throw new Exception (print_r($rdf->getErrors(),true));
    }
    
    # this deletes all previous occurrences of $s $p _
    function replace($rdf, $s, $p, $o, $old_o) {
        global $prefixes;
        
        $q = $prefixes."DELETE { <$s> $p ?o . }";
        #echo $q;
        $rmod = $rdf->query($q);
        #print_r($rmod);
        if ($rdf->getErrors()) throw new Exception (print_r($rdf->getErrors(),true));
        
        insert($rdf, $s, $p, $o);
    }
    
    
    if ($id == null) {
        # new
        $q = $prefixes . "PREFIX mysql: <http://web-semantics.org/ns/mysql/> 
            SELECT * WHERE {
              ?p a foaf:Person
            FILTER regex(str(?p), '^$rdf_uri_prefix/person/')
            }
            # get largest one; numbers aren't 0-padded
            ORDER by  DESC(mysql:char_length(str(?p))) DESC(str(?p))
            LIMIT 1";
        $row = $rdf->query($q, 'row');
        if ($rdf->getErrors()) throw new Exception (print_r($rdf->getErrors(),true));
        $next_num = str_replace("$rdf_uri_prefix/person/","",$row['p'])+1;
        $id = $rdf_uri_prefix . '/person/' . $next_num;
        
        # TODO: maybe a way to send 'foaf:Person' without it being inserted as a string
        insert($rdf, $id, 'a', '<http://xmlns.com/foaf/0.1/Person>');
        if ($_POST['name']) {
            insert($rdf, $id, 'foaf:name', $_POST['name']);
        }
        if ($_POST['email']) {
            insert($rdf, $id, 'foaf:mbox', '<mailto:'.$_POST['email'].'>');
        }
        if ($_POST['web']) {
            insert($rdf, $id, 'foaf:homepage', '<'.$_POST['web'].'>');
        }
        if ($_POST['bday']) {
            insert($rdf, $id, 'foaf:bday', $_POST['bday'].'^^xs:date');
        }
        if ($_POST['gender']) {
            insert($rdf, $id, 'foaf:gender', $_POST['gender']);
        }
        if ($_POST['homeTel']) {
            insert($rdf, $id, 'foaf:homeTel', '<tel:+1-'.$_POST['homeTel'].'>');
        }
        if ($_POST['mobileTel']) {
            insert($rdf, $id, 'foaf:mobileTel', '<tel:+1-'.$_POST['homeTmobileTelel'].'>');
        }
    } else {
        # add
        
        $r = $rdf->query($q, 'row');
        if ($rdf->getErrors()) throw new Exception (print_r($rdf->getErrors(),true));
        if (!$r) throw new Exception ("nobody found with id $id");
        
        if ($r['name'] != $_POST['name']) {
            replace($rdf, $id, 'foaf:name', $_POST['name'], $r['name']);
            if ($id == $_SESSION['id']) {
                $_SESSION['name'] = $_POST['name'];
            }
        }
        
        $email = $r['email'] ? $r['email'] : $r['email2'];
        $email = str_replace('mailto:','',$email);
        if ($email != $_POST['email']) {
            # TODO: check syntax
            # TODO: check when to do vc:email
            replace($rdf, $id, 'foaf:mbox', '<mailto:'.$_POST['email'].'>', '<mailto:'.$email.'>');
        }
        if ($r['web'] != $_POST['web']) {
            # TODO: check syntax (http://)
            replace($rdf, $id, 'foaf:homepage', '<'.$_POST['web'].'>', '<'.$r['web'].'>');
        }
        if ($r['bday'] != $_POST['bday']) {
            # FIXME types
            replace($rdf, $id, 'foaf:bday', $_POST['bday'].'^^xs:date', $r['bday'].'^^xs:date');
        }
        if ($r['gender'] != $_POST['gender']) {
            replace($rdf, $id, 'foaf:gender', $_POST['gender'], $r['gender']);
        }
        if ($r['homeTel'] != $_POST['homeTel']) {
            # TODO: check syntax
            replace($rdf, $id, 'vc:homeTel', $_POST['homeTel'], '<tel:+1-'.$r['homeTel'].'>');
        }
        if ($r['mobileTel'] != $_POST['mobileTel']) {
            # TODO: check syntax
            replace($rdf, $id, 'vc:mobileTel', $_POST['mobileTel'], '<tel:+1-'.$r['mobileTel'].'>');
        }
    }
    
    # TODO: show success
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
if ($rdf->getErrors()) throw new Exception (print_r($rdf->getErrors(),true));
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
#if (!$shared_addr) throw new Exception (print_r($rdf->getErrors(),true));


?>
<div>
    <label for="name">Full Name</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars(@$r['name'])?>"/></label>
</div>
<div>
    <label>Gender</label>
    <label style="width:auto;float:none"><input type="radio" name="gender" value="female"
        <?php if (@$r['gender']=='female') echo "checked='checked'";?>> Female</label>
    <label style="width:auto;float:none"><input type="radio" name="gender" value="male"
        <?php if (@$r['gender']=='male') echo "checked='checked'";?>> Male</label>
</div>
<div>
    <label for="bday">Birthdate</label>
    <input type="text" name="bday" value="<?php echo htmlspecialchars(@$r['bday'])?>" style="width:7em"/>
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
    <input type="text" name="web" value="<?php echo htmlspecialchars(@$r['web'])?>"/>
</div>
<div>
    <label for="homeTel">Home Phone</label>
    <!-- TODO: suggest format -->
    <input type="text" name="homeTel" value="<?php echo htmlspecialchars(str_replace('tel:','',str_replace('tel:+1-','',@$r['homeTel'])))?>"/>
</div>
<div>
    <label for="mobileTel">Cell Phone</label>
    <!-- TODO: suggest format 616-555-1234 -->
    <input type="text" name="mobileTel" value="<?php echo htmlspecialchars(str_replace('tel:','',str_replace('tel:+1-','',@$r['mobileTel'])))?>"/>
</div>
<fieldset>
    <legend>Home Address</legend>
    <div>
        <label for="street-address">Street Address</label>
        <input type="text" name="street-address" value="<?php echo htmlspecialchars(@$r['street_address'])?>"/>
    </div>
    <div>
        <label for="extended-address">Line 2</label>
        <input type="text" name="extended-address" value="<?php echo htmlspecialchars(@$r['extended_address'])?>"/>
    </div>
    <div>
        <label for="locality">City</label>
        <input type="text" name="locality" value="<?php echo htmlspecialchars(@$r['locality'])?>"/>
    </div>
    <div>
        <label for="region">State</label>
        <input type="text" name="region" value="<?php echo htmlspecialchars(@$r['region'])?>" style="width:2em" maxlength="2"/>
    </div>
    <div>
        <label for="postal-code">Zip</label>
        <input type="text" name="postal-code" value="<?php echo htmlspecialchars(@$r['postal_code'])?>" style="width:7em;" maxlength="10"/>
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