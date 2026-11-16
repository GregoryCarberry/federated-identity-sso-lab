# Rivermoor Federated Identity Lab — Setup Guide

## Overview

This guide documents the deployment and configuration of the Rivermoor Federated Identity & SSO Troubleshooting Lab.

The environment uses:

| Component | Technology |
|---|---|
| Identity Provider (IdP) | Keycloak 26.8.2 |
| Service Provider (SP) | SimpleSAMLphp 2.5.4 |
| Application runtime | PHP 8.3 / Apache |
| Container deployment | Docker Compose |
| Development environment | Ubuntu on WSL2 / Docker Desktop |
| Cryptographic tools | OpenSSL / xmlsec1 |

The lab uses a fictional identity provider, `rivermoor`, and a fictional test account, `alice.student`.

**Implementation status: Basic SAML 2.0 federation operational and verified on 14–16 November 2026.**

Successful SP-initiated authentication, session establishment, attribute retrieval, SAML message inspection and independent XML signature verification have been demonstrated.

The repository is not yet a fully automated, clean-install deployment. Some Keycloak configuration and certificate-trust operations must be performed manually.

---

## 1. Prerequisites

The original development environment used:

- Ubuntu running through Windows Subsystem for Linux (WSL2).
- Docker Desktop integrated with WSL2.
- Docker Engine and Docker Compose v2.
- Git.
- OpenSSL.
- Python 3.
- `xmlsec1` for optional independent XML signature verification.

The tested XML signature verification utility was `xmlsec1 1.2.39` with the OpenSSL backend.

Check that Docker Desktop is running and accessible through WSL before starting the containers.

This project uses local development services. It must not be exposed directly to the public internet.

## 2. Repository structure

Relevant paths:

```text
federated-identity-sso-lab/
├── .env.example
├── README.md
├── docker/
│   ├── compose.yaml
│   └── simplesamlphp/
│       ├── Dockerfile
│       ├── apache.conf
│       ├── authsources.php
│       ├── config.php.example
│       ├── cert/
│       └── metadata/
│           └── saml20-idp-remote.php
├── docs/
│   └── setup/
│       └── README.md
└── scripts/
    └── generate-sp-certificate.sh
```

The actual `.env`, local `config.php`, SP signing keys and certificates are installation-specific.

They are not intended to be committed.

The IdP metadata file contains a public Keycloak signing certificate. Its contents are also installation-specific and must be regenerated when using a different Keycloak realm or signing key.

## 3. Local environment configuration

From the repository root, copy the environment template:

```bash
cp .env.example .env
```

Edit `.env` and replace the placeholders with unique development credentials and a randomly generated secret salt.

The environment defines:

- `KC_BOOTSTRAP_ADMIN_USERNAME`
- `KC_BOOTSTRAP_ADMIN_PASSWORD`
- `SSP_SECRET_SALT`
- `SSP_ADMIN_PASSWORD`

Never reuse production passwords or commit the populated `.env` file.

Copy the SimpleSAMLphp configuration template:

```bash
cp docker/simplesamlphp/config.php.example \
   docker/simplesamlphp/config.php
```

The configured SP authentication source is named `default-sp`.

Its entity ID is:

```text
http://localhost:8081/simplesaml/module.php/saml/sp/metadata.php/default-sp
```

Its configured Keycloak IdP entity ID is:

```text
http://localhost:8080/realms/rivermoor
```

These identifiers must match the corresponding Keycloak configuration.

## 4. Generate Service Provider signing credentials

SimpleSAMLphp uses an RSA-3072 signing key pair for SAML authentication requests.

Generate the credentials:

```bash
./scripts/generate-sp-certificate.sh
```

The script creates:

| File | Purpose |
|---|---|
| `sp-signing.key` | Local SP private signing key |
| `sp-signing.crt` | SP public X.509 certificate |
| `sp-signing-runtime.key` | Runtime private key accessible to Apache UID 33 |

The script:

- Generates an RSA-3072 key pair with SHA-256.
- Creates a self-signed development certificate.
- Checks that the public and private keys match.
- Prepares a runtime key accessible to Apache's `www-data` account.
- Refuses to overwrite existing credentials.

It may request the local sudo password when setting runtime key ownership.

