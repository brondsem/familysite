<?php

$path_extra = dirname(__FILE__)."/php-openid";
$path = ini_get('include_path');
$path = $path_extra . PATH_SEPARATOR . $path;
ini_set('include_path', $path);


require_once "Auth/OpenID/Consumer.php";
require_once "Auth/OpenID/FileStore.php";
require_once "Auth/OpenID/SReg.php";


session_start();


function &getOpenIDStore() {
    # FIXME: change, to SQL probably
    $store_path = "/tmp/_php_consumer_test";

    if (!file_exists($store_path) &&
        !mkdir($store_path)) {
        print "Could not create the FileStore directory '$store_path'. ".
            " Please check the effective permissions.";
        exit(0);
    }

    return new Auth_OpenID_FileStore($store_path);
}

function &getOpenIDConsumer() {
    $store = getOpenIDStore();
    $consumer =& new Auth_OpenID_Consumer($store);
    return $consumer;
}

function getHttpScheme() {
    $scheme = 'http';
    if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
        $scheme .= 's';
    }
    return $scheme;
}

function getOpenIDReturnTo() {
    # TODO: make this always the current page
    return sprintf("%s://%s:%s%s",
                   getHttpScheme(), $_SERVER['SERVER_NAME'],
                   $_SERVER['SERVER_PORT'],
                   dirname($_SERVER['PHP_SELF'])."?openid_returning=1");
}

function getOpenIDTrustRoot() {
    # TODO: make this always be the root of the app
    return sprintf("%s://%s:%s%s",
                   getHttpScheme(), $_SERVER['SERVER_NAME'],
                   $_SERVER['SERVER_PORT'],
                   dirname($_SERVER['PHP_SELF']));
}


function checkOpenID() {
    if (isset($_SESSION['openid'])) {
        return;
    }
    
    if (isset($_GET['openid_returning'])) {
        $consumer = getOpenIDConsumer();
        $response = $consumer->complete(getOpenIDReturnTo());

        // Check the response status.
        if ($response->status == Auth_OpenID_CANCEL) {
            $error = 'Verification cancelled.';
            return null;
        } else if ($response->status == Auth_OpenID_FAILURE) {
            $error = "OpenID authentication failed: " . $response->message;
            return null;
        } else if ($response->status == Auth_OpenID_SUCCESS) {
            $openid = $response->getDisplayIdentifier();

            # FIXME: what to do with this?
            if ($response->endpoint->canonicalID) {
                $escaped_canonicalID = htmlentities($response->endpoint->canonicalID);
                $success .= '  (XRI CanonicalID: '.$escaped_canonicalID.') ';
            }

            $sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);

            $sreg = $sreg_resp->contents();

            $_SESSION['openid_sreg'] = @$sreg;
            
            $_SESSION['openid'] = $openid;
        }
    } else if (isset($_GET['openid_identifier'])) {
        $openid = $_GET['openid_identifier'];
    
        $consumer = getOpenIDConsumer();

        // Begin the OpenID authentication process.
        $auth_request = $consumer->begin($openid);

        // No auth request means we can't begin OpenID.
        if (!$auth_request) {
            $error = "Authentication error; not a valid OpenID.";
            return null;
        }

        $sreg_request = Auth_OpenID_SRegRequest::build(
                                        // Required
                                        array(),
                                        // Optional
                                        array('nickname', 'fullname', 'email', 'dob', 'gender', 'postcode', 'country', 'language', 'timezone'));

        if ($sreg_request) {
            $auth_request->addExtension($sreg_request);
        }


        // Redirect the user to the OpenID server for authentication.
        // Store the token for this authentication so we can verify the
        // response.

        // For OpenID 1, send a redirect.  For OpenID 2, use a Javascript
        // form to send a POST request to the server.
        if ($auth_request->shouldSendRedirect()) {
            $redirect_url = $auth_request->redirectURL(getOpenIDTrustRoot(),
                                                    getOpenIDReturnTo());

            // If the redirect URL can't be built, display an error
            // message.
            if (Auth_OpenID::isFailure($redirect_url)) {
                $error = "Could not redirect to server: " . $redirect_url->message;
                return null;
            } else {
                // Send redirect.
                header("Location: ".$redirect_url);
            }
        } else {
            // Generate form markup and render it.
            $form_id = 'openid_message';
            $form_html = $auth_request->htmlMarkup(getOpenIDTrustRoot(), getOpenIDReturnTo(),
                                                false, array('id' => $form_id));

            // Display an error if the form markup couldn't be generated;
            // otherwise, render the HTML.
            if (Auth_OpenID::isFailure($form_html)) {
                $error = "Could not redirect to server: " . $form_html->message;
                return null;
            } else {
                print $form_html;
                exit;
            }
        }
    } else if (isset($_GET['openid_submit'])) {
        $error = "Expected an OpenID URL.";
        return null;
    } else {
        return null;
    }
}

?>
