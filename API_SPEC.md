# Amter Spoken English (`amter.in`) — Mobile JSON API & Google Play Specification

**Base URL:** `https://amter.in/api`  
**Authentication:** Laravel Sanctum Bearer Token (`Authorization: Bearer <token>`)  
**Default Headers for All Requests:**
```http
Accept: application/json
Content-Type: application/json
```

---

## 1. Public Web Routes Required by Google Play

| Purpose | URL | Method(s) | Description |
| :--- | :--- | :--- | :--- |
| **Privacy Policy** | `https://amter.in/privacy-policy` *(alias: `/privacy`)* | `GET` | Public privacy policy covering voice data, microphone permissions (`RECORD_AUDIO`), Razorpay & Google Play Billing, and data rights. |
| **Account & Data Deletion** | `https://amter.in/delete-account` | `GET`, `POST` | Public Google Play Data Safety URL allowing users to verify their credentials and permanently delete their account and associated learning data immediately. |
| **First-Party TTS Audio Stream** | `https://amter.in/tts-stream?text=...&tl=en-US` | `GET` | Streams cached `audio/mpeg` pronunciation audio for any English phrase. |

---

## 2. Authentication Endpoints

### 2.1 `POST /api/login`
Authenticates an existing user and issues a Laravel Sanctum personal access token.

#### Request Payload
```json
{
  "email": "student@example.com",
  "password": "secret123",
  "device_name": "Samsung Galaxy S24"
}
```
| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `email` | `string` | Yes | Registered email address |
| `password` | `string` | Yes | Account password |
| `device_name` | `string` | No | Device identifier for token naming (defaults to `User-Agent` or `amter-mobile-app`) |

#### Success Response (`200 OK`)
```json
{
  "success": true,
  "message": "Login successful.",
  "token": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ0123456789",
  "token_type": "Bearer",
  "user": {
    "id": 42,
    "name": "Rahul Nair",
    "email": "student@example.com",
    "role": "student",
    "level": "beginner",
    "is_suspended": false,
    "has_active_subscription": true,
    "is_paid": true,
    "membership_type": "Paid",
    "subscription_expires_at": "2026-12-14T09:30:00+05:30",
    "plan": {
      "id": 3,
      "product_id": "amter_30_days",
      "google_product_id": "amter_plan_3",
      "name": "1 Month Fluency",
      "price": 225,
      "currency": "INR",
      "duration_days": 30,
      "effective_daily_price": 7.5,
      "discount_percentage": 25,
      "is_best_value": false,
      "is_active": true,
      "description": "Full unlimited access to all Amter Spoken English classes and AI voice matching for 30 days."
    },
    "created_at": "2026-10-01T12:00:00+05:30"
  }
}
```

#### Error Responses
- **`401 Unauthorized`** (Invalid credentials):
```json
{
  "success": false,
  "message": "The provided credentials do not match our records.",
  "errors": {
    "email": ["The provided credentials do not match our records."]
  }
}
```
- **`403 Forbidden`** (Suspended account):
```json
{
  "success": false,
  "message": "Your account has been suspended. Please contact support at amterglobal@gmail.com."
}
```

---

### 2.2 `POST /api/register`
Creates a new student account and returns a Sanctum Bearer token immediately.

#### Request Payload
```json
{
  "name": "Rahul Nair",
  "email": "student@example.com",
  "password": "secret123",
  "password_confirmation": "secret123",
  "device_name": "Samsung Galaxy S24"
}
```
| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `name` | `string` | Yes | Student's full name (max 255 chars) |
| `email` | `string` | Yes | Unique valid email address |
| `password` | `string` | Yes | Minimum 6 characters |
| `password_confirmation` | `string` | No | Must match `password` if provided |
| `device_name` | `string` | No | Optional mobile device name |

#### Success Response (`201 Created`)
```json
{
  "success": true,
  "message": "Registration successful.",
  "token": "2|xYz9876543210aBcDeFgHiJkLmNoPqRsTuVw",
  "token_type": "Bearer",
  "user": {
    "id": 43,
    "name": "Rahul Nair",
    "email": "student@example.com",
    "role": "student",
    "level": null,
    "is_suspended": false,
    "has_active_subscription": false,
    "is_paid": false,
    "membership_type": "Registered",
    "subscription_expires_at": null,
    "plan": null,
    "created_at": "2026-11-14T09:30:00+05:30"
  }
}
```

---

### 2.3 `POST /api/logout`
Revokes the active Bearer token for the current device.

