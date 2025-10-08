ElderCare SG - Project Architecture Document v2.0
Document Version: 2.0
Status: Approved for Implementation
Last Updated: January 2024
Owner: Technical Architecture Team
Stakeholders: Product Management, Development Team, QA Team, DevOps Team, Compliance Officer

Table of Contents
Executive Summary
System Overview
Architecture Principles
System Architecture
Frontend Architecture
Backend Architecture
Data Architecture
Integration Architecture
Analytics & Measurement Architecture
Internationalization Architecture
Content Management Architecture
Security Architecture
Compliance Architecture
DevOps Architecture
Performance Architecture
Accessibility Architecture
Testing Strategy
Scalability Considerations
Technology Stack
Implementation Roadmap
Risk Assessment
Appendices
Conclusion
1. Executive Summary
1.1 Project Overview
ElderCare SG is a comprehensive web platform designed to connect Singaporean families with trusted elderly daycare services. The platform serves as a digital bridge between care providers and families seeking quality eldercare solutions, built with compassion, accessibility, and technical excellence at its core.

This architecture document defines the technical blueprint for delivering a production-ready platform that meets stringent regulatory requirements (PDPA, MOH guidelines, WCAG 2.1 AA), achieves ambitious performance targets (<3 seconds on 3G), and provides a seamless, multilingual user experience.

1.2 Phased Delivery Model
To balance comprehensive feature delivery with the 12-week launch timeline, we adopt a phased approach:

Phase 1: MVP Launch (Weeks 1-12)

Core booking system with Calendly integration
Service discovery and information pages
Photo galleries for centers
Testimonials system with moderation
Contact and inquiry forms
Newsletter subscription (Mailchimp)
Languages: English + Mandarin Chinese (50% translation effort)
Performance: <3 seconds on 3G for all pages
Compliance: Full PDPA and MOH compliance
Target: Launch-ready platform with essential features
Phase 2: Enhanced Features (Weeks 13-16)

Virtual tours with video integration (Cloudflare Stream)
Additional languages: Malay + Tamil
Advanced search filters
Enhanced analytics dashboards
Performance optimizations for video-heavy content
Target: Complete feature set as originally specified
1.3 Key Architectural Decisions
Decision Area	Choice	Rationale
Architecture Pattern	Service-Oriented Monolith	Single Laravel application with service layers; simpler than microservices, appropriate for initial scale, clear migration path if needed
Frontend Framework	Next.js 14 with React Server Components	SEO-critical content benefits from SSR; React 18 server components optimize performance; proven ecosystem
Backend Framework	Laravel 12 (PHP 8.2)	Mature ecosystem, rapid development, excellent documentation, strong security features, team expertise
Primary Database	MySQL 8.0 with Read Replicas	ACID compliance for transactional data, proven reliability, read replica scaling path
Caching Layer	Redis 7	Session management, application cache, queue backend; reduces database load significantly
Search Engine	MeiliSearch	Simpler than Elasticsearch, excellent multilingual support, faster setup, sufficient for MVP scale
Container Orchestration	AWS ECS (not Kubernetes)	Lower operational complexity, managed service reduces DevOps burden, suitable for initial scale
Content Management	Laravel Nova	Production-ready admin panel, faster than custom build, extensible, familiar to Laravel developers
Video Hosting	Cloudflare Stream (Phase 2)	Adaptive bitrate streaming, built-in analytics, Singapore edge locations, cost-effective
Languages (MVP)	English + Mandarin	Reduces translation effort by 50%, covers 85% of target audience, clear Phase 2 expansion
Mobile Strategy	Responsive Web (PWA)	Mobile-first design, installable PWA, native apps deferred post-launch
1.4 Success Criteria
The platform architecture is designed to achieve these measurable outcomes:

Metric	Target	Measurement Method	Timeline
Visit bookings increase	30% within 3 months	Google Analytics conversion tracking, backend booking logs	Monthly review
Mobile bounce rate	<40%	Google Analytics device-specific reports	Bi-weekly
Lighthouse Performance Score	>90	Lighthouse CI in deployment pipeline	Every deployment
Lighthouse Accessibility Score	>90	Lighthouse CI + axe-core automated tests	Every deployment
Average session duration	>5 minutes	Google Analytics engagement metrics	Monthly
Page load time (3G)	<3 seconds	WebPageTest monitoring with 3G throttling	Weekly
Form completion rate	>75%	Hotjar form analytics	Monthly
Video engagement rate (Phase 2)	>60% watch duration	Cloudflare Stream analytics	Monthly
1.5 Document Purpose & Audience
This document serves as:

Technical blueprint for development teams
Reference guide for architectural decisions and patterns
Compliance documentation for regulatory requirements
Onboarding resource for new team members
Stakeholder communication tool for technical strategy
Primary Audience: Software Engineers, DevOps Engineers, QA Engineers, Technical Leads
Secondary Audience: Product Managers, Project Managers, Compliance Officers, Stakeholders

2. System Overview
2.1 Platform Purpose
ElderCare SG addresses a critical need in Singapore's eldercare ecosystem: the lack of a trusted, transparent, accessible platform for families to discover and evaluate daycare services for their elderly loved ones. The platform transforms a fragmented, opaque decision-making process into a streamlined, informed, and confidence-building experience.

Core Value Propositions:

Trust Through Transparency: Verified MOH licenses, authentic testimonials, comprehensive facility information
Accessibility for All: WCAG 2.1 AA compliance ensures elderly users and those with disabilities can navigate independently
Cultural Sensitivity: Multilingual support (4 languages) and culturally appropriate design for Singapore's multicultural context
Informed Decision-Making: Rich information architecture with photos, virtual tours (Phase 2), staff credentials, and peer reviews
Seamless Booking: Integrated scheduling removes friction from the inquiry-to-visit journey
2.2 Target Audience Personas
Primary Persona: Adult Children (30-55 years)

Demographic: Working professionals managing care for aging parents
Tech Savvy: High digital literacy, mobile-first users
Pain Points: Limited time for research, anxiety about care quality, need for trusted recommendations
Language: Primarily English, some prefer Mandarin
Usage Pattern: Research during lunch breaks or evenings on mobile devices
Secondary Persona: Family Caregivers (45-65 years)

Demographic: Spouse or sibling providing care, seeking respite options
Tech Savvy: Moderate digital literacy, growing mobile adoption
Pain Points: Caregiver burnout, need for reliable temporary care, financial concerns
Language: Mixed (English, Mandarin, Malay)
Usage Pattern: Extended research sessions on tablets or desktop
Tertiary Persona: Healthcare Professionals (30-60 years)

Demographic: Doctors, nurses, social workers making referrals
Tech Savvy: High digital literacy
Pain Points: Need to quickly verify facility credentials, match patient needs to services
Language: Primarily English
Usage Pattern: Quick verification lookups during work hours
Quaternary Persona: Digitally Literate Seniors (65-80 years)

Demographic: Seniors seeking independence, exploring options proactively
Tech Savvy: Moderate to low, may use accessibility features
Pain Points: Small text, complex navigation, technical jargon
Language: Mixed, may prefer native language
Usage Pattern: Slower-paced browsing, higher font sizes, screen readers
2.3 MVP Features (Phase 1: Weeks 1-12)
Core Information Architecture

Service Discovery Pages: Browse daycare centers with filtering (location, services, price range)
Center Detail Pages: Comprehensive facility information including:
MOH license number and verification
Accreditation status
Staff qualifications and count
Medical facilities and emergency protocols
Operating hours
Transport information (MRT/bus routes, parking)
Amenities and features
Pricing structure
Photo galleries (8-12 photos per center)
Service Catalog: Detailed descriptions of services offered (daily care, respite care, specialized programs)
About Pages: Platform mission, how it works, our story
FAQ Section: Common questions categorized by topic
Booking System

Pre-Booking Questionnaire: Capture visitor needs and preferences before scheduling
Calendly Integration: Real-time availability display and appointment scheduling
Multi-Channel Confirmation: Email + SMS confirmation and reminders
Reminder System: Automated 72-hour and 24-hour reminders
User Dashboard: View upcoming and past bookings
Cancellation/Rescheduling: Self-service within 24-hour window
Social Proof & Trust

