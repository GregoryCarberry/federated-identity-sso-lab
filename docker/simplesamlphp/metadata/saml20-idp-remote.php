<?php

$metadata['http://localhost:8080/realms/rivermoor'] = array (
  'entityid' => 'http://localhost:8080/realms/rivermoor',
  'contacts' =>
  array (
  ),
  'metadata-set' => 'saml20-idp-remote',
  'sign.authnrequest' => true,
  'SingleSignOnService' =>
  array (
    0 =>
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
      'Location' => 'http://localhost:8080/realms/rivermoor/protocol/saml',
    ),
    1 =>
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
      'Location' => 'http://localhost:8080/realms/rivermoor/protocol/saml',
    ),
    2 =>
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
      'Location' => 'http://localhost:8080/realms/rivermoor/protocol/saml',
    ),
    3 =>
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
      'Location' => 'http://localhost:8080/realms/rivermoor/protocol/saml',
    ),
  ),
  'SingleLogoutService' =>
  array (
    0 =>
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
      'Location' => 'http://localhost:8080/realms/rivermoor/protocol/saml',
    ),
    1 =>
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
      'Location' => 'http://localhost:8080/realms/rivermoor/protocol/saml',
    ),
    2 =>
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
      'Location' => 'http://localhost:8080/realms/rivermoor/protocol/saml',
    ),
    3 =>
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
      'Location' => 'http://localhost:8080/realms/rivermoor/protocol/saml',
    ),
  ),
  'ArtifactResolutionService' =>
  array (
    0 =>
    array (
      'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
      'Location' => 'http://localhost:8080/realms/rivermoor/protocol/saml/resolve',
      'index' => 0,
    ),
  ),
  'NameIDFormats' =>
  array (
    0 => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',
    1 => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
    2 => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified',
    3 => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress',
  ),
  'keys' =>
  array (
    0 =>
    array (
      'encryption' => false,
      'signing' => true,
      'type' => 'X509Certificate',
      'X509Certificate' => 'MIICoTCCAYkCBgGh0mOCPTANBgkqhkiG9w0BAQsFADAUMRIwEAYDVQQDDAlyaXZlcm1vb3IwHhcNMjYxMTEzMDA0OTU5WhcNMzYxMTEzMDA1MTM5WjAUMRIwEAYDVQQDDAlyaXZlcm1vb3IwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDDrxrrr9sr2EAE3qpJz7L8QudhrG/BedRqVfw+IFo+Kak0hCemgrmFSkxjy/B++yPqQ+cYzin2fWWYFinKnZGbvhAXitlyyWuDUTKFrb+o6C/E/DcKBpGvwHG6cX2MBij9fS7xXDISk4Rj8AS/fV7ZR3bHZ21XiG4gqsoj1IiBRQCg1l6QLcQuAzdzhOaX6Wlo5vXqyqyKtkUYeBdqPekHOj0VOLdPTkFWvSLUTtLmAw9vRhIM/8wG1996AjQ7MaJIHOpgSZsc1FbtXEmnQEteqBJgCkT+g22ylhLFhRwFSDFEgSDLW7/rDEZg9cdpleLvpS6HDvKls1/WKxfR+cT5AgMBAAEwDQYJKoZIhvcNAQELBQADggEBAKiLwEDPSgZFcKJXF3Qzafz2/XRLp2BWR70aANTzRGv30kY1wcqIC/UudMkMbFSPOlJ/A4ctdFeVIxQNDF7Lkku1txyDP1kLv55X0F5vDo76PgnJ37IrqAcJOQm9v7CUhb7EcbkYnEzCTkoCJSGcuwQqOLZTzpjs6znyzrTQNz+QX3WSEWYzcMErYmaop21NOstcoVOVD7Vi3xHlsbXhkgEGfp3IH2/SmsH+D8Ycm6A/Z6WBZYZS65QWfej7dyBiILILDRxyitsYj5AsZfiwQWGY5Lons0XJbhP+2AwuV6cSC2+e4i+wqZFfiHBF/UBRCO01DYqXqovoEs/VBZgf/Vc=',
    ),
  ),
);