- **Auth Required:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "success": true,
  "message": "Logged out successfully."
}
```

---

## 3. User Profile & Account Management

### 3.1 `GET /api/user` *(alias: `GET /api/profile`)*
Returns the authenticated user's profile, active subscription status, learning streak/progress stats, and recent payment history.

- **Auth Required:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "success": true,
  "user": {
    "id": 42,
    "name": "Rahul Nair",
    "email": "student@example.com",
    "role": "student",
    "level": "beginner",
    "is_suspended": false,
    "has_active_subscription": true,
    "is_paid": true,
    "membership_type": "Paid",
    "subscription_expires_at": "2026-12-14T09:30:00+05:30",
    "plan": {
      "id": 3,
      "product_id": "amter_30_days",
      "google_product_id": "amter_plan_3",
      "name": "1 Month Fluency",
      "price": 225,
      "currency": "INR",
      "duration_days": 30,
      "effective_daily_price": 7.5,
      "discount_percentage": 25,
      "is_best_value": false,
      "is_active": true,
      "description": "Full unlimited access to all Amter Spoken English classes and AI voice matching for 30 days."
    },
    "created_at": "2026-10-01T12:00:00+05:30",
    "stats": {
      "completed_units_count": 18,
      "mastery_streak_days": 4,
      "resume_unit_id": 5311,
      "resume_unit_title": "Lesson 19",
      "resume_session_id": 12
    }
  },
  "recent_payments": [
    {
      "id": 105,
      "order_id": "GPA.3312-4567-8901-23456",
      "payment_id": "amter_30_days",
      "amount": 225,
      "currency": "INR",
      "method": "google_play",
      "status": "completed",
      "plan_name": "1 Month Fluency",
      "created_at": "2026-11-14T09:30:00+05:30"
    }
  ]
}
```

---

### 3.2 `PUT /api/profile` *(or `PATCH /api/profile`)*
Updates the authenticated user's name, email, or password.

- **Auth Required:** `Authorization: Bearer <token>`

#### Request Payload
```json
{
  "name": "Rahul K. Nair",
  "email": "rahul@example.com",
  "current_password": "oldPassword123",
  "password": "newPassword456"
}
```

#### Success Response (`200 OK`)
```json
{
  "success": true,
  "message": "Profile updated successfully.",
  "user": {
    "id": 42,
    "name": "Rahul K. Nair",
    "email": "rahul@example.com",
    "role": "student",
    "has_active_subscription": true,
    "membership_type": "Paid",
    "subscription_expires_at": "2026-12-14T09:30:00+05:30"
  }
}
```

---

### 3.3 `DELETE /api/user` *(alias: `POST /api/delete-account`)*
Permanently deletes the authenticated user's account, revokes all Sanctum tokens, and removes personal learning progress (in-app account deletion required by Google Play).

- **Auth Required:** `Authorization: Bearer <token>`

#### Optional Request Payload
```json
{
  "password": "secret123"
}
```

#### Success Response (`200 OK`)
```json
{
  "success": true,
  "message": "Your account and associated personal data have been permanently deleted."
}
```

---

## 4. Digital Products & Learning Content Endpoints

### 4.1 `GET /api/products` *(alias: `GET /api/plans`)*
Lists all active digital subscription plans and their Google Play product SKUs.

- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "success": true,
  "products": [
    {
      "id": 1,
      "product_id": "amter_1_days",
      "google_product_id": "amter_plan_1",
      "name": "1 Day Pass",
      "price": 10,
      "currency": "INR",
      "duration_days": 1,
      "effective_daily_price": 10,
      "discount_percentage": 0,
      "is_best_value": false,
      "is_active": true,
      "description": "Full unlimited access to all Amter Spoken English classes and AI voice matching for 1 day."
    },
    {
      "id": 3,
      "product_id": "amter_30_days",
      "google_product_id": "amter_plan_3",
      "name": "1 Month Fluency",
      "price": 225,
      "currency": "INR",
      "duration_days": 30,
      "effective_daily_price": 7.5,
      "discount_percentage": 25,
      "is_best_value": false,
      "is_active": true,
      "description": "Full unlimited access to all Amter Spoken English classes and AI voice matching for 30 days."
    }
  ]
}
```

---

### 4.2 `GET /api/content`
Returns the complete digital catalog in a single request: `products` (plans), `courses` (with modules), `sessions` (Library cards ordered by Admin `sort_order`), and `tracks` (Learning Tracks).  
If an `Authorization: Bearer <token>` header is included, each session also includes the user's personalized `completed_units_count`, `progress_percent`, and `resume_unit_id`.

- **Auth Required:** Optional (`Bearer <token>`)

#### Success Response (`200 OK`)
```json
{
  "success": true,
  "products": [
    {
      "id": 3,
      "product_id": "amter_30_days",
      "google_product_id": "amter_plan_3",
      "name": "1 Month Fluency",
      "price": 225,
      "currency": "INR",
      "duration_days": 30,
      "effective_daily_price": 7.5,
      "discount_percentage": 25,
      "is_best_value": false,
      "is_active": true,
      "description": "Full unlimited access to all Amter Spoken English classes and AI voice matching for 30 days."
    }
  ],
  "courses": [
    {
      "id": 1,
      "title": "Amter Spoken English",
      "slug": "amter-spoken-english",
      "description": "Malayalam to Fluent English",
      "thumbnail_url": null,
      "units_count": 2972,
      "modules": [
        {
          "id": 1,
          "name": "ML",
          "slug": "ml",
          "description": null,
          "thumbnail_url": null,
          "sort_order": 1
        }
      ]
    }
  ],
  "sessions": [
    {
      "id": 12,
      "module_id": 1,
      "module_name": "ML",
      "title": "Freedom from shame",
      "hide_title_on_card": true,
      "slug": "freedom-from-shame",
      "description": "ഓൺലൈൻ ബുള്ളിയിങ്ങിൽ നിന്നും വീണ്ടും ജീവിതത്തിലേക്ക്",
      "duration_minutes": 45,
      "thumbnail_url": "https://amter.in/storage/media-library/obviously-thats-changed-01KV2N73.webp",
      "sort_order": 1,
      "units_count": 120,
      "completed_units_count": 3,
      "progress_percent": 3,
      "has_free_sample": true,
      "first_unit_id": 5308,
      "resume_unit_id": 5311
    }
  ],
  "tracks": [
    {
      "id": 1,
      "title": "Master Spoken English Track",
      "slug": "master-track",
      "description": "Step-by-step guided fluency track",
      "is_primary": true,
      "steps_count": 2972
    }
  ]
}
```

---

### 4.3 `GET /api/sessions`
Returns all published `CourseSession` library items ordered by `sort_order`. Supports optional `?module_id=1` filter.

- **Auth Required:** Optional (`Bearer <token>`)

---

### 4.4 `GET /api/sessions/{session}`
Returns a single `CourseSession` and its ordered list of published lessons (`units`).

- **Auth Required:** Optional (`Bearer <token>`)

#### Success Response (`200 OK`)
```json
{
  "success": true,
  "session": {
    "id": 12,
    "module_id": 1,
    "module_name": "ML",
    "title": "Freedom from shame",
    "hide_title_on_card": true,
    "slug": "freedom-from-shame",
    "description": "ഓൺലൈൻ ബുള്ളിയിങ്ങിൽ നിന്നും വീണ്ടും ജീവിതത്തിലേക്ക്",
    "duration_minutes": 45,
    "thumbnail_url": "https://amter.in/storage/media-library/obviously-thats-changed-01KV2N73.webp",
    "sort_order": 1,
    "units_count": 120,
    "completed_units_count": 1,
    "progress_percent": 1,
    "has_free_sample": true,
    "first_unit_id": 5308,
    "resume_unit_id": 5309
  },
  "units": [
    {
      "id": 5308,
      "course_session_id": 12,
      "sort_order": 1,
      "title": "Lesson 1",
      "display_title": "Lesson 1",
      "type": "interactive",
      "thumbnail_url": null,
      "is_free_sample": true,
      "is_registered_only": false,
      "is_accessible": true,
      "is_completed": true
    }
  ]
}
```

---

### 4.5 `GET /api/units/{unit}`
Returns the full interactive content blocks (`image`, `rich_text`, `text_to_speech`, `voice_match`, `listen_speak`, `quiz`, `dictation_test`, `video`, `audio`) for a specific lesson, with all relative media paths resolved to full HTTPS URLs (`resolved_image_url`, `resolved_video_url`, `resolved_audio_url`).

- **Auth Required:** Optional for `is_free_sample` lessons; Required (`Bearer <token>`) for `is_registered_only` and premium lessons.

#### Success Response (`200 OK`)
```json
{
  "success": true,
  "unit": {
    "id": 5309,
    "course_session_id": 12,
    "session_title": "Freedom from shame",
    "module_name": "ML",
    "sort_order": 2,
    "total_session_units": 120,
    "title": "Lesson 2",
    "display_title": "Lesson 2",
    "type": "interactive",
    "thumbnail_url": null,
    "video_id": null,
    "audio_url": null,
    "transcript": null,
    "is_free_sample": true,
    "is_registered_only": false,
    "is_completed": false,
    "content_blocks": [
      {
        "type": "image",
        "data": {
          "resolved_image_url": "https://amter.in/storage/media-library/obviously-thats-changed-01KV2N73.webp",
          "alt": "Lesson illustration"
        }
      },
      {
        "type": "text_to_speech",
        "data": {
          "instructions": "ഇനി പറഞ്ഞതിന്റെ ഇംഗ്ലീഷും മലയാളവും കാണുക. കേൾക്കുക.",
          "text": "You are looking at a woman who was publicly silent for a decade.",
          "meaning_malayalam": "ഒരു പതിറ്റാണ്ടുകാലം പൊതുസമൂഹത്തിന് മുന്നിൽ നിശബ്ദയായിരുന്ന ഒരു സ്ത്രീയെയാണ് നിങ്ങൾ കാണുന്നത്.",
          "default_accent": "en-US",
          "speech_gender": "female",
          "hide_english": false
        }
      },
      {
        "type": "voice_match",
        "data": {
          "phrase": ["decade."],
          "button_label": "TAP TO SPEAK & MATCH",
          "hide_english": false
        }
      }
    ],
    "navigation": {
      "previous_unit_id": 5308,
      "next_unit_id": 5310
    },
    "tts_stream_endpoint": "https://amter.in/tts-stream"
  }
}
```

#### Locked Lesson Response (`403 Forbidden`)
```json
{
  "success": false,
  "message": "This interactive lesson requires an active Premium Subscription.",
  "auth_required": false,
  "upgrade_required": true,
  "unit": {
    "id": 5320,
    "course_session_id": 12,
    "sort_order": 13,
    "title": "Lesson 13",
    "is_free_sample": false,
    "is_registered_only": false
  }
}
```

---

### 4.6 `POST /api/units/{unit}/complete`
Marks a lesson as completed for the authenticated student, triggers milestone checks, and returns the next lesson ID.

- **Auth Required:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "success": true,
  "message": "Lesson marked as completed.",
  "newly_completed": true,
  "xp_gained": 100,
  "next_unit_id": 5310,
  "mastery_streak": 5,
  "completed_units_count": 19
}
```