Testimonials System: User-submitted reviews with moderation workflow
Star Ratings: 5-star rating system per center
Verified Reviews: Indicator for bookings confirmed through platform
Response from Centers: Centers can respond to testimonials (Phase 2)
Engagement & Communication

Contact Forms: General inquiry and center-specific contact
Newsletter Subscription: Mailchimp integration with preference management
Email Notifications: Booking confirmations, reminders, account updates
SMS Notifications: Critical booking updates (Singapore numbers via Twilio)
Multilingual Support (MVP)

Languages: English (default), Mandarin Chinese
Translatable Content: All UI strings, center descriptions, service details, FAQs
Language Switcher: Persistent selection across sessions
Future: Malay and Tamil in Phase 2
User Account Management

Registration/Login: Email-based authentication with password reset
Profile Management: Edit personal information, contact details
Booking History: View past and upcoming appointments
Consent Management: Granular privacy settings
Data Export: Download personal data (PDPA compliance)
Account Deletion: Self-service with 30-day grace period
2.4 Phase 2 Features (Weeks 13-16)
Virtual Tours

Video Integration: High-quality facility tours hosted on Cloudflare Stream
Adaptive Bitrate: Automatic quality adjustment based on connection speed
Chapter Markers: Jump to specific facility areas (reception, activity room, dining area, medical facilities)
Accessibility Features: Captions, audio descriptions, keyboard controls
Analytics: Track view duration, drop-off points, completion rates
VR Support (Future): WebXR for immersive 360° tours
Additional Languages

Malay Language Support: Full translation of UI and content
Tamil Language Support: Full translation of UI and content
Professional Translation: Native speaker review for cultural appropriateness
Enhanced Search

Advanced Filters: Staff-to-patient ratio, medical facilities, language support, special programs
Saved Searches: Save filter combinations for quick access
Search Alerts: Email notifications for new centers matching criteria
Map View: Visual representation of center locations with clustering
Analytics Dashboard (Admin)

Real-Time Metrics: Current visitors, bookings today, conversion rates
Conversion Funnels: Visualize user journey drop-offs
Content Performance: Most viewed centers, popular services
User Behavior: Heatmaps, session recordings (with consent)
2.5 Out of Scope
The following features are explicitly not included in the current architecture:

Native Mobile Applications: iOS/Android apps (responsive web PWA sufficient for MVP)
Payment Processing: No online payments; pricing information only (Phase 3+)
Caregiver Matching: No algorithmic matching of caregivers to families
Medical Record Integration: No EHR or medical record access
Telehealth: No virtual consultations (potential future feature)
Family Portal: Shared accounts for family members (Phase 3+)
Provider Dashboard: Self-service portal for care centers to manage listings (admin-managed in MVP)
Live Chat: Real-time messaging (Phase 3+)
Social Features: User-to-user messaging, forums, groups
2.6 System Boundaries & Constraints
Regulatory Constraints:

PDPA (Personal Data Protection Act): All personal data must remain in Singapore; explicit consent required for all data processing; right to access, rectify, and erasure must be implemented
MOH Guidelines: Display of valid licenses mandatory; staff credentials verification required; emergency protocols must be documented
WCAG 2.1 AA: Minimum accessibility standard; annual audits required
IMDA Guidelines: Follow Singapore's digital accessibility guidelines for government-related services
Performance Constraints:

3-Second Load Target: Standard pages must load in <3 seconds on 3G (virtual tour pages exempt with explicit performance warnings)
Mobile-First: 60%+ users on mobile; mobile experience is primary design target
Bandwidth Efficiency: Optimized for Singapore's average mobile speeds (4G LTE typical, 3G fallback)
Technical Constraints:

Data Residency: AWS Singapore region only; no cross-region data replication
Browser Support: Modern browsers only (Chrome, Safari, Firefox, Edge - last 2 versions)
Device Support: Responsive design for mobile (375px+), tablet (768px+), desktop (1280px+)
Timeline Constraints:

12-Week MVP: Launch-ready platform with English + Mandarin
16-Week Complete: Full feature set with all 4 languages and virtual tours
Budget Constraints:

External Services: Calendly Pro (
12
/
u
s
e
r
/
m
o
n
t
h
)
,
M
a
i
l
c
h
i
m
p
S
t
a
n
d
a
r
d
(
12/user/month),MailchimpStandard(20/month), Laravel Nova (
199
/
s
i
t
e
/
y
e
a
r
)
,
T
w
i
l
i
o
S
M
S
(
 
199/site/year),TwilioSMS( 0.05/message)
Infrastructure: AWS costs optimized for Singapore region; Cloudflare Pro ($20/month)
Translation: Professional translation services for 4 languages
3. Architecture Principles
The ElderCare SG platform is built on seven foundational principles that guide all architectural and technical decisions.

3.1 User-Centric Design
Principle: Every architectural decision prioritizes the end-user experience, especially for elderly users and their families who may have varying levels of technical proficiency.

Implementation:

Performance First: <3-second load times ensure users with slower devices/connections can access information quickly
Progressive Enhancement: Core content accessible without JavaScript; interactivity added when available
Error Handling: User-friendly error messages with clear recovery paths (no technical jargon like "500 Internal Server Error")
Loading States: Skeleton screens and progress indicators prevent user confusion during data fetching
Mobile Optimization: Touch-friendly UI elements (minimum 44×44px tap targets), thumb-zone navigation
Example: Booking form implements multi-step wizard with clear progress indicators and ability to save/resume, reducing cognitive load for elderly users.

3.2 Accessibility First
Principle: WCAG 2.1 AA compliance is a foundational requirement integrated from the start, not retrofitted.

Implementation:

Semantic HTML: Proper use of HTML5 elements (<nav>, <main>, <article>) ensures screen reader comprehension
Radix UI Primitives: Component library with built-in keyboard navigation, focus management, and ARIA attributes
Skip Links: "Skip to main content" link for keyboard users to bypass navigation
Color Contrast: Minimum 4.5:1 contrast ratio for normal text, 3:1 for large text
Keyboard Navigation: All interactive elements accessible via Tab key with visible focus indicators
Screen Reader Testing: Regular testing with NVDA (Windows), VoiceOver (Mac/iOS)
Text Resizing: UI remains functional at 200% zoom
Motion Reduction: Respect prefers-reduced-motion CSS media query, provide UI toggle
Example: Booking form includes ARIA live regions to announce validation errors to screen reader users immediately without requiring focus change.

3.3 Security by Design
Principle: Security measures are integrated throughout the architecture, with particular attention to Singapore's PDPA requirements and healthcare data sensitivity.

Implementation:

Defense in Depth: Multiple layers of security (network, application, data)
Least Privilege: Users and services granted minimum permissions necessary
Secure by Default: Security features enabled out-of-the-box (HTTPS, secure cookies, CSRF protection)
Input Validation: All user input validated server-side (never trust client)
Output Encoding: Prevent XSS attacks through automatic escaping (React, Blade templates)
Encryption: TLS 1.3 for data in transit, AES-256 for sensitive data at rest
Security Headers: CSP, X-Frame-Options, HSTS implemented via middleware
Dependency Scanning: Automated vulnerability scanning in CI/CD (Dependabot, npm audit, composer audit)
Example: User passwords hashed with bcrypt (work factor 12), never stored in plain text or reversible encryption; session tokens rotated on privilege escalation.

3.4 Performance Optimized
Principle: The architecture prioritizes fast loading times and smooth interactions, especially on mobile devices and slower connections typical in Singapore.

Implementation:

Page-Specific Budgets: Standard pages <280 KB total weight (3-second 3G target)
Code Splitting: Route-based and component-based splitting in Next.js
Image Optimization: WebP with JPEG fallback, lazy loading, responsive images
Caching Layers: Browser cache, CDN cache (Cloudflare), application cache (Redis)
Database Optimization: Indexed queries, read replicas for heavy reads, query result caching
Async Processing: Queue jobs for non-critical operations (email sending, analytics)
CDN Distribution: Static assets served from Cloudflare edge locations (Singapore)
Example: Home page implements critical CSS inlining (<14 KB) and defers non-critical CSS, achieving First Contentful Paint <1.2 seconds on 3G.

3.5 Compliance Built-In
Principle: Regulatory compliance (PDPA, MOH, WCAG) is architected from day one, not added later.

Implementation:

