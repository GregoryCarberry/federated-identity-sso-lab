
#!/usr/bin/env bash
set -euo pipefail

# Rivermoor Federated Identity Lab
# Generate development-only SAML SP signing credentials.
# Refuses to overwrite existing credentials.

SCRIPT_DIR="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
CERT_DIR="$PROJECT_ROOT/docker/simplesamlphp/cert"

KEY="$CERT_DIR/sp-signing.key"
CERT="$CERT_DIR/sp-signing.crt"
RUNTIME_KEY="$CERT_DIR/sp-signing-runtime.key"

umask 077
mkdir -p "$CERT_DIR"

# Never silently replace an existing signing identity.
for file in "$KEY" "$CERT" "$RUNTIME_KEY"; do
    if [[ -e "$file" || -L "$file" ]]; then
        echo "ERROR: Signing credential already exists: $file" >&2
        echo "No credentials were generated." >&2
        exit 1
    fi
done

# Generate files in a temporary directory first.
TEMP_DIR="$(mktemp -d "$CERT_DIR/.generate.XXXXXXXX")"
trap 'rm -rf -- "$TEMP_DIR"' EXIT

echo "Generating Rivermoor development SAML signing credentials..."

openssl req \
    -x509 \
    -newkey rsa:3072 \
    -sha256 \
    -noenc \
    -keyout "$TEMP_DIR/sp-signing.key" \
    -out "$TEMP_DIR/sp-signing.crt" \
    -days 365 \
    -subj "/CN=Rivermoor SAML Lab SP"

# Confirm the private key and certificate match.
KEY_HASH="$(openssl pkey -in "$TEMP_DIR/sp-signing.key" -pubout -outform DER | sha256sum | awk '{print $1}')"
CERT_HASH="$(openssl x509 -in "$TEMP_DIR/sp-signing.crt" -pubkey -noout | openssl pkey -pubin -outform DER | sha256sum | awk '{print $1}')"

if [[ "$KEY_HASH" != "$CERT_HASH" ]]; then
    echo "ERROR: Certificate and private key do not match." >&2
    exit 1
fi

# Prepare the runtime key for Apache's www-data account (UID 33).
# This operation may prompt for the local sudo password.
sudo install -m 600 -o 33 -g 33 \
    "$TEMP_DIR/sp-signing.key" "$RUNTIME_KEY"

install -m 600 "$TEMP_DIR/sp-signing.key" "$KEY"
install -m 644 "$TEMP_DIR/sp-signing.crt" "$CERT"

echo "Signing credentials generated successfully."
echo "Private key: $KEY"
echo "Public certificate: $CERT"
echo "Runtime key: $RUNTIME_KEY"
echo "Certificate validity:"
openssl x509 -in "$CERT" -noout -dates
