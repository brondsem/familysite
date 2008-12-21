<?php
require_once(dirname(__FILE__).'/header.php');
    
echo "<p>Welcome, ", $_SESSION['name'], "</p>";
/* TODO: use openid simple reg. info
echo "<p>"; print_r($_SESSION); echo "</p>";
*/

?>
<h2>Family Members:</h2>
<p><a href="edit.php?add">Add someone</a></p>
<?php
$q = $prefixes.'SELECT ?p ?name ?email ?email2
WHERE {
    ?p a foaf:Person .
    OPTIONAL { ?p foaf:name ?name . }
    OPTIONAL { ?p foaf:mbox ?email . }
    OPTIONAL { ?p vc:email ?email2 . }
}
ORDER BY ?name';

if ($rows = $rdf->query($q, 'rows')) {
    ?>
    <table>
    <?php
    foreach ($rows as $row) {
        $email = ($row['email'] ? $row['email'] : $row['email2']);
        echo '<tr><td><a ';
        if ($row['p'] != $_SESSION['id']) {
            echo 'style="color:gray" ';
        }
        echo 'href="edit.php?id=' . $row['p'] . '">Edit</a>';
        echo '</td><td>';
        if ($row['p'] == $_SESSION['id']) {
            echo "<strong>";
        }
        echo $row['name'];
        if ($row['p'] == $_SESSION['id']) {
            echo "</strong>";
        }
        echo '</td><td><a href="'.$email.'">' . str_replace('mailto:','',$email) . '</a></td></tr>';
    }
    ?>
    </table>
    <?php
}

require_once(dirname(__FILE__).'/footer.php');
?>