**Never upload the private signing keys to Keycloak.** Keycloak only requires the SP public certificate to verify signed authentication requests.

A separate installation must generate its own credentials.

## 5. Docker Compose configuration

The verified environment uses:

| Service | Host address | Container port |
|---|---|---|
| Keycloak | `127.0.0.1:8080` | 8080 |
| SimpleSAMLphp | `127.0.0.1:8081` | 80 |

Both services are restricted to the local host.

Keycloak uses a named Docker volume, `keycloak-data`, for its development database.

Do not delete or reset that volume unless deliberately rebuilding the realm and its configuration.

### Fixed Keycloak hostname

Keycloak must advertise a consistent public SAML entity ID.

The working Compose configuration contains:

```yaml
environment:
  KC_BOOTSTRAP_ADMIN_USERNAME: ${KC_BOOTSTRAP_ADMIN_USERNAME:?Admin username required}
  KC_BOOTSTRAP_ADMIN_PASSWORD: ${KC_BOOTSTRAP_ADMIN_PASSWORD:?Admin password required}
  KC_HOSTNAME: http://localhost:8080
```

This resolves an issue observed during development:

- Browser access through `localhost:8080` originally produced one entity ID.
- Container access through `keycloak:8080` originally produced another.
- Configuring the fixed hostname made both metadata responses advertise the same public entity ID.

Dynamic hostname backchannel support remains disabled.

### SimpleSAMLphp metadata mount

The SimpleSAMLphp service includes a read-only bind mount:

```yaml
- ./simplesamlphp/metadata/saml20-idp-remote.php:/var/simplesamlphp/metadata/saml20-idp-remote.php:ro
```

This mount belongs under `simplesamlphp`, **not** `keycloak`.

The repository includes a generated `saml20-idp-remote.php` file from the original development environment. This allows Docker Compose to mount an existing file during initial startup.

**Important:** This file contains the original Keycloak realm's public signing certificate. It must not be treated as valid trust metadata for a newly created realm.

For a fresh installation, start the services using the existing metadata file, create and configure the Rivermoor realm as described in Sections 7–8, then regenerate the IdP metadata from the new realm using Section 9. Recreate SimpleSAMLphp after regenerating the metadata, and only then attempt SAML authentication.

This bootstrap sequence is documented but has not yet been independently validated from a clean repository clone.

## 6. Build and start the environment

Validate Docker Compose:

```bash
docker compose --env-file .env -f docker/compose.yaml config --quiet
```

Build SimpleSAMLphp:

```bash
docker compose --env-file .env -f docker/compose.yaml build simplesamlphp
```

Start the environment:

```bash
docker compose --env-file .env -f docker/compose.yaml up -d
```

The original deployment was tested successfully.

However, a completely fresh clone with a newly generated Keycloak realm has **not yet been verified as a complete clean-room installation**.

The steps below describe the manual setup needed after initial deployment.

## 7. Configure the Rivermoor realm in Keycloak

Open:

http://localhost:8080/admin/

Sign in with the development-only bootstrap administrator account.

Create a realm named:

```text
rivermoor
```

This name was deliberately chosen for a fictional identity provider.

### Create a test identity

Within the `rivermoor` realm:

1. Open **Users**.
2. Create an enabled user named `alice.student`.
3. Optionally use fictional identity details, such as Alice Morgan.
4. Configure a unique development-only password.
5. Disable the temporary-password requirement if the test needs a persistent password.

The original development account used:

```text
alice.student@rivermoor.example
```

No password or other secret value is stored in the repository.

The account must be able to authenticate to the Rivermoor realm before federation troubleshooting begins.

## 8. Configure the SAML Service Provider client in Keycloak

Within the `rivermoor` realm, create a client using the SAML protocol.

Use the SimpleSAMLphp entity ID as the client identifier:

```text
http://localhost:8081/simplesaml/module.php/saml/sp/metadata.php/default-sp
```

The Service Provider metadata can be inspected at the same address.

The Assertion Consumer Service (ACS) endpoint used in the verified login was:

```text
http://localhost:8081/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp
```

Configure the client to recognise the correct SP entity ID and ACS destination.

### Signature settings

The working Keycloak configuration used:

