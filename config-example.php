<?php

# this is required for the the initial setup
# all further logins check against openids stored in the database
$admin_openid = 'http://my.openid.site.com/';

# internal RDF use only
$graph_name = 'http://www.myfamily.net/rdf-graph';

$arc_config = array(
  /* db */
  'db_host' => 'mysql.mysite.com',
  'db_name' => 'mydatabase',
  'db_user' => 'myusername',
  'db_pwd' => 'mypassword',
  /* store; this is used fo database table prefixes */
  'store_name' => 'familysite',
  /* parsers */
  'bnode_prefix' => 'bn',
  /* sem html extraction */
  'sem_html_formats' => 'rdfa microformats',
);

?>
