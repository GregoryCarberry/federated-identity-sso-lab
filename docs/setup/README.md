
# Rivermoor Federated Identity Lab — Setup Guide

## Overview

This lab uses Docker Compose to run:

- Keycloak 26.8.2 as the SAML Identity Provider (IdP)
- SimpleSAMLphp 2.5.4 as the SAML Service Provider (SP)
- Apache and PHP 8.3 for the SimpleSAMLphp application

The environment is intended for isolated local development and troubleshooting.

**Status:** Both applications have been started successfully. SAML federation has not yet been validated.

## Prerequisites

- Docker Engine or Docker Desktop
- Docker Compose v2
- OpenSSL
- Python 3
- Git

## Local configuration

Copy the environment template:

```bash
cp .env.example .env
```

Replace the placeholder values with unique development credentials and a random secret salt.

Never commit `.env` or private signing keys.

Copy the SimpleSAMLphp configuration template:

```bash
cp docker/simplesamlphp/config.php.example \
   docker/simplesamlphp/config.php
```

## Signing credentials

The Service Provider uses a locally generated RSA key pair:

- `sp-signing.key` — private key, excluded from Git
- `sp-signing.crt` — public certificate
- `sp-signing-runtime.key` — runtime copy accessible to Apache UID 33

The current development certificates are self-signed. Signing-key generation and ownership configuration must be completed before SAML request signing can work.

Generate the Service Provider signing credentials:

```bash
./scripts/generate-sp-certificate.sh
```

The script creates a fresh self-signed certificate and RSA-3072
private key, verifies that they match, and prepares a runtime key
owned by UID/GID 33 for the Apache container.

It refuses to overwrite existing credentials.

The script may request your local sudo password when setting
ownership of the runtime key.

All generated signing credentials are excluded from Git.

## Deployment

Validate the configuration:

```bash
docker compose --env-file .env -f docker/compose.yaml config --quiet
```

Build the Service Provider image:

```bash
docker compose --env-file .env -f docker/compose.yaml build simplesamlphp
```

Start the services:

```bash
docker compose --env-file .env -f docker/compose.yaml up -d
```

## Local endpoints

| Component | URL |
|---|---|
| Keycloak administration | http://localhost:8080/admin/ |
| Rivermoor realm | http://localhost:8080/realms/rivermoor |
| SimpleSAMLphp | http://localhost:8081/simplesaml/ |
| SP metadata | http://localhost:8081/simplesaml/module.php/saml/sp/metadata.php/default-sp |
| Keycloak IdP metadata | http://localhost:8080/realms/rivermoor/protocol/saml/descriptor |

## Current limitations

- SAML federation has not yet been tested end to end.
- Container-to-container addressing still requires validation.
- Signing certificates must be generated for each independent installation.
- The Keycloak realm and SAML client currently require manual configuration.
- The environment uses HTTP and development-only settings. Do not expose it publicly.
- Automated deployment and integration tests remain future work.