| Setting | Verified configuration |
|---|---|
| Client signature required | On |
| Sign documents | On |
| Sign assertions | Off |
| Signature algorithm | RSA_SHA256 |
| SAML signature key name | NONE |
| Canonicalization method | EXCLUSIVE |
| Encrypt assertions | Off |
| Use metadata descriptor URL | Off |

These settings describe the configuration that was actually tested. They are not a general production-security recommendation.

### Import the SP public certificate

In the SAML client's **Keys** tab:

1. Select **Import key**.
2. Choose **Certificate PEM** as the archive format.
3. Select `docker/simplesamlphp/cert/sp-signing.crt`.
4. Import the certificate.

Do not import either private key.

The certificate displayed in Keycloak should match the locally generated SP certificate.

You can inspect the local certificate fingerprint with:

```bash
openssl x509 \
  -in docker/simplesamlphp/cert/sp-signing.crt \
  -noout -fingerprint -sha256
```

The original lab fingerprint was:

```text
66:45:8F:29:9A:25:A0:EC:14:B5:04:90:6E:10:A6:B5:03:93:1B:DF:A1:50:0B:1F:25:A0:83:FE:1F:76:4D:33
```

**A fresh installation should normally have a different fingerprint.** The requirement is that the certificate imported into Keycloak matches the certificate used by SimpleSAMLphp.

## 9. Configure Keycloak IdP metadata in SimpleSAMLphp

Keycloak provides SAML 2.0 IdP metadata at:

```text
http://localhost:8080/realms/rivermoor/protocol/saml/descriptor
```

The original deployment also verified HTTP access from the SimpleSAMLphp container through Docker's internal hostname:

```text
http://keycloak:8080/realms/rivermoor/protocol/saml/descriptor
```

Because Keycloak has an explicit public hostname, both access methods should advertise:

```text
http://localhost:8080/realms/rivermoor
```

Verify this behaviour before importing metadata.

### Generate the IdP metadata file

SimpleSAMLphp 2.5.4 provides the `SimpleSAML\Metadata\SAMLParser` class.

The original lab successfully used that parser to convert Keycloak's XML into flat-file PHP metadata.

From the repository root, with both services running:

```bash
(
  set -e

  target="docker/simplesamlphp/metadata/saml20-idp-remote.php"
  tmp="$(mktemp docker/simplesamlphp/metadata/.idp-metadata.XXXXXX)"
  trap 'rm -f -- "$tmp"' EXIT

  docker compose --env-file .env -f docker/compose.yaml \
    exec -T simplesamlphp php -r '
require "/var/simplesamlphp/vendor/autoload.php";

$xml = file_get_contents(
    "http://keycloak:8080/realms/rivermoor/protocol/saml/descriptor"
);

$parser = \SimpleSAML\Metadata\SAMLParser::parseString($xml);
$metadata = $parser->getMetadata20IdP();

if ($metadata === null) {
    fwrite(STDERR, "No SAML 2.0 IdP metadata found\n");
    exit(1);
}

echo "<?php\n\n";
echo "\$metadata[" . var_export($parser->getEntityId(), true) . "] = ";
echo var_export($metadata, true);
echo ";\n";
' > "$tmp"

  test -s "$tmp"
  sed -i 's/[[:blank:]]\+$//' "$tmp"
  php -l "$tmp"

  php -r '
$metadata = [];
require $argv[1];

$entity = "http://localhost:8080/realms/rivermoor";

if (empty($metadata[$entity]["keys"]) ||
    empty($metadata[$entity]["SingleSignOnService"])) {
    fwrite(STDERR, "Expected IdP metadata missing\n");
    exit(1);
}
' "$tmp"

  chmod 644 "$tmp"
  mv -- "$tmp" "$target"
)
```

This replaces the local generated metadata with information from the current Keycloak realm.

Validate the generated PHP file:

```bash
php -l docker/simplesamlphp/metadata/saml20-idp-remote.php
```

The successful original conversion produced:

- Entity ID: `http://localhost:8080/realms/rivermoor`
- Four Single Sign-On endpoints.
- One IdP signing key.
- `sign.authnrequest = true`.

The metadata file contains public signing certificate information. It must not contain private signing keys or session data.

### Activate the metadata

After regenerating the file, recreate the SimpleSAMLphp service so its bind mount is refreshed:

```bash
docker compose --env-file .env -f docker/compose.yaml up -d --no-deps --force-recreate simplesamlphp
```