Data Residency: All databases in AWS Singapore region; no cross-border data transfer
Consent Management: Granular consent tracking in database; explicit opt-in (no pre-checked boxes)
Audit Trails: Comprehensive logging of data access and modifications
Right to Access: User-facing data export functionality
Right to Erasure: Automated deletion workflow with 30-day grace period
MOH License Verification: Required field for center creation; validation workflow
Accessibility Testing: Automated (axe-core) and manual (screen reader) testing in QA process
Example: User registration flow captures explicit consent for account creation, marketing emails (separate), and SMS notifications (separate), storing consent text snapshot and timestamp.

3.6 Scalable and Maintainable
Principle: The system is designed to grow with business needs while maintaining code quality and ease of maintenance.

Implementation:

Service-Oriented Architecture: Clear separation of concerns (AuthService, BookingService, ContentService)
Repository Pattern: Abstract data access, making database changes isolated
Dependency Injection: Loose coupling between components
Automated Testing: 90%+ code coverage requirement; CI/CD gates prevent regressions
Documentation: Inline code comments, PHPDoc/JSDoc, architecture decision records
Monitoring: Real-time application performance monitoring (New Relic), error tracking (Sentry)
Horizontal Scaling: Stateless design enables adding application servers as needed
Database Scaling: Read replicas for read-heavy workloads, clear sharding strategy if needed
Example: Booking service implements repository pattern, allowing switch from MySQL to PostgreSQL (if needed) by changing repository implementation without touching service layer.

3.7 Cultural Sensitivity
Principle: The architecture supports Singapore's multicultural context with proper internationalization and localization.

Implementation:

Multilingual Architecture: i18n built into frontend (next-intl) and backend (Laravel localization)
Content Translation Workflow: Database-backed translations for CMS content, JSON files for UI strings
Language Detection: Intelligent locale detection (URL > cookie > browser > default)
Visual Design: Color palette respects cultural preferences (avoid inauspicious combinations)
Imagery: Photos reflect Singapore's diverse elderly population
Character Encoding: UTF-8 (utf8mb4 in MySQL) supports Chinese, Malay, Tamil characters
Date/Time Formatting: Locale-aware formatting (Singapore Standard Time UTC+8)
Example: Center descriptions support rich text in all 4 languages with separate translation workflow; admin cannot publish center until all required languages are translated.

4. System Architecture
4.1 Architecture Pattern: Service-Oriented Monolith
ElderCare SG implements a service-oriented monolith architecture—a single Laravel application organized into discrete service layers with clear separation of concerns. This approach balances the simplicity of a monolithic deployment with the modularity and maintainability of service-oriented design.

Why Not Microservices?

Current Scale: Expected initial traffic (~1,000-5,000 users/month) does not justify microservices complexity
Team Size: Small team (4-6 developers) benefits from simpler deployment and debugging
Timeline: 12-week MVP timeline incompatible with microservices operational overhead
Data Consistency: Transactional booking flow benefits from ACID guarantees of single database
Migration Path: The service-oriented structure provides a clear migration path to microservices if scale demands. Each service (Auth, Booking, Content, Notification) can be extracted into separate microservice with well-defined API contracts already in place.

4.2 High-Level System Architecture
mermaid

graph TB
    subgraph "Client Layer"
        A[Web Browser<br/>Desktop/Mobile]
        B[Search Engine Crawler<br/>Google/Bing]
    end
    
    subgraph "CDN Layer - Cloudflare"
        C[Edge Cache<br/>Static Assets]
        D[DDoS Protection<br/>WAF]
    end
    
    subgraph "Presentation Layer - AWS ECS"
        E[Next.js Frontend<br/>Port 3000]
        F[Laravel API Gateway<br/>Port 8000]
    end
    
    subgraph "Service Layer - Laravel"
        G[AuthService]
        H[BookingService]
        I[ContentService]
        J[NotificationService]
        K[AnalyticsService]
    end
    
    subgraph "Data Layer - AWS RDS/ElastiCache"
        L[(MySQL Primary<br/>Write)]
        M[(MySQL Replica<br/>Read)]
        N[(Redis<br/>Cache/Queue)]
        O[(MeiliSearch<br/>Search Index)]
    end
    
    subgraph "External Services"
        P[Calendly API<br/>Scheduling]
        Q[Mailchimp API<br/>Newsletter]
        R[Twilio API<br/>SMS]
        S[Cloudflare Stream<br/>Video - Phase 2]
    end
    
    subgraph "Storage - AWS S3"
        T[S3 Singapore<br/>Media Files]
    end
    
    subgraph "Monitoring"
        U[Sentry<br/>Error Tracking]
        V[New Relic<br/>APM]
        W[CloudWatch<br/>Logs]
    end
    
    A --> C
    B --> C
    C --> D
    D --> E
    D --> F
    E --> F
    F --> G
    F --> H
    F --> I
    F --> J
    F --> K
    G --> L
    H --> L
    I --> L
    J --> N
    K --> M
    G --> N
    H --> N
    I --> O
    H --> P
    J --> Q
    J --> R
    I --> S
    I --> T
    F --> U
    F --> V
    F --> W
4.3 Component Interaction Patterns
Synchronous Request Flow (User Views Center Details)

User requests /en/centers/sunshine-care
Cloudflare CDN checks cache (miss for dynamic content)
Request reaches Next.js frontend
Frontend makes API call to Laravel: GET /api/v1/centers/sunshine-care?locale=en
Laravel ContentService retrieves center from MySQL (checks Redis cache first)
ContentService fetches translations from content_translations table
Response cached in Redis (5 minutes TTL)
Next.js server-side renders page with data
HTML returned to Cloudflare, cached (if appropriate), returned to user
Asynchronous Job Flow (Booking Confirmation)

User submits booking form
Laravel BookingService validates input
Service creates booking record in MySQL (status: pending)
Service calls Calendly API to create scheduled event
Calendly returns event ID; booking updated (status: confirmed)
Service dispatches jobs to Redis queue:
SendBookingConfirmationEmail job
SendBookingConfirmationSMS job
SyncToAnalytics job
Laravel Horizon workers process jobs asynchronously
User receives immediate response; notifications sent in background
4.4 Data Flow Architecture
mermaid

graph LR
    subgraph "Write Path"
        A[User Action] --> B[Laravel Validation]
        B --> C[Service Layer]
        C --> D[Repository Layer]
        D --> E[(MySQL Primary)]
        E --> F[Event Dispatched]
        F --> G[Cache Invalidation]
    end
    
    subgraph "Read Path"
        H[API Request] --> I{Redis Cache?}
        I -->|Hit| J[Return Cached]
        I -->|Miss| K[Repository Query]
        K --> L[(MySQL Replica)]
        L --> M[Transform Response]
        M --> N[Cache in Redis]
        N --> O[Return Response]
    end
    
    subgraph "Search Path"
        P[Search Query] --> Q[MeiliSearch]
        Q --> R[Result IDs]
        R --> S[Fetch from Cache/DB]
        S --> T[Return Results]
    end
4.5 API-First Design Philosophy
All business logic is exposed through versioned RESTful APIs, enabling:

Frontend Flexibility: Next.js frontend consumes APIs; future mobile apps use same APIs
Third-Party Integration: External partners can integrate via documented APIs (future)
Testing: APIs tested independently from frontend
API Versioning Strategy:

Base URL: https://api.eldercare.sg/v1/
Version in URL path (not header) for clarity
Deprecation policy: 6-month notice, maintain 2 versions simultaneously
API Standards:

Authentication: Bearer token (Laravel Sanctum)
Content Type: application/json
Status Codes: Semantic HTTP codes (200, 201, 400, 401, 403, 404, 422, 500)
Error Format: Consistent JSON structure
JSON

{
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  }
}
Pagination: Query params ?page=1&per_page=20, meta in response
Filtering: Query params ?filter[city]=Singapore&filter[status]=active
Sorting: Query param ?sort=-created_at (- prefix for descending)
4.6 Event-Driven Architecture
Background processes and cross-cutting concerns handled via Laravel events and listeners:

Event Examples:

UserRegistered → Send welcome email, sync to Mailchimp
BookingConfirmed → Send confirmations (email/SMS), update analytics
TestimonialSubmitted → Notify moderators, log for audit
CenterUpdated → Invalidate cache, reindex search
ConsentGiven → Log to audit table, update user preferences
Queue Infrastructure:

