# Payments API Documentation

## Overview
The Payments API provides endpoints for managing payment transactions within the Sports Club Pakistan system. This system handles various types of payments including memberships, events, trainer sessions, and service purchases with support for multiple payment methods and statuses.

## Base URL
```
/api
```

## Authentication
All protected endpoints require authentication using Laravel Sanctum tokens:
```
Authorization: Bearer {token}
```

## Role-Based Access Control

### Roles and Permissions:
- **Member**: Can view their own payments and create new payments
- **Owner**: Can view all payments and manage payment operations
- **Admin**: Can view all payments and manage payment operations

---

## Endpoints

### 1. List Payments
**GET** `/api/payments`

Returns a list of payments based on user role.

#### Query Parameters:
| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `user_id` | UUID | Filter by user (admin/owner only) | `user_id=123e4567-e89b-12d3-a456-426614174000` |
| `payment_type` | string | Filter by payment type | `payment_type=membership` |
| `status` | string | Filter by status | `status=completed` |
| `payment_method` | string | Filter by payment method | `payment_method=card` |
| `start_date` | date | Start date filter | `start_date=2025-08-01` |
| `end_date` | date | End date filter | `end_date=2025-08-31` |
| `min_amount` | decimal | Minimum amount filter | `min_amount=100.00` |
| `max_amount` | decimal | Maximum amount filter | `max_amount=1000.00` |
| `sort_by` | string | Sort field | `sort_by=created_at` |
| `sort_order` | string | Sort direction | `sort_order=desc` |
| `per_page` | integer | Items per page | `per_page=15` |

#### Response:
```json
{
    "status": "success",
    "data": {
        "payments": [
            {
                "id": "123e4567-e89b-12d3-a456-426614174000",
                "user_id": "123e4567-e89b-12d3-a456-426614174001",
                "transaction_id": "TXN_123456789",
                "amount": "150.00",
                "currency": "PKR",
                "payment_method": "card",
                "payment_type": "membership",
                "reference_id": "123e4567-e89b-12d3-a456-426614174002",
                "status": "completed",
                "payment_gateway_response": {
                    "transaction_id": "txn_abc123",
                    "authorization_code": "AUTH_123"
                },
                "failure_reason": null,
                "refund_amount": null,
                "refund_date": null,
                "payment_date": "2025-08-31T10:00:00.000000Z",
                "created_at": "2025-08-31T10:00:00.000000Z",
                "updated_at": "2025-08-31T10:00:00.000000Z",
                "user": {
                    "id": "123e4567-e89b-12d3-a456-426614174001",
                    "name": "Ahmed Khan",
                    "email": "ahmed@example.com"
                }
            }
        ],
        "pagination": {
            "current_page": 1,
            "last_page": 5,
            "per_page": 15,
            "total": 75
        }
    }
}
```

### 2. Create Payment
**POST** `/api/payments`

Creates a new payment record.

#### Request Body:
```json
{
    "user_id": "123e4567-e89b-12d3-a456-426614174001",
    "transaction_id": "TXN_123456789",
    "amount": "150.00",
    "currency": "PKR",
    "payment_method": "card",
    "payment_type": "membership",
    "reference_id": "123e4567-e89b-12d3-a456-426614174002",
    "status": "completed",
    "payment_gateway_response": {
        "transaction_id": "txn_abc123",
        "authorization_code": "AUTH_123"
    },
    "payment_date": "2025-08-31T10:00:00.000000Z"
}
```

#### Response:
```json
{
    "status": "success",
    "message": "Payment created successfully",
    "data": {
        "payment": {
            "id": "123e4567-e89b-12d3-a456-426614174000",
            "user_id": "123e4567-e89b-12d3-a456-426614174001",
            "transaction_id": "TXN_123456789",
            "amount": "150.00",
            "currency": "PKR",
            "payment_method": "card",
            "payment_type": "membership",
            "reference_id": "123e4567-e89b-12d3-a456-426614174002",
            "status": "completed",
            "payment_gateway_response": {
                "transaction_id": "txn_abc123",
                "authorization_code": "AUTH_123"
            },
            "failure_reason": null,
            "refund_amount": null,
            "refund_date": null,
            "payment_date": "2025-08-31T10:00:00.000000Z",
            "created_at": "2025-08-31T10:00:00.000000Z",
            "updated_at": "2025-08-31T10:00:00.000000Z"
        }
    }
}
```

### 3. Get Payment
**GET** `/api/payments/{payment}`

Returns a specific payment.

