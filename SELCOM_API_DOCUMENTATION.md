# Selcom Wallet Pull Funds (Push USSD) Integration

This document describes the Selcom API integration replacing the previous Tembo integration. Based on [SELCOMAPIS/docs/guides/wallet-pull-funds-push-ussd.md](/Volumes/DATA/PROJECTS/SELCOMAPIS/docs/guides/wallet-pull-funds-push-ussd.md).

## Overview

- **API:** `POST /v1/wallet/pushussd` – Triggers USSD push to customer wallet
- **Flow:** Business initiates → Selcom pushes to customer → Customer enters PIN → Selcom notifies via C2B
- **Supported wallets:** AIRTELMONEY, MPESA-TZ, TIGOPESATZ, HALOPESATZ, TTCLMOBILE, ZANTELEZPESA

## Configuration

Add to `.env`:

```env
SELCOM_BASE_URL=http://example.com
SELCOM_API_KEY=your_api_key
SELCOM_API_SECRET=your_api_secret
SELCOM_VENDOR_ID=01234567891
SELCOM_C2B_BEARER_TOKEN=
```

Obtain credentials from Selcom: support@selcom.net

## C2B Endpoints (Register with Selcom)

Selcom will call these URLs. Register them when setting up your C2B integration:

| Endpoint | Purpose |
|----------|---------|
| `POST /api/selcom/c2b/lookup` | Validate payment reference before push |
| `POST /api/selcom/c2b/validation` | Validate before funds pulled (timeout = auto reverse) |
| `POST /api/selcom/c2b/notification` | Final payment confirmation |

Full URLs: `https://your-domain.com/api/selcom/c2b/lookup`, etc.

## Authentication

### Outgoing (to Selcom)

- `Authorization: SELCOM {base64(api_key)}`
- `Digest-Method: HS256`
- `Digest: Base64(HMAC-SHA256(signed_string, api_secret))`
- `Timestamp: ISO 8601`
- `Signed-Fields: transid,utilityref,amount,vendor,msisdn`

### Incoming (C2B from Selcom)

- `Authorization: Bearer {SELCOM_C2B_BEARER_TOKEN}` (if configured)

## Push USSD Request Format

| Field | Source |
|-------|--------|
| transid | Auto-generated (TXN-{timestamp}-{random}) |
| utilityref | Payment reference (client_reference) |
| amount | Payment amount (TZS) |
| vendor | From SELCOM_VENDOR_ID |
| msisdn | Customer phone (255XXXXXXXXX) |

## Response Codes

| resultcode | Meaning |
|------------|---------|
| 000 | Success (push accepted) |
| 111, 927 | In progress; wait for notification |
| 999 | Ambiguous; query status |
| Others | Failed |

## Query Transaction Status

```
GET /v1/c2b/query-status?transid={transid}
GET /v1/c2b/query-status?reference={reference}
```

## Seeding

```bash
php artisan db:seed --class=SelcomAggregatorSeeder
```

Or run full seed (uses Selcom in AggregatorsSeeder):

```bash
php artisan migrate:fresh --seed
```