The original lab verified that its metadata loader could retrieve the Rivermoor IdP configuration with four SSO endpoints and one signing key.

**Reproducibility limitation:** The original installation was configured and tested incrementally. This full fresh-install sequence has not yet been independently retested from an empty Keycloak volume and repository clone.

## 10. Local service endpoints

| Component | URL |
|---|---|
| Keycloak administration | http://localhost:8080/admin/ |
| Rivermoor realm | http://localhost:8080/realms/rivermoor |
| Keycloak IdP metadata | http://localhost:8080/realms/rivermoor/protocol/saml/descriptor |
| SimpleSAMLphp home | http://localhost:8081/simplesaml/ |
| SimpleSAMLphp administration | http://localhost:8081/simplesaml/module.php/admin/ |
| SP metadata | http://localhost:8081/simplesaml/module.php/saml/sp/metadata.php/default-sp |

These addresses assume the default local development port mappings.

## 11. Test SAML authentication

The verified authentication source is:

```text
default-sp
```

A successful SP-initiated login was performed using the following endpoint:

```text
http://localhost:8081/simplesaml/module.php/saml/sp/login/default-sp?ReturnTo=http%3A%2F%2Flocalhost%3A8081%2Fsimplesaml%2F
```

The `ReturnTo` parameter supplies the local destination after authentication.

### Expected sequence

1. Open the login URL in a private browser session.
2. SimpleSAMLphp initiates SAML authentication.
3. The browser redirects to the Rivermoor Keycloak login.
4. Authenticate as `alice.student`.
5. Keycloak returns a SAML Response to the SimpleSAMLphp ACS.
6. SimpleSAMLphp processes the response and establishes an authenticated session.
7. The browser returns to the requested local destination.

### Verify the SP session

Open the SimpleSAMLphp administration interface:

```text
http://localhost:8081/simplesaml/module.php/admin/
```

Sign in using the local SimpleSAMLphp administrator credentials.

Navigate to:

**Test → default-sp**

The original successful test displayed:

- An authenticated SP session.
- IdP: `http://localhost:8080/realms/rivermoor`.
- Subject NameID: `alice.student`.
- An authentication context.
- Six `Role` attribute values.

The received roles included:

- `default-roles-rivermoor`
- `manage-account`
- `view-profile`
- `uma_authorization`
- `manage-account-links`
- `offline_access`

These were observed values in the original lab, not universally required SAML attributes.

### Verify Keycloak events

Within the `rivermoor` realm, navigate to:

**Events → User events**

The verified test produced a successful `LOGIN` event containing:

```text
auth_method: saml
```

The event's client ID matched the SimpleSAMLphp Service Provider.

This distinguishes the SAML authentication test from earlier Keycloak account-console authentication, which used OpenID Connect.

## 12. Signature verification

The original implementation used the following signing behaviour:

| Message | Verification result |
|---|---|
| SAML AuthnRequest | HTTP-Redirect signature parameters present |
| AuthnRequest algorithm | RSA-SHA256 |
| SAML Response | XML digital signature present |
| Response algorithm | RSA-SHA256 |
| Individually signed Assertion | Not present, consistent with configuration |
| Response cryptographic verification | Successful using xmlsec1 |

### Inspect an AuthnRequest

Browser Developer Tools can be used to inspect the outgoing HTTP-Redirect request.

The original capture contained:

```text
SAMLRequest
SigAlg
Signature
RelayState
```

The observed `SigAlg` value was:

```text
http://www.w3.org/2001/04/xmldsig-more#rsa-sha256
```

The presence of a signature parameter is evidence that the SP generated a signed request, but is not by itself independent cryptographic verification.

### Inspect a SAML Response

The browser's Network panel showed a POST request to:

```text
/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp
```

The form data contained a `SAMLResponse` value.

The Base64-encoded response was decoded locally, without printing or publishing the XML.

Inspection established:

- `ds:Signature` was present under the SAML Response.
- The SignatureMethod was RSA-SHA256.
- An individual Assertion signature was absent.

### Cryptographic verification with xmlsec1

The original lab extracted the public IdP certificate from the SimpleSAMLphp metadata configuration.

It then used `xmlsec1 1.2.39` with the OpenSSL backend to verify the captured SAML Response.

