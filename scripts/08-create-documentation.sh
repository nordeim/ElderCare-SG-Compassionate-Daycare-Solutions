#!/bin/bash
# scripts/08-create-documentation.sh

set -e

echo "📚 Creating documentation..."

# Create API documentation
cat > docs/api/README.md << 'EOF'
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
EOF

# Create deployment documentation
cat > docs/deployment/README.md << 'EOF'
# ElderCare SG Deployment Guide

## Overview

This guide provides instructions for deploying the ElderCare SG application to various environments.

## Prerequisites

- Docker and Docker Compose installed
- SSH access to the server
- Domain name configured
- SSL certificates (for production)

## Environment Setup

### Development Environment

1. Clone the repository:
   ```bash
   git clone https://github.com/eldercare-sg/web-platform.git
   cd web-platform
   ```

2. Copy environment files:
   ```bash
   cp .env.example .env
   cp frontend/.env.local.example frontend/.env.local
   ```

3. Start the development environment:
   ```bash
   docker-compose up -d
   ```

4. Run database migrations:
   ```bash
   docker-compose exec backend php artisan migrate
   ```

5. Seed the database (optional):
   ```bash
   docker-compose exec backend php artisan db:seed
   ```

### Production Environment

1. Set up the server:
   ```bash
   # Update system packages
   sudo apt update && sudo apt upgrade -y
   
   # Install Docker
   curl -fsSL https://get.docker.com -o get-docker.sh
   sudo sh get-docker.sh
   
   # Install Docker Compose
   sudo curl -L "https://github.com/docker/compose/releases/download/v2.20.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
   sudo chmod +x /usr/local/bin/docker-compose
   ```

2. Clone the repository:
   ```bash
   git clone https://github.com/eldercare-sg/web-platform.git
   cd web-platform
   ```

3. Set up environment variables:
   ```bash
   cp .env.example .env
   # Edit .env with production values
   ```

4. Create SSL certificates:
   ```bash
   mkdir -p docker/nginx/ssl
   # Copy your SSL certificates to docker/nginx/ssl/
   ```

5. Deploy the application:
   ```bash
   docker-compose -f docker-compose.prod.yml up -d --build
   ```

6. Run database migrations:
   ```bash
   docker-compose -f docker-compose.prod.yml exec backend php artisan migrate --force
   ```

7. Optimize the application:
   ```bash
   docker-compose -f docker-compose.prod.yml exec backend php artisan config:cache
   docker-compose -f docker-compose.prod.yml exec backend php artisan route:cache
   docker-compose -f docker-compose.prod.yml exec backend php artisan view:cache
   ```

## Monitoring and Maintenance

### Logs

View application logs:
```bash
# Frontend logs
docker-compose logs -f frontend

# Backend logs
docker-compose logs -f backend

# Database logs
docker-compose logs -f mysql
```

### Backups

Create database backup:
```bash
./scripts/backup.sh
```

### Updates

Update the application:
```bash
git pull origin main
docker-compose -f docker-compose.prod.yml up -d --build
```

## Troubleshooting

### Common Issues

1. **Database connection errors**
   - Check database credentials in .env file
   - Ensure database container is running
   - Check database logs for errors

2. **Frontend build errors**
   - Check Node.js version compatibility
   - Clear node_modules and reinstall dependencies
   - Check for any syntax errors in the code

3. **Permission issues**
   - Ensure proper file permissions for storage directories
   - Check user and group ownership of files

### Performance Optimization

1. **Database optimization**
   - Regularly optimize database tables
   - Implement proper indexing
   - Monitor slow queries

2. **Caching**
   - Implement Redis caching for frequently accessed data
   - Use CDN for static assets
   - Enable browser caching

3. **Server resources**
   - Monitor CPU and memory usage
   - Scale resources as needed
   - Implement load balancing for high traffic
EOF

# Create design system documentation
cat > docs/design-system/README.md << 'EOF'
# ElderCare SG Design System

## Overview

This design system provides guidelines and components for the ElderCare SG platform, ensuring consistency, accessibility, and a user-friendly experience.

## Brand Identity

### Logo

The ElderCare SG logo represents care, compassion, and community. It should be used consistently across all materials.

### Color Palette

| Color | Hex Code | Usage |
|-------|----------|-------|
| Deep Blue | #1C3D5A | Primary brand color, headers, important CTAs |
| Off-White | #F7F9FC | Main background |
| Gold | #F0A500 | Highlights, accents, primary CTAs |
| Soft Amber | #FCDFA6 | Card backgrounds, secondary highlights |
| Calming Green | #3D9A74 | Success states, wellness features |
| Slate Gray 1 | #334155 | Primary text |
| Slate Gray 2 | #64748B | Secondary text |

