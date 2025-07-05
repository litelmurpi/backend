# 📋 Panduan Testing API Jurnal Belajar Digital dengan Postman

## 🚀 Persiapan Awal

### 1. Install Postman
- Download Postman dari [https://www.postman.com/downloads/](https://www.postman.com/downloads/)
- Install dan buat akun (gratis)

### 2. Import Collection
Jika sudah ada collection:
1. Klik **Import** di Postman
2. Pilih file `.json` collection atau paste link collection
3. Klik **Import**

### 3. Setup Environment
1. Klik ⚙️ **Environment** di sidebar kiri
2. Klik **Create Environment**
3. Beri nama: `Jurnal Belajar - Local`
4. Tambahkan variables:

| Variable | Initial Value | Current Value |
|----------|--------------|---------------|
| `base_url` | `http://localhost:8000/api` | `http://localhost:8000/api` |
| `token` | | |
| `user_id` | | |

---

## 🔐 Authentication Endpoints

### 1. Register User
```
POST {{base_url}}/register
```

**Headers:**
```json
{
  "Content-Type": "application/json",
  "Accept": "application/json"
}
```

**Body (raw - JSON):**
```json
{
  "name": "Test User",
  "email": "test@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Expected Response (200 OK):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "Test User",
      "email": "test@example.com"
    },
    "token": "1|laravel_sanctum_token_here..."
  }
}
```

**After Success:**
- Save token ke environment variable: 
  - Klik tab **Tests**
  - Add script:
  ```javascript
  if (pm.response.code === 200) {
    pm.environment.set("token", pm.response.json().data.token);
    pm.environment.set("user_id", pm.response.json().data.user.id);
  }
  ```

### 2. Login User
```
POST {{base_url}}/login
```

**Body (raw - JSON):**
```json
{
  "email": "test@example.com",
  "password": "password123"
}
```

**Tests Script:**
```javascript
if (pm.response.code === 200) {
    const response = pm.response.json();
    pm.environment.set("token", response.data.token);
    pm.environment.set("user_id", response.data.user.id);
    
    pm.test("Login successful", function() {
        pm.expect(response.success).to.be.true;
    });
}
```

### 3. Logout User
```
POST {{base_url}}/logout
```

**Headers:**
```json
{
  "Authorization": "Bearer {{token}}"
}
```

---

## 📚 Journal Endpoints

### 1. Create Journal Entry
```
POST {{base_url}}/journals
```

**Headers:**
```json
{
  "Authorization": "Bearer {{token}}",
  "Content-Type": "application/json"
}
```

**Body:**
```json
{
  "title": "Belajar Laravel API",
  "subject": "Backend Development",
  "duration_minutes": 120,
  "date": "2025-01-05",
  "notes": "Hari ini belajar membuat RESTful API dengan Laravel",
  "resources": [
    {
      "type": "video",
      "url": "https://youtube.com/watch?v=example",
      "title": "Laravel API Tutorial"
    }
  ]
}
```

### 2. Get All Journal Entries
```
GET {{base_url}}/journals
```

**Query Parameters (optional):**
- `page=1`
- `per_page=10`
- `subject=Backend Development`
- `date_from=2025-01-01`
- `date_to=2025-01-31`

**Example:**
```
GET {{base_url}}/journals?page=1&per_page=10&subject=Backend Development
```

### 3. Get Single Journal Entry
```
GET {{base_url}}/journals/{id}
```

**Example:**
```
GET {{base_url}}/journals/1
```

### 4. Update Journal Entry
```
PUT {{base_url}}/journals/{id}
```

**Body:**
```json
{
  "title": "Belajar Laravel API (Updated)",
  "duration_minutes": 150,
  "notes": "Menambahkan authentication dengan Sanctum"
}
```

### 5. Delete Journal Entry
```
DELETE {{base_url}}/journals/{id}
```

---

## 📊 Statistics Endpoints

### 1. Get Learning Statistics
```
GET {{base_url}}/statistics/overview
```

**Query Parameters:**
- `period=week` (week/month/year)
- `date=2025-01-05`

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "total_hours": 45.5,
    "total_entries": 23,
    "current_streak": 7,
    "longest_streak": 15,
    "favorite_subject": "Backend Development",
    "daily_average": 2.5
  }
}
```

### 2. Get Heatmap Data
```
GET {{base_url}}/statistics/heatmap
```

**Query Parameters:**
- `year=2025`

**Expected Response:**
```json
{
  "success": true,
  "data": [
    {
      "date": "2025-01-01",
      "value": 120,
      "level": 3
    },
    {
      "date": "2025-01-02",
      "value": 90,
      "level": 2
    }
  ]
}
```

---

## 🎯 Goals Endpoints

### 1. Create Goal
```
POST {{base_url}}/goals
```

**Body:**
```json
{
  "title": "Belajar 100 jam di bulan Januari",
  "target_hours": 100,
  "period": "monthly",
  "start_date": "2025-01-01",
  "end_date": "2025-01-31"
}
```

### 2. Get Goal Progress
```
GET {{base_url}}/goals/{id}/progress
```

---

## 🧪 Testing Best Practices

### 1. Automated Tests dalam Postman

**Pre-request Script (untuk semua request):**
```javascript
// Check if token exists
if (!pm.environment.get("token") && !pm.request.url.path.includes("login") && !pm.request.url.path.includes("register")) {
    console.warn("No auth token found. Please login first.");
}
```

**Tests Script Template:**
```javascript
// Status code test
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