The relevant verification options were:

```bash
xmlsec1 --verify \
  --pubkey-cert-pem /tmp/rivermoor-idp-signing.pem \
  --id-attr:ID 'urn:oasis:names:tc:SAML:2.0:protocol:Response' \
  --enabled-reference-uris same-doc \
  /path/to/temporary-saml-response.xml
```

The certificate and XML file paths refer to temporary local files prepared for the verification.

The command exited with:

```text
Verification exit code: 0
```

This establishes successful XML signature verification against the explicitly supplied IdP certificate for that captured response.

The original Ubuntu build did not support `--enabled-retrieval-uris`, despite advertising that option in its help output. Removing the unsupported option resolved the command-line failure.

**Security note:** A successful XML signature verification does not independently establish that every SAML assertion condition or authorisation constraint has been validated.

Never publish raw SAML responses, session identifiers, authentication tokens or private signing keys.

## 13. Troubleshooting observations

Several genuine configuration issues were encountered and resolved during development.

### Inconsistent Keycloak entity ID

**Symptom:** Keycloak metadata returned different entity IDs depending on whether it was requested through `localhost:8080` or `keycloak:8080`.

**Cause:** No fixed Keycloak hostname was configured.

**Resolution:** Set `KC_HOSTNAME=http://localhost:8080`.

**Verification:** Both access paths advertised the same public entity ID.

### IdP metadata not found

**Symptom:** SimpleSAMLphp returned `METADATANOTFOUND`.

**Cause:** The expected metadata file was not present inside the running container because the bind mount had not been included in the effective Compose configuration.

**Resolution:** Add the read-only bind mount under the SimpleSAMLphp service and recreate the container.

**Verification:** The SimpleSAMLphp metadata loader successfully retrieved the IdP configuration.

### Signing certificate mismatch

**Symptom:** The certificate originally displayed by Keycloak did not match the SP certificate.

**Investigation:** Compare SHA-256 fingerprints.

**Resolution:** Import the existing SP public certificate using Keycloak's Certificate PEM import option.

**Verification:** The imported certificate's fingerprint matched the locally generated SP certificate.

### Invalid login endpoint

**Symptom:** Some attempted authentication URLs produced Apache or SimpleSAMLphp 404 errors.

**Cause:** The attempts used outdated routes, omitted the required authentication source, or incorrectly duplicated `module.php`.

**Resolution:** Inspect the installed SAML module's routing definitions and use the correct `default-sp` login endpoint.

### Missing ReturnTo parameter

**Symptom:** SimpleSAMLphp reported:

```text
Bad request received
Missing ReturnTo parameter.
```

**Resolution:** Supply a valid local `ReturnTo` URL.

**Verification:** Authentication proceeded to the Rivermoor login page.

These are observed development troubleshooting findings. They are separate from the deliberately introduced failure scenarios planned for the next project phase.

## 14. Current limitations

The basic federation works, but the project is not production-ready.

Known limitations include:

- Keycloak realm and SAML client provisioning are manual.
- Fresh-install reproducibility has not been independently verified.
- IdP metadata contains installation-specific signing certificate data.
- HTTP is used in a local-only development environment.
- Keycloak uses development-mode configuration.
- SAML assertion audience restrictions, expiry handling and replay protection have not been independently exercised through controlled negative tests.
- Single Logout has not been validated.
- HTTP-Artifact and SOAP-based workflows have not been tested.
- Automated SAML integration tests are not yet implemented.
- No production hardening or external identity integration has been performed.

## 15. Next development phase

With the working baseline established, the next project phase will focus on controlled failure scenarios.

Candidate exercises include:

1. Incorrect IdP entity ID.
2. Incorrect ACS destination.
3. Missing or incorrect SP signing certificate.
4. Missing required attributes.
5. Assertion expiry or time validation problems.
6. Unavailable Identity Provider.
7. SAML session and logout behaviour.

Each exercise should document:

- Expected behaviour.
- Deliberate configuration change.
- Observed error or symptom.
- Relevant logs and SAML evidence.
- Root cause.
- Corrective action.
- Successful recovery verification.

Always retain a known-good configuration and a clear rollback method before introducing deliberate failures.

---

*This is an independently developed local portfolio environment using fictional identities. It does not involve access to any organisation's production identity systems.*