### Typography

#### Fonts

- **Headings**: Playfair Display
- **Body Text**: Inter

#### Typography Scale

| Style | Font | Weight | Size | Line Height |
|-------|------|--------|------|-------------|
| H1 | Playfair Display | 700 | 40-64px | 1.25em |
| H2 | Playfair Display | 600 | 32-48px | 1.25em |
| H3 | Playfair Display | 600 | 24-32px | 1.25em |
| Body | Inter | 400 | 16-18px | 1.5em |
| Small | Inter | 400 | 14px | 1.5em |

## Components

### Buttons

#### Primary Button
Used for primary actions and CTAs.

```jsx
<Button variant="default">Button Text</Button>
```

#### Secondary Button
Used for secondary actions.

```jsx
<Button variant="secondary">Button Text</Button>
```

#### CTA Button
Used for important call-to-action elements.

```jsx
<Button variant="cta">Button Text</Button>
```

### Cards

Used to group related content and actions.

```jsx
<Card>
  <CardHeader>
    <CardTitle>Card Title</CardTitle>
    <CardDescription>Card Description</CardDescription>
  </CardHeader>
  <CardContent>
    Card content goes here
  </CardContent>
  <CardFooter>
    <Button>Action</Button>
  </CardFooter>
</Card>
```

## Layout

### Grid System

- **Container**: Max width of 1280px with 24px gutters
- **Columns**: 12-column responsive grid
- **Breakpoints**:
  - Mobile: < 768px
  - Tablet: 768px - 1023px
  - Desktop: ≥ 1024px

### Spacing

- **Base unit**: 4px
- **Scale**: 4, 8, 12, 16, 20, 24, 32, 40, 48, 64, 80, 96px

## Icons

We use Lucide icons for consistency and clarity. Icons should be used to:

- Enhance meaning
- Improve navigation
- Indicate status
- Represent actions

## Imagery

### Photography

- Focus on authentic, warm interactions
- Diverse representation of elderly individuals
- Bright, natural lighting
- High-resolution images

### Videos

- Professional production quality
- Subtitles for accessibility
- Autoplay with user controls
- Optimized for web (WebM format preferred)

## Accessibility

### Color Contrast

All text and background color combinations must meet WCAG 2.1 AA standards:
- Normal text: 4.5:1
- Large text: 3:1
- Non-text elements: 3:1

### Keyboard Navigation

All interactive elements must be accessible via keyboard:
- Clear focus indicators
- Logical tab order
- Skip navigation links

### Screen Reader Support

- Semantic HTML5 elements
- ARIA labels and roles
- Alt text for images
- Descriptive link text

## Animation

### Principles

- Purposeful and meaningful
- Subtle and natural
- Respectful of user preferences
- Performance optimized

### Guidelines

- Duration: 200-500ms for most animations
- Easing: ease-out for entrance animations
- Reduced motion: Respect prefers-reduced-motion setting

## Voice and Tone

### Principles

- Warm and compassionate
- Clear and simple
- Respectful and dignified
- Professional yet approachable

### Guidelines

- Use simple language
- Avoid jargon
- Be direct and clear
- Show empathy and understanding

## Usage

### Implementation

1. Import components from the design system
2. Use established color and typography tokens
3. Follow layout and spacing guidelines
4. Ensure accessibility standards are met

### Customization

While the design system provides a solid foundation, it's designed to be flexible:

- Extend with additional components as needed
- Adjust spacing within the established scale
- Use color palette for additional UI states
- Follow animation principles for custom animations

## Maintenance

The design system is a living document that evolves with the product:

- Regular reviews and updates
- Component versioning
- Documentation updates
- User feedback incorporation
EOF

# Create accessibility documentation
cat > docs/accessibility/README.md << 'EOF'
# ElderCare SG Accessibility Guide

## Overview

This guide outlines the accessibility standards and practices implemented in the ElderCare SG platform to ensure it's usable by everyone, including people with disabilities.

## Standards Compliance

The ElderCare SG platform aims to comply with:

- WCAG 2.1 AA (Web Content Accessibility Guidelines)
- Singapore's IMDA accessibility guidelines
- Section 508 of the Rehabilitation Act

## Key Accessibility Features

### Keyboard Navigation

All interactive elements are fully accessible via keyboard:

- **Tab Order**: Logical and intuitive navigation
- **Focus Indicators**: Clear visible focus states
- **Skip Links**: Quick navigation to main content
- **Keyboard Shortcuts**: Enhanced navigation for power users

### Screen Reader Support

The platform is optimized for screen readers:

- **Semantic HTML**: Proper use of HTML5 elements
- **ARIA Labels**: Descriptive labels for complex elements
- **Alt Text**: Meaningful descriptions for images
- **Heading Structure**: Logical heading hierarchy