#### Response:
```json
{
    "status": "success",
    "data": {
        "payment": {
            "id": "123e4567-e89b-12d3-a456-426614174000",
            "user_id": "123e4567-e89b-12d3-a456-426614174001",
            "transaction_id": "TXN_123456789",
            "amount": "150.00",
            "currency": "PKR",
            "payment_method": "card",
            "payment_type": "membership",
            "reference_id": "123e4567-e89b-12d3-a456-426614174002",
            "status": "completed",
            "payment_gateway_response": {
                "transaction_id": "txn_abc123",
                "authorization_code": "AUTH_123"
            },
            "failure_reason": null,
            "refund_amount": null,
            "refund_date": null,
            "payment_date": "2025-08-31T10:00:00.000000Z",
            "created_at": "2025-08-31T10:00:00.000000Z",
            "updated_at": "2025-08-31T10:00:00.000000Z",
            "user": {
                "id": "123e4567-e89b-12d3-a456-426614174001",
                "name": "Ahmed Khan",
                "email": "ahmed@example.com"
            }
        }
    }
}
```

### 4. Update Payment
**PUT** `/api/payments/{payment}`

Updates a payment record.

#### Request Body:
```json
{
    "status": "refunded",
    "refund_amount": "150.00",
    "refund_date": "2025-08-31T12:00:00.000000Z",
    "failure_reason": "Customer requested refund"
}
```

#### Response:
```json
{
    "status": "success",
    "message": "Payment updated successfully",
    "data": {
        "payment": {
            "id": "123e4567-e89b-12d3-a456-426614174000",
            "status": "refunded",
            "refund_amount": "150.00",
            "refund_date": "2025-08-31T12:00:00.000000Z",
            "failure_reason": "Customer requested refund",
            "updated_at": "2025-08-31T12:00:00.000000Z"
        }
    }
}
```

### 5. Delete Payment
**DELETE** `/api/payments/{payment}`

Deletes a payment record.

#### Response:
```json
{
    "status": "success",
    "message": "Payment deleted successfully"
}
```

### 6. Get Payment Statistics
**GET** `/api/payments/statistics`

Returns payment statistics based on user role.

#### Query Parameters:
| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `user_id` | UUID | Filter by user (admin/owner only) | `user_id=123e4567-e89b-12d3-a456-426614174000` |
| `start_date` | date | Start date for statistics | `start_date=2025-08-01` |
| `end_date` | date | End date for statistics | `end_date=2025-08-31` |

#### Response:
```json
{
    "status": "success",
    "data": {
        "statistics": {
            "total_payments": 150,
            "total_amount": "22500.00",
            "completed_payments": 140,
            "completed_amount": "21000.00",
            "pending_payments": 5,
            "pending_amount": "750.00",
            "failed_payments": 3,
            "failed_amount": "450.00",
            "refunded_payments": 2,
            "refunded_amount": "300.00",
            "payment_methods": {
                "card": {
                    "count": 120,
                    "amount": "18000.00"
                },
                "bank_transfer": {
                    "count": 20,
                    "amount": "3000.00"
                },
                "cash": {
                    "count": 10,
                    "amount": "1500.00"
                }
            },
            "payment_types": {
                "membership": {
                    "count": 80,
                    "amount": "12000.00"
                },
                "event": {
                    "count": 40,
                    "amount": "6000.00"
                },
                "trainer_session": {
                    "count": 20,
                    "amount": "3000.00"
                },
                "service": {
                    "count": 10,
                    "amount": "1500.00"
                }
            }
        }
    }
}
```

---

## Payment Types

The system supports the following payment types:

| Type | Description | Reference Entity |
|------|-------------|------------------|
| `membership` | Membership subscription payments | Membership |
| `event` | Event registration payments | Event Registration |
| `trainer_session` | Trainer session booking payments | Trainer Session |
| `service` | Service purchase payments | Service Purchase |

---

## Payment Methods

The system supports the following payment methods:

| Method | Description |
|--------|-------------|
| `card` | Credit/Debit card payments |
| `bank_transfer` | Bank transfer payments |
| `cash` | Cash payments |
| `wallet` | Digital wallet payments |
| `other` | Other payment methods |

---

## Payment Statuses

The system supports the following payment statuses:

| Status | Description |
|--------|-------------|
| `pending` | Payment initiated but not completed |
| `completed` | Payment successfully processed |
| `failed` | Payment failed to process |
| `cancelled` | Payment was cancelled |
| `refunded` | Payment was refunded |

---

## Data Structures

