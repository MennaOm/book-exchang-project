# Backend Exchange & Messaging (PHP + MySQL)

## Endpoints
- POST `exchange/send_request.php` { listing_id, message? }
- POST `exchange/update_request.php` { request_id, action: approve|reject|cancel|complete }
- GET  `exchange/my_requests.php?role=owner|requester&status=pending|approved|rejected|completed`
- POST `messages/send_message.php` { exchange_request_id, message }
- GET  `messages/get_messages.php?exchange_request_id=ID`
- POST `messages/mark_read.php` { exchange_request_id }
- GET  `messages/list_conversations.php`
- POST `ratings/submit_rating.php` { request_id , rating , review? }
- GET  `ratings/get_user_ratings.php?user_id=id`
- POST `notifications/read.php` { notification_id }
- GET  `notifications/fetch.php`

### Notes
- All endpoints require user login. Your existing auth should set `$_SESSION['user_id']`.
- Uses prepared statements; returns JSON.
- Update `backend/_inc/config.php` with your database credentials.
