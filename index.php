<?php

require 'vendor/autoload.php';

session_start();

$provider = new Stevenmaguire\OAuth2\Client\Provider\Keycloak([
    'authServerUrl'             => 'http://localhost:8085/auth',
    'realm'                     => 'demo-realm',
    'clientId'                  => 'demo-app',
    'clientSecret'              => 'f554f58e-9827-4aa7-9b73-a91ae01df0f8',
    'redirectUri'               => 'http://localhost:8070',
    'encryptionAlgorithm'       => null,
    'encryptionKey'             => null,
    'encryptionKeyPath'         => null
]);

if (!isset($_GET['code'])) {
    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']);
    exit('Invalid state, make sure HTTP sessions are enabled.');
} else {
    // Try to get an access token (using the authorization coe grant)
    try {
        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);
    } catch (Exception $e) {
        exit('Failed to get access token: '.$e->getMessage());
    }

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $user = $provider->getResourceOwner($token);
        // Use these details to create a new profile
        printf('Hello %s!\n<br>', $user->getName());
        printf($_SESSION);

    } catch (Exception $e) {
        exit('Failed to get resource owner: '.$e->getMessage());
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