// Response time test
pm.test("Response time is less than 500ms", function () {
    pm.expect(pm.response.responseTime).to.be.below(500);
});

// Response structure test
pm.test("Response has correct structure", function () {
    const response = pm.response.json();
    pm.expect(response).to.have.property('success');
    pm.expect(response).to.have.property('data');
});

// Content type test
pm.test("Content-Type is application/json", function () {
    pm.response.to.have.header("Content-Type", "application/json");
});
```

### 2. Collection Runner

1. Klik **Runner** button di Postman
2. Select collection
3. Set environment
4. Set iterations jika perlu
5. Klik **Run**

### 3. Environment Variables untuk Testing

```javascript
// Generate random data
pm.environment.set("random_email", `test${Date.now()}@example.com`);
pm.environment.set("random_title", `Journal Entry ${Date.now()}`);
pm.environment.set("today_date", new Date().toISOString().split('T')[0]);
```

---

## 🔍 Error Testing

### 1. Test Validation Errors
```
POST {{base_url}}/journals
```

**Body (invalid data):**
```json
{
  "title": "",
  "duration_minutes": -10
}
```

**Expected Response (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required."],
    "duration_minutes": ["The duration must be at least 1."]
  }
}
```

### 2. Test Authentication Errors
Remove or use invalid token:
```
GET {{base_url}}/journals
Headers: Authorization: Bearer invalid_token
```

**Expected Response (401):**
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

---

## 📝 Sample Test Flow

### Complete User Journey Test:

1. **Register new user** → Save token
2. **Create journal entry** → Save journal_id
3. **Get all journals** → Verify entry exists
4. **Update journal** → Verify changes
5. **Get statistics** → Verify calculations
6. **Create goal** → Save goal_id
7. **Check goal progress**
8. **Delete journal**
9. **Logout**

---

## 🛠️ Troubleshooting

### Common Issues:

1. **CSRF Token Error**
   - Add `X-Requested-With: XMLHttpRequest` header
   - Or disable CSRF for API routes

2. **404 Not Found**
   - Check `base_url` in environment
   - Verify route exists: `php artisan route:list`

3. **500 Internal Server Error**
   - Check Laravel logs: `storage/logs/laravel.log`
   - Enable debug mode temporarily

4. **Token Issues**
   - Token might be expired
   - Re-login to get new token

---

## 📤 Export & Share

### Export Collection:
1. Right-click collection
2. Select **Export**
3. Choose format (v2.1 recommended)
4. Share `.json` file

### Generate Documentation:
1. Click collection
2. Click **View Documentation**
3. Click **Publish**
4. Share public URL

---

## 🔗 Useful Resources

- [Postman Learning Center](https://learning.postman.com/)
- [Laravel API Documentation](https://laravel.com/docs/api)
- [RESTful API Best Practices](https://restfulapi.net/)

---

**Pro Tips:**
- Use folders to organize endpoints by feature
- Save example responses for documentation
- Use collection variables for reusable data
- Create mock servers for frontend development
- Use monitors for automated API health checks