### Visual Accessibility

Visual design considerations for accessibility:

- **Color Contrast**: All text meets WCAG AA contrast ratios
- **Text Resizing**: Text remains readable at 200% zoom
- **Color Independence**: Information not conveyed by color alone
- **Focus Indicators**: High contrast focus states

### Motor Accessibility

Features to assist users with motor impairments:

- **Large Click Targets**: Minimum 44×44px touch targets
- **Spacing**: Adequate spacing between interactive elements
- **Error Prevention**: Confirmation for destructive actions
- **Time Limits**: Sufficient time to complete tasks

## Implementation Guidelines

### HTML Structure

Use semantic HTML5 elements:

```html
<header>
  <nav>
    <ul>
      <li><a href="/">Home</a></li>
      <li><a href="/programs">Programs</a></li>
    </ul>
  </nav>
</header>

<main>
  <section>
    <h1>Page Title</h1>
    <p>Page content</p>
  </section>
</main>

<footer>
  <p>Footer content</p>
</footer>
```

### ARIA Usage

Use ARIA attributes to enhance accessibility:

```html
<button aria-expanded="false" aria-controls="menu" id="menu-button">
  Menu
</button>
<ul id="menu" aria-labelledby="menu-button" hidden>
  <li><a href="/">Home</a></li>
  <li><a href="/programs">Programs</a></li>
</ul>
```

### Form Accessibility

Ensure forms are accessible:

```html
<form>
  <fieldset>
    <legend>Contact Information</legend>
    
    <div class="form-group">
      <label for="name">Name</label>
      <input type="text" id="name" name="name" required aria-describedby="name-help">
      <div id="name-help" class="help-text">Please enter your full name</div>
    </div>
    
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required aria-describedby="email-error">
      <div id="email-error" class="error-message" aria-live="polite"></div>
    </div>
    
    <button type="submit">Submit</button>
  </fieldset>
</form>
```

### Image Accessibility

Provide meaningful alt text for images:

```html
<img src="/images/center-activity.jpg" alt="Elderly participants engaging in a group art therapy session">
<img src="/images/logo.png" alt="ElderCare SG logo">
<img src="/images/decoration.png" alt="" role="presentation">
```

## Testing

### Automated Testing

We use automated tools to check for accessibility issues:

- **axe-core**: JavaScript library for accessibility testing
- **Lighthouse**: Performance and accessibility auditing
- **Pa11y**: Automated accessibility testing

### Manual Testing

Manual testing is performed to catch issues that automated tools might miss:

- **Keyboard Navigation**: Testing all functionality with keyboard only
- **Screen Reader Testing**: Using NVDA, VoiceOver, and JAWS
- **Color Contrast**: Verifying contrast ratios with tools
- **Zoom Testing**: Testing at 200% and 400% zoom

### User Testing

We conduct accessibility testing with users with disabilities:

- **Blind and Low Vision Users**: Screen reader usability
- **Motor Impairments**: Keyboard and alternative input methods
- **Cognitive Disabilities**: Simplified navigation and content

## Best Practices

### Content

- **Plain Language**: Use simple, clear language
- **Consistent Navigation**: Predictable navigation patterns
- **Error Messages**: Clear, helpful error messages
- **Instructions**: Provide clear instructions for complex tasks

### Design

- **Consistent Layout**: Predictable page structure
- **White Space**: Adequate spacing for readability
- **Typography**: Clear, readable fonts at appropriate sizes
- **Color**: Sufficient contrast and avoid color-only information

### Development

- **Progressive Enhancement**: Core functionality works without JavaScript
- **Focus Management**: Proper focus handling in dynamic content
- **Error Prevention**: Confirmations for destructive actions
- **Performance**: Fast loading for users with slow connections

## Resources

### Guidelines and Standards

- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [IMDA Accessibility Guidelines](https://www.imda.gov.sg/infocomm-media-landscape/digital-inclusion/accessibility)
- [ARIA Authoring Practices](https://www.w3.org/TR/wai-aria-practices-1.1/)

### Testing Tools

- [axe DevTools](https://www.deque.com/axe/devtools/)
- [WAVE Web Accessibility Evaluation Tool](https://wave.webaim.org/)
- [Colour Contrast Analyser](https://www.tpgi.com/color-contrast-checker/)
- [Screen Reader Simulators](https://webaim.org/articles/screenreader_simulator/)

### Learning Resources

- [WebAIM: Web Accessibility In Mind](https://webaim.org/)
- [A11y Project](https://www.a11yproject.com/)
- [Inclusive Design Principles](https://inclusivedesignprinciples.org/)
EOF

echo "✅ Documentation created successfully!"