Driver: Redis (reliable, fast, supports priority queues)
Workers: Laravel Horizon manages queue workers
Retry Strategy: 3 attempts with exponential backoff (1min, 5min, 15min)
Failed Jobs: Stored in failed_jobs table for manual retry/inspection
Priority Queues: high (SMS, critical emails), default (standard emails), low (analytics sync)
4.7 Future Microservices Migration Path
If scale demands migration to microservices, the service-oriented structure provides a clean extraction path:

Candidate Services for Extraction (Priority Order):

NotificationService → Notification Microservice

Already async via queue
Clear API boundary
Independent scaling need (high volume)
AnalyticsService → Analytics Microservice

Read-heavy workload
Can use different database (time-series DB)
Independent scaling
BookingService → Booking Microservice

Core business logic
Complex interactions with Calendly
May need different SLA
Not Recommended for Extraction:

AuthService: Tightly coupled to session management; shared security concern
ContentService: Frequent joins across tables; transaction complexity
Migration Strategy:

Implement API Gateway pattern (Kong, Tyk, or AWS API Gateway)
Extract one service at a time (strangler fig pattern)
Maintain backward compatibility during transition
Use feature flags to control traffic routing
5. Frontend Architecture
5.1 Next.js 14 Architecture Overview
The frontend is built on Next.js 14 with the App Router, leveraging React Server Components for optimal performance and SEO.

Key Architectural Decisions:

App Router: Modern routing with layouts, loading states, error boundaries
React Server Components (RSC): Server-side rendering for data-heavy pages (center listings, testimonials)
Client Components: Interactive elements (booking form, language switcher, modals)
TypeScript: Type safety across entire codebase
Tailwind CSS: Utility-first styling with custom design tokens
5.2 Server vs. Client Component Strategy
Server Components (Default):

Center listing pages
Center detail pages
Static content pages (About, FAQ)
Testimonials display
Footer, navigation (non-interactive parts)
Benefits: No JavaScript shipped to client, faster initial load, SEO-friendly

Client Components (Selective):

Booking form (form state, validation)
Language switcher (interactive dropdown)
Modal dialogs
Image galleries with lightbox
Search filters (real-time filtering)
Map view (Phase 2)
Directive: 'use client' at top of file

5.3 Directory Structure
text

frontend/
├── app/                          # Next.js App Router
│   ├── [locale]/                # Dynamic locale segment
│   │   ├── layout.tsx           # Root layout with header/footer
│   │   ├── page.tsx             # Home page
│   │   ├── centers/
│   │   │   ├── page.tsx         # Center listing (Server Component)
│   │   │   └── [slug]/
│   │   │       └── page.tsx     # Center detail (Server Component)
│   │   ├── services/
│   │   │   └── page.tsx         # Services listing
│   │   ├── booking/
│   │   │   └── page.tsx         # Booking flow (Client Component)
│   │   ├── about/
│   │   │   └── page.tsx         # About page
│   │   ├── contact/
│   │   │   └── page.tsx         # Contact page
│   │   └── dashboard/
│   │       └── page.tsx         # User dashboard (auth required)
│   ├── api/                     # API routes (proxy to Laravel)
│   └── globals.css              # Global styles
├── components/
│   ├── atoms/                   # Atomic design: smallest components
│   │   ├── Button.tsx
│   │   ├── Input.tsx
│   │   ├── Label.tsx
│   │   ├── Icon.tsx
│   │   └── Badge.tsx
│   ├── molecules/               # Combinations of atoms
│   │   ├── FormField.tsx
│   │   ├── Card.tsx
│   │   ├── NavItem.tsx
│   │   └── SearchBar.tsx
│   ├── organisms/               # Complex UI sections
│   │   ├── Header.tsx
│   │   ├── Footer.tsx
│   │   ├── ServiceCard.tsx
│   │   ├── BookingForm.tsx
│   │   ├── TestimonialCard.tsx
│   │   └── LanguageSwitcher.tsx
│   ├── templates/               # Page layouts
│   │   ├── PageLayout.tsx
│   │   └── DashboardLayout.tsx
│   └── providers/               # Context providers
│       ├── AuthProvider.tsx
│       └── ThemeProvider.tsx
├── lib/
│   ├── api.ts                   # API client functions
│   ├── utils.ts                 # Utility functions
│   └── validations.ts           # Zod schemas
├── hooks/
│   ├── useAuth.ts               # Authentication hook
│   ├── useBooking.ts            # Booking state hook
│   └── useTranslation.ts        # i18n hook
├── store/
│   └── index.ts                 # Zustand store
├── locales/                     # Translation files
│   ├── en/
│   │   ├── common.json
│   │   ├── navigation.json
│   │   └── forms.json
│   └── zh/
│       ├── common.json
│       ├── navigation.json
│       └── forms.json
├── public/
│   ├── images/
│   └── fonts/
├── types/
│   ├── api.d.ts                 # API response types
│   └── models.d.ts              # Data model types
└── middleware.ts                # Next.js middleware (locale detection)
5.4 Component Architecture (Atomic Design)
mermaid

graph TB
    subgraph "Pages"
        A[Home Page]
        B[Center Detail Page]
        C[Booking Page]
    end
    
    subgraph "Templates"
        D[PageLayout]
        E[DashboardLayout]
    end
    
    subgraph "Organisms"
        F[Header]
        G[Footer]
        H[CenterCard]
        I[BookingForm]
        J[TestimonialList]
    end
    
    subgraph "Molecules"
        K[FormField]
        L[Card]
        M[NavItem]
        N[SearchBar]
    end
    
    subgraph "Atoms"
        O[Button]
        P[Input]
        Q[Label]
        R[Icon]
    end
    
    A --> D
    B --> D
    C --> D
    D --> F
    D --> G
    B --> H
    C --> I
    A --> J
    F --> M
    H --> L
    I --> K
    J --> L
    K --> P
    K --> Q
    L --> O
    M --> R
    N --> P
Example: ServiceCard Organism

TypeScript

// components/organisms/ServiceCard.tsx
'use client'

import { Card } from '@/components/molecules/Card'
import { Button } from '@/components/atoms/Button'
import { Badge } from '@/components/atoms/Badge'
import { useTranslation } from '@/hooks/useTranslation'
import type { Service } from '@/types/models'

interface ServiceCardProps {
  service: Service
  onBookClick: (serviceId: string) => void
}

export function ServiceCard({ service, onBookClick }: ServiceCardProps) {
  const { t } = useTranslation()
  
  return (
    <Card className="p-6 hover:shadow-lg transition-shadow">
      <div className="flex justify-between items-start mb-4">
        <h3 className="text-xl font-semibold text-gray-900">
          {service.name}
        </h3>
        {service.featured && (
          <Badge variant="primary">{t('common.featured')}</Badge>
        )}
      </div>
      
      <p className="text-gray-600 mb-4 line-clamp-3">
        {service.description}
      </p>
      
      <div className="flex justify-between items-center">
        <span className="text-2xl font-bold text-primary-600">
          ${service.price}
          <span className="text-sm text-gray-500">/{service.duration}</span>
        </span>
        
        <Button 
          onClick={() => onBookClick(service.id)}
          variant="primary"
          size="md"
        >
          {t('common.book_now')}
        </Button>
      </div>
    </Card>
  )
}
5.5 State Management Strategy
Three-Tier State Management:

Server State (React Query)

Purpose: Data from API (centers, services, bookings, user profile)
Library: @tanstack/react-query v4
Features: Caching, automatic refetching, optimistic updates, infinite scrolling
Example:
TypeScript

// hooks/useCenters.ts
import { useQuery } from '@tanstack/react-query'
import { fetchCenters } from '@/lib/api'

export function useCenters(filters: CenterFilters) {
  return useQuery({
    queryKey: ['centers', filters],
    queryFn: () => fetchCenters(filters),
    staleTime: 5 * 60 * 1000, // 5 minutes
    cacheTime: 10 * 60 * 1000, // 10 minutes
  })
}
Global Client State (Zustand)

Purpose: UI state, user session, language preference
Library: zustand v4
Why Zustand: Lightweight (1 KB), no boilerplate, TypeScript-friendly
Example:
TypeScript

// store/index.ts
import create from 'zustand'
import { persist } from 'zustand/middleware'

