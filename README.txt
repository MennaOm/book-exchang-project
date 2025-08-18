# Backend Exchange & Messaging (PHP + MySQL)

## Endpoints
- POST `backend/exchange/send_request.php` { listing_id, message? }
- POST `backend/exchange/update_request.php` { request_id, action: approve|reject|cancel|complete }
- GET  `backend/exchange/my_requests.php?role=owner|requester&status=pending|approved|rejected|completed`
- POST `backend/messages/send_message.php` { exchange_request_id, message }
- GET  `backend/messages/get_messages.php?exchange_request_id=ID`
- POST `backend/messages/mark_read.php` { exchange_request_id }
- GET  `backend/messages/list_conversations.php`

### Notes
- All endpoints require user login. Your existing auth should set `$_SESSION['user_id']`.
- Uses prepared statements; returns JSON.
- Update `backend/_inc/config.php` with your database credentials.
