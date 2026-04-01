# Postman – Tembo ESB (ZIMA Pay)

## Import

1. Open Postman.
2. **Import** → **File** → choose `Tembo_ESB_Collection.postman_collection.json`.
3. The collection **Tembo ESB - ZIMA Pay** will appear.

## Setup

1. **Start Laravel** (if testing locally):
   ```bash
   php artisan serve
   ```
2. In the collection, check **Variables**:
   - **base_url**: `http://127.0.0.1:8000` (or your server URL)
   - **api_key** / **api_secret**: Test Bank credentials (pre-filled) or your client’s.
3. For **Money Collection**, set **customer_phone** (e.g. `255767582837`) and optionally **reference**.

## Requests

| Request | Method | Description |
|--------|--------|-------------|
| **Health** | GET | ESB health check (no auth). |
| **Get Services** | GET | List services for the authenticated client. |
| **Money Collection** | POST | Trigger Tembo collection (USSD push). Amount ≥ 1000 TZS. |
| **Money Collection (Airtel)** | POST | Same with `TZ-AIRTEL-C2B`. |
| **Collection Balance** | POST | Get Tembo collection account balance. |
| **Collection Statement** | POST | Get statement (use `start_date`, `end_date`). |
| **Payment Status** | POST | Check status (use `transaction_id` and `reference` from Money Collection). |

## Money Collection body

- **customer_phone**: e.g. `255767582837`
- **mobile_network**: `TZ-VODACOM-C2B` (M-Pesa), `TZ-AIRTEL-C2B`, `TZ-TIGO-C2B`, `TZ-HALOTEL-C2B`
- **amount**: ≥ 1000 (TZS)
- **description**, **reference**, **date** (e.g. `2025-03-05 12:00:00`), **webhook_url**

If **date** uses `{{$isoTimestamp}}` and your Postman version doesn’t support it, replace it with a fixed date in `Y-m-d H:i:s` format.

## Expected responses

- **Health**: `{"status":"healthy","version":"1.0.0",...}`
- **Get Services**: `{"services":[...]}`
- **Money Collection**: `status` (pending/success/failed), `transaction_id`, `reference`, `amount`, etc.  
  `PROVIDER_FAILED` = payment not completed by customer/network (e.g. cancelled, timeout).