---

## 5. Google Play Billing Verification Endpoint

### 5.1 `POST /api/verify-google-purchase`
Verifies a Google Play Billing purchase or subscription token, records the transaction in the `payments` table (`method = "google_play"`), and activates/extends the user's `subscription_expires_at` and `plan_id`.

- **Auth Required:** `Authorization: Bearer <token>` *(or `user_id` in request payload)*
- **Idempotency:** Safe to retry; submitting the same `purchase_token` or `order_id` for the same user returns `200 OK` with `"already_verified": true` without double-extending the subscription.

#### Request Payload
```json
{
  "purchase_token": "opaque-google-play-purchase-token-string",
  "product_id": "amter_30_days",
  "plan_id": 3,
  "order_id": "GPA.3312-4567-8901-23456",
  "package_name": "in.amter.app",
  "purchase_type": "subscription"
}
```
| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `purchase_token` | `string` | Yes | Purchase token returned by Google Play Billing Library |
| `product_id` | `string` | Yes | Google Play SKU (e.g., `amter_30_days`, `amter_plan_3`, or plan ID) |
| `plan_id` | `integer` | No | Optional explicit database `plans.id` |
| `order_id` | `string` | No | Google Play `GPA.*` order ID (auto-derived from token hash if omitted) |
| `package_name` | `string` | No | Android application package name (defaults to `GOOGLE_PLAY_PACKAGE_NAME` / `in.amter.app`) |
| `purchase_type` | `string` | No | `"subscription"` (default), `"inapp"`, or `"product"` |

#### Success Response (`200 OK`)
```json
{
  "success": true,
  "already_verified": false,
  "message": "Google Play purchase verified and subscription activated.",
  "verification_mode": "google_api",
  "subscription": {
    "has_active_subscription": true,
    "membership_type": "Paid",
    "subscription_expires_at": "2026-12-14T09:30:00+05:30",
    "plan": {
      "id": 3,
      "product_id": "amter_30_days",
      "google_product_id": "amter_plan_3",
      "name": "1 Month Fluency",
      "price": 225,
      "currency": "INR",
      "duration_days": 30,
      "effective_daily_price": 7.5,
      "discount_percentage": 25,
      "is_best_value": false,
      "is_active": true,
      "description": "Full unlimited access to all Amter Spoken English classes and AI voice matching for 30 days."
    }
  },
  "payment": {
    "id": 105,
    "order_id": "GPA.3312-4567-8901-23456",
    "product_id": "amter_30_days",
    "amount": 225,
    "currency": "INR",
    "method": "google_play",
    "status": "completed"
  },
  "user": {
    "id": 42,
    "name": "Rahul Nair",
    "email": "student@example.com",
    "role": "student",
    "level": "beginner",
    "is_suspended": false,
    "has_active_subscription": true,
    "is_paid": true,
    "membership_type": "Paid",
    "subscription_expires_at": "2026-12-14T09:30:00+05:30"
  }
}
```

#### Error Responses
- **`400 Bad Request`** (Invalid or expired Google Play purchase token):
```json
{
  "success": false,
  "message": "Google Play subscription token verification failed (404)."
}
```
- **`409 Conflict`** (Purchase token already claimed by a different user):
```json
{
  "success": false,
  "message": "This Google Play purchase token has already been claimed by another account."
}
```
