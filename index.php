<?php
require_once(dirname(__FILE__).'/header.php');
    
echo "<p>Welcome, ", $_SESSION['name'], "</p>";
/* TODO: use openid simple reg. info
echo "<p>"; print_r($_SESSION); echo "</p>";
*/


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
