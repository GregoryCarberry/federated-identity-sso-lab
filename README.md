# Federated Identity & SSO Troubleshooting Lab

A practical, Docker-based portfolio lab for exploring **SAML 2.0 federation, Single Sign-On (SSO), identity management and authentication troubleshooting**.

The project uses Keycloak as an Identity Provider (IdP) and SimpleSAMLphp as a Service Provider (SP), with an emphasis on understanding authentication flows, investigating failures and validating security configurations.

## Project objectives

- Build and configure a working SAML 2.0 federation.
- Understand the relationship between an Identity Provider and Service Provider.
- Investigate SAML authentication requests, responses, certificates and session attributes.
- Reproduce common federation failures in a controlled environment.
- Document symptoms, root causes, corrective actions and verification evidence.
- Develop practical identity and access management troubleshooting skills.

## Architecture

| Component | Technology |
|---|---|
| Identity Provider | Keycloak 26.8.2 |
| Service Provider | SimpleSAMLphp 2.5.4 |
| Web server / runtime | Apache / PHP 8.3 |
| Deployment | Docker Compose |
| Development environment | Ubuntu on WSL2 / Docker Desktop |
| Signature verification | OpenSSL / xmlsec1 |

The environment runs locally, using fictional identities and development-only credentials.

### Authentication flow

1. The user initiates authentication through SimpleSAMLphp.
2. The SP generates a signed SAML authentication request.
3. The browser redirects to the Keycloak Identity Provider.
4. Keycloak authenticates the user.
5. Keycloak returns a signed SAML response to the SP's Assertion Consumer Service (ACS).
6. SimpleSAMLphp validates the response and establishes an authenticated session.
7. The SP exposes the authenticated identity and received attributes.

## Current project status

**Phase 1 — Local infrastructure: Complete**

**Phase 2 — Basic SAML federation: Verified**

**Phase 3 — Controlled troubleshooting scenarios: Planned**

### Verified implementation

The following capabilities have been tested successfully:

- Keycloak and SimpleSAMLphp deployed through Docker Compose.
- Fictional `rivermoor` realm and `alice.student` test identity configured.
- Consistent Keycloak SAML entity ID across browser-facing and Docker-internal access paths.
- Keycloak IdP metadata configured and loaded by SimpleSAMLphp.
- SP public signing certificate imported into Keycloak and verified by SHA-256 fingerprint.
- Successful SP-initiated SAML 2.0 browser authentication.
- Keycloak authentication event confirming `auth_method: saml`.
- Authenticated SimpleSAMLphp session containing the expected NameID and role attributes.
- HTTP-Redirect authentication request containing `SAMLRequest`, `SigAlg` and `Signature`.
- RSA-SHA256 authentication-request signing confirmed from the captured request parameters.
- HTTP-POST SAML response delivered to the SP's ACS.
- RSA-SHA256 XML signature present on the SAML Response.
- Independent cryptographic verification of the Response signature using `xmlsec1` and the trusted IdP public certificate.

The implementation signs the SAML Response document rather than signing the individual Assertion separately.

### Scope of validation

Successful authentication and XML signature verification demonstrate that the basic federation works.

They do not constitute a complete SAML security assessment. Additional testing of assertion conditions, audience restrictions, time validation, replay protection and logout behaviour remains outstanding.

## Planned troubleshooting scenarios

| Scenario | Status |
|---|---|
| Incorrect SAML entity ID | Planned |
| Incorrect Assertion Consumer Service URL | Planned |
| Signing certificate mismatch | Planned |
| Missing or incorrect user attributes | Planned |
| Expired SAML assertions | Planned |
| Identity Provider unavailable | Planned |
| Session and logout behaviour | Planned |

Each scenario will include the expected behaviour, observed symptoms, investigation evidence, root cause, resolution and verification steps.

## Repository structure

- `docker/` — Docker Compose and application configuration.
- `docker/simplesamlphp/` — Service Provider configuration and metadata.
- `scripts/` — Supporting setup and certificate-generation scripts.
- `docs/setup/` — Environment setup instructions and prerequisites.

Additional architecture diagrams, troubleshooting runbooks and incident records will be added as the project develops.

## Setup and reproducibility

See [the setup guide](docs/setup/README.md) for environment prerequisites, local configuration and deployment instructions.

**Important:** The current repository is not yet a fully automated, one-command federation deployment.

Keycloak realm creation, SAML client settings, test identities and certificate trust configuration currently involve manual steps.

Each installation must generate its own signing credentials and establish trust using the corresponding IdP and SP certificates. The included IdP metadata reflects the development installation and must not be assumed valid for a newly created Keycloak realm.

Improving clean-environment reproducibility is part of the project's continuing development.

## Security considerations

This is a local educational environment, **not a production-ready identity service**.

- Services are bound to the local host.
- Authentication currently uses HTTP in an isolated development environment.
- Fictional users and development-only credentials are used.
- Private keys, passwords, environment secrets and runtime session data must not be committed.
- Public signing certificates are used to establish and verify federation trust.
- Captured authentication messages and session identifiers are excluded from public repository evidence.

Production deployments would require additional hardening, including HTTPS, appropriate secret management, stronger operational controls and security testing.

## Future extensions

- Controlled SAML fault-injection and recovery exercises.
- Reproducible Keycloak realm and SAML client provisioning.
- Automated configuration and integration tests.
- Authentication flow diagrams and troubleshooting runbooks.
- OpenID Connect (OIDC) comparison.
- Multi-factor authentication.
- LDAP and directory integration.
- Additional federation technologies, such as Shibboleth.

---

*This project is an independently developed personal learning and portfolio environment. It does not represent access to or work performed on any organisation's production identity infrastructure.*