interface AppState {
  locale: string
  setLocale: (locale: string) => void
  user: User | null
  setUser: (user: User | null) => void
  isMenuOpen: boolean
  toggleMenu: () => void
}

export const useAppStore = create<AppState>()(
  persist(
    (set) => ({
      locale: 'en',
      setLocale: (locale) => set({ locale }),
      user: null,
      setUser: (user) => set({ user }),
      isMenuOpen: false,
      toggleMenu: () => set((state) => ({ isMenuOpen: !state.isMenuOpen })),
    }),
    {
      name: 'eldercare-storage',
      partialize: (state) => ({ locale: state.locale }), // Only persist locale
    }
  )
)
Local Component State (useState, useReducer)

Purpose: Form inputs, modal open/closed, temporary UI state
Example: Booking form step state, search filter UI state
5.6 Routing & Navigation
Dynamic Locale Routing:

URL structure: /{locale}/{path} (e.g., /en/centers, /zh/centers)
Middleware detects locale from URL → cookie → browser → default to en
TypeScript

// middleware.ts
import { NextRequest, NextResponse } from 'next/server'
import { match } from '@formatjs/intl-localematcher'
import Negotiator from 'negotiator'

const locales = ['en', 'zh', 'ms', 'ta']
const defaultLocale = 'en'

function getLocale(request: NextRequest): string {
  // Check URL
  const pathname = request.nextUrl.pathname
  const pathnameLocale = locales.find(
    (locale) => pathname.startsWith(`/${locale}/`) || pathname === `/${locale}`
  )
  if (pathnameLocale) return pathnameLocale
  
  // Check cookie
  const cookieLocale = request.cookies.get('NEXT_LOCALE')?.value
  if (cookieLocale && locales.includes(cookieLocale)) return cookieLocale
  
  // Check Accept-Language header
  const headers = { 'accept-language': request.headers.get('accept-language') || '' }
  const languages = new Negotiator({ headers }).languages()
  return match(languages, locales, defaultLocale)
}

export function middleware(request: NextRequest) {
  const pathname = request.nextUrl.pathname
  
  // Skip API routes, static files
  if (
    pathname.startsWith('/api') ||
    pathname.startsWith('/_next') ||
    pathname.includes('/images/')
  ) {
    return NextResponse.next()
  }
  
  // Redirect root to locale
  if (pathname === '/') {
    const locale = getLocale(request)
    return NextResponse.redirect(new URL(`/${locale}`, request.url))
  }
  
  // Check if pathname is missing locale
  const pathnameIsMissingLocale = locales.every(
    (locale) => !pathname.startsWith(`/${locale}/`) && pathname !== `/${locale}`
  )
  
  if (pathnameIsMissingLocale) {
    const locale = getLocale(request)
    return NextResponse.redirect(new URL(`/${locale}${pathname}`, request.url))
  }
  
  return NextResponse.next()
}

export const config = {
  matcher: ['/((?!api|_next/static|_next/image|favicon.ico).*)'],
}
5.7 Asset Optimization Strategy
Images:

Format: WebP with JPEG fallback (Next.js <Image> handles automatically)
Sizing: Responsive images with srcset (Next.js <Image> generates)
Lazy Loading: Below-fold images lazy loaded (loading="lazy")
Optimization: Next.js Image Optimization API or Cloudflare Images
Example:
React

import Image from 'next/image'

<Image
  src="/images/center-sunshine.jpg"
  alt="Sunshine Care Center - Main Entrance"
  width={800}
  height={600}
  quality={85}
  loading="lazy"
  placeholder="blur"
  blurDataURL="data:image/jpeg;base64,/9j/4AAQSkZJRg..." // Low-quality placeholder
/>
Fonts:

Strategy: Self-hosted via Next.js @next/font for optimal loading
Fonts: Inter (UI), Noto Sans SC (Chinese), Noto Sans (Malay/Tamil)
Loading: font-display: swap to prevent FOIT (Flash of Invisible Text)
Subsetting: Only include necessary character sets per language
CSS:

Framework: Tailwind CSS with PurgeCSS (removes unused styles)
Critical CSS: Inlined in <head> for above-fold content
Non-Critical CSS: Loaded asynchronously
File Size: Typically <20 KB after optimization
JavaScript:

Code Splitting: Automatic route-based splitting in Next.js
Component Splitting: Dynamic imports for heavy components
Tree Shaking: Webpack removes unused code
Minification: Production builds automatically minified
Example:
React

// Dynamic import for heavy component
import dynamic from 'next/dynamic'

const BookingForm = dynamic(() => import('@/components/organisms/BookingForm'), {
  loading: () => <BookingFormSkeleton />,
  ssr: false, // Client-side only
})
5.8 Progressive Enhancement
Core Principle: Essential functionality works without JavaScript; enhancements added when available.

Implementation:

Forms: Standard HTML forms submit via POST; JavaScript enhances with validation and async submission
Navigation: Standard <a> links work; Next.js Link component enhances with client-side routing
Content: Server-rendered HTML displays immediately; JavaScript adds interactivity
Example: Contact Form

React

// Without JavaScript: Traditional form submission
<form action="/api/contact" method="POST">
  <input name="name" required />
  <input name="email" type="email" required />
  <textarea name="message" required></textarea>
  <button type="submit">Send</button>
</form>

// With JavaScript: Enhanced with client-side validation and async submission
'use client'

export function ContactForm() {
  const [isSubmitting, setIsSubmitting] = useState(false)
  
  async function handleSubmit(e: FormEvent) {
    e.preventDefault() // Prevent default only if JS available
    setIsSubmitting(true)
    
    const formData = new FormData(e.target as HTMLFormElement)
    const response = await fetch('/api/contact', {
      method: 'POST',
      body: formData,
    })
    
    if (response.ok) {
      showSuccessToast()
    } else {
      showErrorToast()
    }
    
    setIsSubmitting(false)
  }
  
  return (
    <form onSubmit={handleSubmit}>
      {/* Same HTML form structure */}
    </form>
  )
}
6. Backend Architecture
6.1 Laravel 12 Service-Oriented Architecture
The backend is a single Laravel 12 application organized into service layers with clear separation of concerns.

Architectural Pattern: Service Layer + Repository Pattern

Controllers: Thin controllers handle HTTP concerns (request/response)
Services: Business logic encapsulated in service classes
Repositories: Data access abstraction (query logic)
Models: Eloquent ORM models (data representation)
Events/Listeners: Side effects and cross-cutting concerns
6.2 Directory Structure
text

backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── V1/
│   │   │   │   │   ├── AuthController.php
│   │   │   │   │   ├── CenterController.php
│   │   │   │   │   ├── ServiceController.php
│   │   │   │   │   ├── BookingController.php
│   │   │   │   │   ├── TestimonialController.php
│   │   │   │   │   └── UserController.php
│   │   ├── Middleware/
│   │   │   ├── LocaleMiddleware.php
│   │   │   ├── LogApiRequests.php
│   │   │   └── CheckPdpaConsent.php
│   │   ├── Requests/
│   │   │   ├── BookingRequest.php
│   │   │   ├── CenterRequest.php
│   │   │   └── TestimonialRequest.php
│   │   └── Resources/
│   │       ├── CenterResource.php
│   │       ├── ServiceResource.php
│   │       ├── BookingResource.php
│   │       └── UserResource.php
│   ├── Services/
│   │   ├── AuthService.php
│   │   ├── BookingService.php
│   │   ├── ContentService.php
│   │   ├── NotificationService.php
│   │   ├── AnalyticsService.php
│   │   ├── CalendlyService.php
│   │   ├── MailchimpService.php
│   │   └── TwilioService.php
│   ├── Repositories/
│   │   ├── UserRepository.php
│   │   ├── CenterRepository.php
│   │   ├── ServiceRepository.php
│   │   ├── BookingRepository.php
│   │   └── TestimonialRepository.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Profile.php
│   │   ├── Center.php
│   │   ├── Service.php
│   │   ├── Booking.php
│   │   ├── Testimonial.php
│   │   ├── Consent.php
│   │   ├── AuditLog.php
│   │   ├── ContentTranslation.php
│   │   ├── Media.php
│   │   ├── Subscription.php
│   │   └── Faq.php
│   ├── Events/
│   │   ├── UserRegistered.php
│   │   ├── BookingConfirmed.php
│   │   ├── BookingCancelled.php
│   │   ├── TestimonialSubmitted.php
│   │   ├── CenterUpdated.php
│   │   └── ConsentGiven.php
│   ├── Listeners/
│   │   ├── SendWelcomeEmail.php
│   │   ├── SendBookingConfirmation.php
│   │   ├── SyncToMailchimp.php
│   │   ├── InvalidateCenterCache.php
│   │   └── LogConsentToAudit.php
│   ├── Jobs/
│   │   ├── SendEmailJob.php
│   │   ├── SendSmsJob.php
│   │   ├── SyncMailchimpJob.php
│   │   ├── ProcessDataExport.php
│   │   └── DeleteInactiveUsers.php
│   ├── Exceptions/
│   │   ├── Handler.php
│   │   ├── BookingException.php
│   │   └── IntegrationException.php
│   └── Providers/
│       ├── AppServiceProvider.php
│       ├── EventServiceProvider.php
│       └── RepositoryServiceProvider.php
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── routes/
│   ├── api.php
│   └── web.php
├── config/
│   ├── services.php     # External service credentials
│   └── pdpa.php         # PDPA configuration
└── tests/
    ├── Unit/
    ├── Feature/
    └── Integration/
