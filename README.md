# Federated Identity & SSO Troubleshooting Lab

A practical, Docker-based lab exploring federated authentication,
Single Sign-On (SSO), SAML 2.0 and technical incident investigation.

## Project objectives

- Configure a working SAML 2.0 authentication environment.
- Understand interactions between an Identity Provider (IdP)
  and a Service Provider (SP).
- Inspect authentication requests, responses and application logs.
- Simulate and troubleshoot common authentication failures.
- Document root causes, resolutions and verification steps.

## Proposed architecture

| Component | Technology |
|-----------|------------|
| Identity Provider | Keycloak |
| Service Provider | SimpleSAMLphp |
| Protected application | Minimal PHP application |
| Deployment | Docker Compose |
| Development environment | WSL2 Ubuntu / Docker Desktop |

The initial environment will use fictional user accounts and
remain isolated from production systems.

## Planned troubleshooting exercises

1. Incorrect SAML Entity ID.
2. Incorrect Assertion Consumer Service (ACS) URL.
3. Signing certificate mismatch.
4. Missing required user attributes.
5. Expired SAML assertions.
6. Identity Provider unavailable.

Each exercise will document symptoms, investigation steps,
root cause, corrective action and successful validation.

## Project status

**Phase 1 — Foundations and architecture (in progress)**

The development environment has been verified.
No SAML services have been deployed yet.

## Repository structure

- `docker/` — Container and application configuration.
- `docs/architecture/` — Architecture and authentication flow documentation.
- `docs/incidents/` — Simulated incidents and troubleshooting evidence.

## Security

This is a local educational environment.

No real institutional accounts, production credentials or
publicly accessible authentication services will be used.

Secrets, private keys and runtime data must not be committed.

## Future extensions

- OpenID Connect (OIDC).
- Shibboleth integration.
- Multi-factor authentication.
- LDAP integration.
- Monitoring and automated configuration validation.