### Payment Object
```json
{
    "id": "UUID",
    "user_id": "UUID",
    "transaction_id": "string (unique)",
    "amount": "decimal (10,2)",
    "currency": "string (default: PKR)",
    "payment_method": "enum: card, bank_transfer, cash, wallet, other",
    "payment_type": "enum: membership, event, trainer_session, service",
    "reference_id": "UUID (nullable)",
    "status": "enum: pending, completed, failed, cancelled, refunded",
    "payment_gateway_response": "JSON object (nullable)",
    "failure_reason": "text (nullable)",
    "refund_amount": "decimal (10,2, nullable)",
    "refund_date": "datetime (nullable)",
    "payment_date": "datetime (nullable)",
    "created_at": "datetime",
    "updated_at": "datetime"
}
```

### Validation Rules

#### Store Payment Request:
- `user_id`: required, exists:users,id
- `transaction_id`: required, string, unique:payments
- `amount`: required, numeric, min:0, max:999999.99
- `currency`: required, string, size:3
- `payment_method`: required, in:card,bank_transfer,cash,wallet,other
- `payment_type`: required, in:membership,event,trainer_session,service
- `reference_id`: nullable, exists based on payment_type
- `status`: required, in:pending,completed,failed,cancelled,refunded
- `payment_gateway_response`: nullable, json
- `failure_reason`: nullable, string
- `refund_amount`: nullable, numeric, min:0
- `refund_date`: nullable, date
- `payment_date`: nullable, date

#### Update Payment Request:
- `status`: sometimes, in:pending,completed,failed,cancelled,refunded
- `payment_gateway_response`: sometimes, json
- `failure_reason`: sometimes, nullable, string
- `refund_amount`: sometimes, nullable, numeric, min:0
- `refund_date`: sometimes, nullable, date
- `payment_date`: sometimes, nullable, date

---

## Error Responses

### 403 Unauthorized
```json
{
    "status": "error",
    "message": "Unauthorized to view this payment"
}
```

### 404 Not Found
```json
{
    "status": "error",
    "message": "Payment not found"
}
```

### 422 Validation Error
```json
{
    "status": "error",
    "message": "Validation failed",
    "errors": {
        "amount": ["The amount must be at least 0."],
        "payment_method": ["The selected payment method is invalid."]
    }
}
```

---

## Rate Limiting
- All payment endpoints are subject to standard API rate limiting
- Payment creation endpoints have additional rate limiting to prevent abuse

---

## Best Practices

1. **Use unique transaction IDs** to prevent duplicate payments
2. **Store payment gateway responses** for debugging and audit purposes
3. **Handle refunds properly** by updating both refund_amount and refund_date
4. **Validate reference IDs** based on payment type before creating payments
5. **Use appropriate payment statuses** to track payment lifecycle
6. **Implement proper error handling** for failed payments

---

## Integration Examples

### JavaScript (Fetch API)
```javascript
// Create a payment
const createPayment = async (paymentData) => {
    const response = await fetch('/api/payments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify(paymentData)
    });
    return response.json();
};

// Get payment statistics
const getPaymentStats = async (params = {}) => {
    const queryString = new URLSearchParams(params).toString();
    const response = await fetch(`/api/payments/statistics?${queryString}`, {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });
    return response.json();
};
```

### cURL Examples
```bash
# Create payment
curl -X POST /api/payments \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "user_id": "123e4567-e89b-12d3-a456-426614174001",
    "transaction_id": "TXN_123456789",
    "amount": "150.00",
    "currency": "PKR",
    "payment_method": "card",
    "payment_type": "membership",
    "reference_id": "123e4567-e89b-12d3-a456-426614174002",
    "status": "completed"
  }'

# Get payments with filters
curl -X GET "/api/payments?status=completed&payment_type=membership&start_date=2025-08-01&end_date=2025-08-31" \
  -H "Authorization: Bearer {token}"

# Get payment statistics
curl -X GET /api/payments/statistics \
  -H "Authorization: Bearer {token}"
```

---

## Webhook Integration

For payment gateway integrations, consider implementing webhooks to automatically update payment statuses:

```php
// Example webhook handler
public function handlePaymentWebhook(Request $request)
{
    $transactionId = $request->input('transaction_id');
    $status = $request->input('status');
    $gatewayResponse = $request->input('gateway_response');

    $payment = Payment::where('transaction_id', $transactionId)->first();

    if ($payment) {
        $payment->update([
            'status' => $status,
            'payment_gateway_response' => $gatewayResponse,
            'payment_date' => now()
        ]);

        // Trigger additional actions based on payment status
        if ($status === 'completed') {
            // Send success notification, update related records, etc.
        }
    }

    return response()->json(['status' => 'success']);
}
```