6.3 Service Layer Pattern
Purpose: Encapsulate business logic, keep controllers thin, enable reusability and testability.

Example: BookingService

PHP

<?php

namespace App\Services;

use App\Models\Booking;
use App\Repositories\BookingRepository;
use App\Events\BookingConfirmed;
use App\Exceptions\BookingException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingService
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private CalendlyService $calendlyService,
        private NotificationService $notificationService
    ) {}

    /**
     * Create a new booking with Calendly integration
     *
     * @param array $data Booking data (user_id, center_id, service_id, date, time, questionnaire)
     * @return Booking
     * @throws BookingException
     */
    public function createBooking(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            // 1. Create booking record (status: pending)
            $booking = $this->bookingRepository->create([
                'user_id' => $data['user_id'],
                'center_id' => $data['center_id'],
                'service_id' => $data['service_id'],
                'booking_date' => $data['date'],
                'booking_time' => $data['time'],
                'questionnaire_responses' => $data['questionnaire'] ?? null,
                'status' => 'pending',
            ]);

            try {
                // 2. Create Calendly event
                $calendlyEvent = $this->calendlyService->createEvent([
                    'event_type' => $data['service']->calendly_event_type_id,
                    'start_time' => $data['date'] . ' ' . $data['time'],
                    'invitee_email' => $data['user']->email,
                    'invitee_name' => $data['user']->name,
                ]);

                // 3. Update booking with Calendly event ID
                $booking->update([
                    'calendly_event_id' => $calendlyEvent['id'],
                    'status' => 'confirmed',
                ]);

                // 4. Dispatch event (triggers email/SMS notifications)
                event(new BookingConfirmed($booking));

                // 5. Log success
                Log::info('Booking created successfully', [
                    'booking_id' => $booking->id,
                    'calendly_event_id' => $calendlyEvent['id'],
                ]);

                return $booking;

            } catch (\Exception $e) {
                // Calendly API failed - mark booking as failed, notify admin
                $booking->update(['status' => 'failed']);
                
                Log::error('Calendly integration failed', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);

                // Notify admin to manually schedule
                $this->notificationService->notifyAdminOfFailedBooking($booking, $e->getMessage());

                throw new BookingException(
                    'Booking created but scheduling failed. Our team will contact you shortly.',
                    previous: $e
                );
            }
        });
    }

    /**
     * Cancel a booking
     */
    public function cancelBooking(int $bookingId, string $reason = null): bool
    {
        $booking = $this->bookingRepository->findOrFail($bookingId);

        // Check cancellation policy (24 hours before)
        if ($booking->booking_date->diffInHours(now()) < 24) {
            throw new BookingException('Bookings can only be cancelled 24 hours in advance.');
        }

        return DB::transaction(function () use ($booking, $reason) {
            // Cancel in Calendly
            if ($booking->calendly_event_id) {
                $this->calendlyService->cancelEvent($booking->calendly_event_id);
            }

            // Update booking
            $booking->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
            ]);

            // Send notifications
            $this->notificationService->sendBookingCancellation($booking);

            return true;
        });
    }

    /**
     * Get user's upcoming bookings
     */
    public function getUserUpcomingBookings(int $userId)
    {
        return $this->bookingRepository->getUpcomingByUser($userId);
    }
}
6.4 Repository Pattern
Purpose: Abstract data access, make switching databases easier, centralize query logic.

Example: BookingRepository

PHP

<?php

namespace App\Repositories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class BookingRepository
{
    /**
     * Create a new booking
     */
    public function create(array $data): Booking
    {
        return Booking::create($data);
    }

    /**
     * Find booking by ID
     */
    public function findOrFail(int $id): Booking
    {
        return Booking::with(['user', 'center', 'service'])->findOrFail($id);
    }

    /**
     * Get upcoming bookings for a user
     */
    public function getUpcomingByUser(int $userId): Collection
    {
        $cacheKey = "user.{$userId}.bookings.upcoming";

        return Cache::remember($cacheKey, 300, function () use ($userId) {
            return Booking::where('user_id', $userId)
                ->where('booking_date', '>=', now())
                ->whereIn('status', ['confirmed', 'pending'])
                ->with(['center', 'service'])
                ->orderBy('booking_date')
                ->orderBy('booking_time')
                ->get();
        });
    }

    /**
     * Get all bookings for a center on a specific date
     */
    public function getByCenter(int $centerId, string $date): Collection
    {
        return Booking::where('center_id', $centerId)
            ->where('booking_date', $date)
            ->whereIn('status', ['confirmed', 'pending'])
            ->with('user')
            ->get();
    }

    /**
     * Update booking status
     */
    public function updateStatus(int $id, string $status): bool
    {
        $booking = $this->findOrFail($id);
        
        // Invalidate cache
        Cache::forget("user.{$booking->user_id}.bookings.upcoming");
        
        return $booking->update(['status' => $status]);
    }
}
6.5 API Controller Example
Thin controllers delegate to services:

PHP

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Http\Resources\BookingResource;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService
    ) {}

    /**
     * Create a new booking
     */
    public function store(BookingRequest $request): JsonResponse
    {
        try {
            $booking = $this->bookingService->createBooking(
                $request->validated()
            );

            return response()->json([
                'message' => 'Booking created successfully',
                'data' => new BookingResource($booking),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get user's bookings
     */
    public function index(): JsonResponse
    {
        $bookings = $this->bookingService->getUserUpcomingBookings(
            auth()->id()
        );

        return response()->json([
            'data' => BookingResource::collection($bookings),
        ]);
    }

    /**
     * Cancel a booking
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->bookingService->cancelBooking($id);

            return response()->json([
                'message' => 'Booking cancelled successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
6.6 Error Handling Architecture
Global Exception Handler:

PHP

<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response
     */
    public function render($request, Throwable $e)
    {
        // API requests get JSON responses
        if ($request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions
     */
    protected function handleApiException($request, Throwable $e)
    {
        // Validation errors
        if ($e instanceof ValidationException) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        // Model not found
        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return response()->json([
                'message' => 'Resource not found',
            ], 404);
        }

        // Custom business exceptions
        if ($e instanceof BookingException) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        // Log unexpected errors to Sentry
        if (!app()->environment('local')) {
            app('sentry')->captureException($e);
        }

        // Generic error response (hide details in production)
        $message = app()->environment('local') 
            ? $e->getMessage() 
            : 'An error occurred. Please try again later.';

        return response()->json([
            'message' => $message,
        ], 500);
    }
}
6.7 Queue Architecture
Queue Configuration:

PHP

// config/queue.php
return [
    'default' => env('QUEUE_CONNECTION', 'redis'),
    
    'connections' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => null,
        ],
    ],
    
    'failed' => [
        'driver' => 'database',
        'database' => 'mysql',
        'table' => 'failed_jobs',
    ],
];
Job Example:

PHP

<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\TwilioService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBookingConfirmationSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times job may be attempted
     */
    public $tries = 3;

    /**
     * Number of seconds to wait before retrying
     */
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    /**
     * Create a new job instance
     */
    public function __construct(
        public Booking $booking
    ) {}

    /**
     * Execute the job
     */
    public function handle(TwilioService $twilioService): void
    {
        $message = "Your visit to {$this->booking->center->name} is confirmed for " .
                   "{$this->booking->booking_date->format('d M Y')} at " .
                   "{$this->booking->booking_time}. " .
                   "Address: {$this->booking->center->address}. " .
                   "Contact: {$this->booking->center->phone}.";

        $twilioService->sendSms(
            to: $this->booking->user->phone,
            message: $message
        );
    }

    /**
     * Handle job failure
     */
    public function failed(Throwable $exception): void
    {
        // Log failure, notify admin
        \Log::error('Failed to send booking confirmation SMS', [
            'booking_id' => $this->booking->id,
            'error' => $exception->getMessage(),
        ]);

        // Notify admin to manually contact user
        // (Implementation omitted for brevity)
    }
}
Dispatching Jobs:

PHP

// High priority queue (SMS, critical emails)
SendBookingConfirmationSms::dispatch($booking)->onQueue('high');

// Default queue (standard emails)
SendBookingConfirmationEmail::dispatch($booking);

// Low priority queue (analytics, non-critical)
SyncToAnalytics::dispatch($booking)->onQueue('low');

// Delayed dispatch (24-hour reminder)
SendBookingReminder::dispatch($booking)
    ->delay($booking->booking_date->subDay());
6.8 Event-Listener Architecture
Event:

PHP

<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Booking $booking
    ) {}
}
Listeners (registered in EventServiceProvider):

