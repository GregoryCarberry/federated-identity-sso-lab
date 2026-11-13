<?php

/**
 * Rivermoor Federated Identity Lab
 *
 * SimpleSAMLphp Service Provider authentication sources.
 */

$config = [
    'admin' => [
        'core:AdminPassword',
    ],

    'default-sp' => [
        'saml:SP',

        // Unique identifier for our Service Provider.
        'entityID' => 'http://localhost:8081/simplesaml/module.php/saml/sp/metadata.php/default-sp',

        // Keycloak IdP entity ID. To be verified against Keycloak metadata.
        'idp' => 'http://localhost:8080/realms/rivermoor',

        // No IdP discovery service required for this lab.
        'discoURL' => null,

        // Signing credentials, relative to SimpleSAMLphp's cert directory.
        'privatekey' => 'sp-signing.key',
        'certificate' => 'sp-signing.crt',

        // Sign outgoing SAML authentication requests.
        'sign.authnrequest' => true,
    ],
];
