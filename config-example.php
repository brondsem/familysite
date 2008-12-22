<?php

# this is required for the the initial setup
# all further logins check against openids stored in the database
$admin_openid = 'http://my.openid.site.com/';

$website_title = "Our Family Website";

# internal RDF use only.  Recommended to be your website address, followed by '/rdf-data'.  If your website address changes, though, it's best to keep this the same forever.
$rdf_uri_prefix = 'http://www.myfamily.net/rdf-data';

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
  /* endpoint */
  'endpoint_features' => array(
    'select', 'construct', 'ask', 'describe'
  ),
  'endpoint_max_limit' => 500, /* optional */
);

?>