PHP

<?php

namespace App\Providers;

use App\Events\BookingConfirmed;
use App\Listeners\SendBookingConfirmation;
use App\Listeners\SendBookingToAnalytics;
use App\Listeners\UpdateCenterAvailability;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        BookingConfirmed::class => [
            SendBookingConfirmation::class,  // Email + SMS
            SendBookingToAnalytics::class,   // Track conversion
            UpdateCenterAvailability::class, // Update availability
        ],
        
        UserRegistered::class => [
            SendWelcomeEmail::class,
            SyncToMailchimp::class,
        ],
        
        TestimonialSubmitted::class => [
            NotifyModerators::class,
        ],
    ];
}
7. Data Architecture
7.1 Database Schema Overview
The database schema is designed for PDPA compliance, multilingual content, and audit trails.

Database: MySQL 8.0 with utf8mb4 charset (full Unicode support)
Engine: InnoDB (ACID compliance, foreign key support)
Collation: utf8mb4_unicode_ci (case-insensitive, accurate sorting for all languages)

7.2 Entity Relationship Diagram
mermaid

erDiagram
    USERS ||--o| PROFILES : has
    USERS ||--o{ BOOKINGS : makes
    USERS ||--o{ TESTIMONIALS : writes
    USERS ||--o{ CONSENTS : gives
    USERS ||--o{ AUDIT_LOGS : generates
    
    CENTERS ||--o{ SERVICES : offers
    CENTERS ||--o{ BOOKINGS : receives
    CENTERS ||--o{ TESTIMONIALS : has
    CENTERS ||--o{ MEDIA : has
    CENTERS ||--o{ CONTENT_TRANSLATIONS : has
    
    SERVICES ||--o{ BOOKINGS : "booked for"
    SERVICES ||--o{ CONTENT_TRANSLATIONS : has
    
    TESTIMONIALS ||--o{ CONTENT_TRANSLATIONS : has
    
    FAQS ||--o{ CONTENT_TRANSLATIONS : has
    
    SUBSCRIPTIONS }o--|| USERS : "may belong to"
7.3 Complete Database Schema
Core User Tables:

SQL

-- Users table
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    phone VARCHAR(20) NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin', 'moderator', 'translator') DEFAULT 'user',
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Profiles table (extended user information)
CREATE TABLE profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED UNIQUE NOT NULL,
    avatar VARCHAR(500) NULL,
    bio TEXT NULL,
    birth_date DATE NULL,
    address VARCHAR(500) NULL,
    city VARCHAR(100) NULL,
    postal_code VARCHAR(20) NULL,
    country VARCHAR(100) DEFAULT 'Singapore',
    preferred_language VARCHAR(5) DEFAULT 'en',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_city (city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Center & Service Tables:

SQL

-- Centers table
CREATE TABLE centers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT NULL,
    address VARCHAR(500) NOT NULL,
    city VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    latitude DECIMAL(10, 8) NULL,
    longitude DECIMAL(11, 8) NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NULL,
    website VARCHAR(500) NULL,
    
    -- MOH Compliance fields
    moh_license_number VARCHAR(50) UNIQUE NOT NULL,
    license_expiry_date DATE NULL,
    accreditation_status ENUM('pending', 'accredited', 'not_accredited') DEFAULT 'pending',
    
    -- Facility information (JSON)
    staff_count INT UNSIGNED DEFAULT 0,
    medical_facilities JSON NULL COMMENT 'Array of available medical facilities',
    transport_info JSON NULL COMMENT 'MRT stations, bus routes, parking info',
    amenities JSON NULL COMMENT 'List of amenities (wheelchair access, activities, etc.)',
    languages_supported JSON NULL COMMENT 'Array of supported languages',
    
    -- Operating information
    operating_hours JSON NULL COMMENT 'Hours per day of week',
    capacity INT UNSIGNED NULL,
    
    -- Status
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    featured BOOLEAN DEFAULT FALSE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_slug (slug),
    INDEX idx_city (city),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_moh_license (moh_license_number),
    INDEX idx_deleted_at (deleted_at),
    FULLTEXT idx_search (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Services table
CREATE TABLE services (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    center_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10, 2) NULL COMMENT 'Price in SGD',
    duration VARCHAR(50) NULL COMMENT 'E.g., "day", "month", "hour"',
    features JSON NULL COMMENT 'Array of service features',
    
    -- Calendly integration
    calendly_event_type_id VARCHAR(255) NULL,
    
    -- Status
    status ENUM('active', 'inactive') DEFAULT 'active',
    featured BOOLEAN DEFAULT FALSE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE CASCADE,
    INDEX idx_center_id (center_id),
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    UNIQUE KEY unique_center_slug (center_id, slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Booking Table:

SQL

-- Bookings table
CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    center_id BIGINT UNSIGNED NOT NULL,
    service_id BIGINT UNSIGNED NOT NULL,
    
    -- Booking details
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    
    -- Calendly integration
    calendly_event_id VARCHAR(255) NULL,
    calendly_event_url VARCHAR(500) NULL,
    
    -- Pre-booking questionnaire (JSON)
    questionnaire_responses JSON NULL,
    
    -- Status
    status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'failed') DEFAULT 'pending',
    cancellation_reason TEXT NULL,
    
    -- Notes
    notes TEXT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_center_id (center_id),
    INDEX idx_booking_date (booking_date),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_calendly_event (calendly_event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Testimonial Table:

SQL

-- Testimonials table
CREATE TABLE testimonials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    center_id BIGINT UNSIGNED NOT NULL,
    
    title VARCHAR(255) NULL,
    content TEXT NOT NULL,
    rating TINYINT UNSIGNED NOT NULL COMMENT '1-5 stars',
    
    -- Moderation
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    moderated_by BIGINT UNSIGNED NULL,
    moderated_at TIMESTAMP NULL,
    moderation_notes TEXT NULL,
    
    -- Verification (booking-based testimonials are verified)
    is_verified BOOLEAN DEFAULT FALSE,
    booking_id BIGINT UNSIGNED NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE CASCADE,
    FOREIGN KEY (moderated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    
    INDEX idx_user_id (user_id),
    INDEX idx_center_id (center_id),
    INDEX idx_status (status),
    INDEX idx_rating (rating),
    INDEX idx_is_verified (is_verified),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
PDPA Compliance Tables:

SQL

-- Consents table (PDPA requirement)
CREATE TABLE consents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    
    -- Consent type (account, marketing_email, sms, cookies)
    consent_type ENUM('account', 'marketing_email', 'sms', 'cookies') NOT NULL,
    consent_given BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Record what user agreed to
    consent_text TEXT NOT NULL COMMENT 'Snapshot of privacy policy at time of consent',
    consent_version VARCHAR(10) NOT NULL COMMENT 'E.g., "1.0", "2.1"',
    
    -- Audit trail
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_consent_type (consent_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Audit logs table (compliance and security)
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    
    -- Action performed
    action VARCHAR(50) NOT NULL COMMENT 'created, updated, deleted, viewed, exported',
    
    -- Resource affected (polymorphic)
    auditable_type VARCHAR(100) NULL COMMENT 'Model class name',
    auditable_id BIGINT UNSIGNED NULL COMMENT 'Model ID',
    
    -- Changes made (JSON)
    old_values JSON NULL,
    new_values JSON NULL,
    
    -- Context
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_auditable (auditable_type, auditable_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
i18n Table:

SQL

-- Content translations table (polymorphic)
CREATE TABLE content_translations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Translatable resource (polymorphic)
    translatable_type VARCHAR(100) NOT NULL COMMENT 'Model class name',
    translatable_id BIGINT UNSIGNED NOT NULL COMMENT 'Model ID',
    
    -- Translation details
    locale VARCHAR(5) NOT NULL COMMENT 'en, zh, ms, ta',
    field VARCHAR(100) NOT NULL COMMENT 'Field being translated (name, description, etc.)',
    value TEXT NOT NULL,
    
    -- Translation workflow
    status ENUM('draft', 'translated', 'reviewed', 'published') DEFAULT 'draft',
    translated_by BIGINT UNSIGNED NULL,
    reviewed_by BIGINT UNSIGNED NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (translated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_translatable (translatable_type, translatable_id),
    INDEX idx_locale (locale),
    INDEX idx_status (status),
    UNIQUE KEY unique_translation (translatable_type, translatable_id, locale, field)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Media Table:

SQL

-- Media table (polymorphic)
CREATE TABLE media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Mediable resource (polymorphic - centers, services, testimonials)
    mediable_type VARCHAR(100) NOT NULL,
    mediable_id BIGINT UNSIGNED NOT NULL,
    
    -- Media details
    type ENUM('image', 'video', 'document') NOT NULL,
    url VARCHAR(1000) NOT NULL COMMENT 'S3 URL or Cloudflare Stream ID',
    thumbnail_url VARCHAR(1000) NULL,
    
    -- Metadata
    filename VARCHAR(255) NULL,
    mime_type VARCHAR(100) NULL,
    size INT UNSIGNED NULL COMMENT 'File size in bytes',
    duration INT UNSIGNED NULL COMMENT 'Video duration in seconds',
    
    -- Accessibility
    caption VARCHAR(500) NULL,
    alt_text VARCHAR(500) NULL,
    
    -- Ordering
    sort_order INT UNSIGNED DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_mediable (mediable_type, mediable_id),
    INDEX idx_type (type),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Newsletter & FAQ Tables:

SQL

-- Subscriptions table (newsletter)
CREATE TABLE subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    user_id BIGINT UNSIGNED NULL COMMENT 'NULL if non-registered user',
    
    -- Mailchimp integration
    mailchimp_subscriber_id VARCHAR(255) NULL,
    
    -- Status
    status ENUM('active', 'unsubscribed', 'bounced', 'pending') DEFAULT 'pending',
    
    -- Preferences (JSON)
    preferences JSON NULL COMMENT 'Content preferences, frequency',
    
    -- Timestamps
    subscribed_at TIMESTAMP NULL,
    unsubscribed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_mailchimp_id (mailchimp_subscriber_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- FAQs table
CREATE TABLE faqs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category ENUM('general', 'booking', 'services', 'pricing', 'accessibility') NOT NULL,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    
    -- Ordering
    sort_order INT UNSIGNED DEFAULT 0,
    
    -- Status
    status ENUM('active', 'inactive') DEFAULT 'active',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
7.4 Indexing Strategy
Primary Indexes (Already defined above):

Primary keys on all tables
Foreign keys for relationships
Unique indexes on email, slug, license numbers
Composite Indexes (For common query patterns):

SQL

-- Centers: Filter by city and status
CREATE INDEX idx_centers_city_status ON centers(city, status);

-- Bookings: User's upcoming bookings
CREATE INDEX idx_bookings_user_date_status ON bookings(user_id, booking_date, status);

-- Testimonials: Center's approved testimonials
CREATE INDEX idx_testimonials_center_status ON testimonials(center_id, status);

-- Content Translations: Quick locale lookup
CREATE INDEX idx_translations_locale_status ON content_translations(locale, status);
Full-Text Indexes (For search):

SQL

-- Centers: Name and description search
CREATE FULLTEXT INDEX idx_centers_fulltext ON centers(name, description);

-- Services: Name and description search
CREATE FULLTEXT INDEX idx_services_fulltext ON services(name, description);
7.5 Data Retention & Archival Policy
Data Type	Retention Period	Archival Strategy	Deletion Policy
Active user accounts	Indefinite	N/A	User-initiated only
Inactive user accounts	2 years of inactivity	Email notice → 30 days → soft delete	Hard delete after 30-day grace period
Deleted user accounts	30 days (soft delete)	N/A	Hard delete, cascade to related records
Bookings (completed)	2 years	Move to archive table	Anonymize after 2 years
Audit logs	7 years	Move to cold storage after 1 year	Delete after 7 years (legal requirement)
Consents	User lifetime + 7 years	Archive with user data	Delete 7 years after account deletion
Media files (orphaned)	90 days	N/A	Delete from S3 and database
Failed jobs	30 days	N/A	Delete
Automated Cleanup Jobs:

PHP

// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Delete inactive users (2 years + 30 days)
    $schedule->job(new DeleteInactiveUsers())->monthly();
    
    // Archive old bookings
    $schedule->job(new ArchiveOldBookings())->weekly();
    
    // Clean up orphaned media
    $schedule->job(new CleanupOrphanedMedia())->weekly();
    
    // Delete old failed jobs
    $schedule->command('queue:prune-failed --hours=720')->daily(); // 30 days
}
7.6 Backup & Recovery Strategy
Automated Backups (AWS RDS):

Frequency: Daily automated snapshots
Retention: 30 days
Window: 02:00-04:00 SGT (low traffic period)
Point-in-time Recovery: 5-day window
Manual Backups:

Frequency: Monthly
Retention: 12 months
Storage: S3 with Glacier archival after 6 months
Cross-Region Replication:

Secondary Region: AWS Tokyo (ap-northeast-1)
Purpose: Disaster recovery
Lag: <1 minute
Recovery Time Objectives (RTO):

Critical systems: 1 hour
Non-critical systems: 4 hours
Recovery Point Objectives (RPO):

Maximum data loss: 5 minutes (via point-in-time recovery)
Backup Testing:

Quarterly restore drills
Documented recovery procedures
Regular verification of backup integrity
8. Integration Architecture
ElderCare SG integrates with four critical external services: Calendly (booking), Mailchimp (newsletter), Twilio (SMS), and Cloudflare Stream (video hosting - Phase 2).

8.1 Calendly API Integration
Purpose: Real-time appointment scheduling with automatic availability management.

Authentication:

Method: Personal Access Token (stored in .env)
Token Scope: Read/write access to event types and scheduled events
Security: Token rotated quarterly, never committed to version control
Endpoints Used:

Endpoint	Method	Purpose	Frequency
/event_types	GET	List available appointment types	On service page load (cached 1 hour)
/scheduled_events	POST	Create new appointment	On booking submission
/scheduled_events/{uuid}	DELETE	Cancel appointment	On booking cancellation
Webhook: invitee.created	POST	Confirm booking	Real-time
Webhook: invitee.canceled	POST	Handle cancellations	Real-time
Data Flow:

mermaid

sequenceDiagram
    participant User
    participant Frontend
    participant Laravel
    participant CalendlyService
    participant Calendly API
    participant Queue
    
    User->>Frontend: Submit booking form
    Frontend->>Laravel: POST /api/v1/bookings
    Laravel->>CalendlyService: createEvent()
    CalendlyService->>Calendly API: POST /scheduled_events
    Calendly API-->>CalendlyService: Event created (ID: abc123)
    CalendlyService-->>Laravel: Return event data
    Laravel->>Database: Save booking (status: confirmed, calendly_event_id: abc123)
    Laravel->>Queue: Dispatch SendConfirmation jobs
    Laravel-->>Frontend: Success response
    Frontend-->>User: Show confirmation
    
    Note over Calendly API,Laravel: Later (async via webhook)
    Calendly API->>Laravel: POST /webhooks/calendly (invitee.created)
    
S
