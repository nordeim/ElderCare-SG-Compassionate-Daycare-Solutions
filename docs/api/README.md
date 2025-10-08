# ElderCare SG API Documentation

## Overview

This document provides information about the ElderCare SG API endpoints, authentication, and data models.

## Base URL

- Development: `http://localhost:8000/api`
- Production: `https://api.eldercare-sg.example.com`

## Authentication

The API uses Laravel Sanctum for authentication. Include the token in the Authorization header:

```
Authorization: Bearer {token}
```

## Endpoints

### Authentication

#### POST /auth/login
Login with email and password.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com"
  },
  "token": "1|abc123..."
}
```

#### POST /auth/logout
Logout the authenticated user.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "message": "Successfully logged out"
}
```

### Bookings

#### GET /bookings
Get a list of bookings for the authenticated user.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "date": "2023-06-15",
      "time": "10:00",
      "status": "confirmed",
      "center": {
        "id": 1,
        "name": "ElderCare SG Central"
      }
    }
  ]
}
```

#### POST /bookings
Create a new booking.

**Headers:**
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
  "center_id": 1,
  "date": "2023-06-15",
  "time": "10:00",
  "notes": "First visit"
}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "date": "2023-06-15",
    "time": "10:00",
    "status": "pending",
    "center": {
      "id": 1,
      "name": "ElderCare SG Central"
    }
  }
}
```

### Centers

#### GET /centers
Get a list of all daycare centers.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "ElderCare SG Central",
      "address": "123 Care Lane, Singapore 123456",
      "phone": "+65 6123 4567",
      "email": "central@eldercare-sg.example.com",
      "facilities": ["Day Care", "Rehabilitation", "Social Activities"],
      "operating_hours": {
        "monday": "8:00 AM - 6:00 PM",
        "tuesday": "8:00 AM - 6:00 PM",
        "wednesday": "8:00 AM - 6:00 PM",
        "thursday": "8:00 AM - 6:00 PM",
        "friday": "8:00 AM - 6:00 PM",
        "saturday": "Closed",
        "sunday": "Closed"
      }
    }
  ]
}
```

#### GET /centers/{id}
Get details of a specific center.

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "ElderCare SG Central",
    "address": "123 Care Lane, Singapore 123456",
    "phone": "+65 6123 4567",
    "email": "central@eldercare-sg.example.com",
    "description": "Our flagship center offering comprehensive daycare services...",
    "facilities": ["Day Care", "Rehabilitation", "Social Activities"],
    "operating_hours": {
      "monday": "8:00 AM - 6:00 PM",
      "tuesday": "8:00 AM - 6:00 PM",
      "wednesday": "8:00 AM - 6:00 PM",
      "thursday": "8:00 AM - 6:00 PM",
      "friday": "8:00 AM - 6:00 PM",
      "saturday": "Closed",
      "sunday": "Closed"
    },
    "images": [
      {
        "url": "https://example.com/images/center1-1.jpg",
        "caption": "Main activity area"
      }
    ],
    "staff": [
      {
        "id": 1,
        "name": "Dr. Jane Smith",
        "position": "Center Director",
        "qualification": "PhD in Gerontology",
        "experience": "15 years"
      }
    ]
  }
}
```

### Programs

#### GET /programs
Get a list of all programs.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Day Programs",
      "description": "Engaging daily activities...",
      "duration": "Full day",
      "price": "$50 per day",
      "activities": ["Art Therapy", "Music Therapy", "Physical Exercise"]
    }
  ]
}
```

### Testimonials

#### GET /testimonials
Get a list of testimonials.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "John Tan",
      "relation": "Son",
      "content": "The care provided to my mother has been exceptional...",
      "rating": 5,
      "date": "2023-05-15"
    }
  ]
}
```

## Error Responses

All error responses follow this format:

```json
{
  "message": "Error message",
  "errors": {
    "field": ["Error details for this field"]
  }
}
```

## Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Unprocessable Entity
- `500` - Internal Server Error
