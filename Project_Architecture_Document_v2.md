ElderCare SG - Project Architecture Document v2.0
Document Version: 2.0
Status: Approved for Implementation
Last Updated: January 2024
Owner: Technical Architecture Team
Stakeholders: Product Management, Engineering, QA, Compliance, UX/UI

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
ElderCare SG is a compassionate, accessibility-first web platform designed to connect Singaporean families with trusted elderly daycare services. This architecture document defines the technical blueprint for building a secure, scalable, and compliant platform that serves Singapore's multicultural elderly care community.

Platform Vision
The platform serves as a digital bridge between families seeking quality eldercare and service providers, emphasizing trust, transparency, and cultural sensitivity. Built on modern web technologies with compliance and accessibility as foundational requirements, ElderCare SG will provide families with comprehensive information, virtual engagement tools, and seamless booking capabilities.

Phased Delivery Model
To ensure realistic delivery timelines while maintaining quality standards, we adopt a phased approach:

Phase 1 - MVP (Weeks 1-12):

Core service discovery and information architecture
Photo galleries showcasing care environments
Integrated booking system with automated confirmations
Testimonial and review system
Contact and inquiry forms
Newsletter subscription management
English and Mandarin Chinese language support
PDPA and MOH compliance implementation
WCAG 2.1 AA accessibility compliance
Phase 2 - Enhanced Features (Weeks 13-16):

Virtual tours with video and accessibility features
Malay and Tamil language support
Advanced search filters and faceted navigation
Enhanced analytics and user behavior tracking
Additional integrations and optimizations
Key Architectural Decisions
Decision Area	Choice	Rationale
Architecture Pattern	Service-Oriented Monolith	Single Laravel application with clear service layers; simpler operations than microservices while maintaining modularity
Frontend Framework	Next.js 14 with React Server Components	Optimal SEO performance, excellent developer experience, server-side rendering for content-heavy pages
Backend Framework	Laravel 12 (PHP 8.2)	Mature ecosystem, excellent documentation, rapid development for CRUD operations, strong security features
Primary Database	MySQL 8.0	Proven reliability, excellent performance for relational data, broad expertise availability
Caching Layer	Redis 7	High-performance in-memory store for sessions, application cache, and queue management
Search Engine	MeiliSearch	Superior multilingual support, simpler than Elasticsearch, excellent performance for expected data volumes
Infrastructure	AWS ECS (Elastic Container Service)	Managed container orchestration, lower complexity than Kubernetes, sufficient for scale requirements
CDN	Cloudflare	Singapore edge locations, integrated security (WAF, DDoS), excellent performance
CMS	Laravel Nova	Production-ready admin panel, rapid development, extensible, excellent Laravel integration
Video Hosting	Cloudflare Stream (Phase 2)	Adaptive bitrate streaming, built-in analytics, global delivery, Singapore presence
Success Criteria
The platform will be measured against eight key metrics aligned with business objectives:

Metric	Target	Measurement Method	Review Frequency
Visit booking conversions	30% increase within 3 months	Google Analytics conversion tracking	Monthly
Mobile bounce rate	<40%	GA4 device-specific engagement reports	Bi-weekly
Lighthouse Performance Score	>90	Lighthouse CI in deployment pipeline	Every deployment
Lighthouse Accessibility Score	>90	Lighthouse CI + axe-core automated tests	Every deployment
Average session duration	>5 minutes	GA4 engagement metrics	Monthly
Page load time (3G connection)	<3 seconds (standard pages)	WebPageTest monitoring from Singapore	Weekly
Form completion rate	>75%	Hotjar form analytics	Monthly
Video engagement rate (Phase 2)	>60% watch duration	Cloudflare Stream analytics	Monthly
Technology Stack Summary
Frontend: Next.js 14, React 18, TypeScript 5, Tailwind CSS 3, Radix UI, Framer Motion 10, React Query 4, Zustand 4

Backend: Laravel 12, PHP 8.2, MySQL 8.0, Redis 7, MeiliSearch, Laravel Sanctum, Laravel Nova

Infrastructure: Docker, AWS ECS, AWS RDS, AWS S3, Cloudflare CDN, GitHub Actions, Sentry, New Relic

Document Purpose
This architecture document serves as the authoritative technical reference for:

Development Teams: Detailed implementation guidance and technical specifications
QA Engineers: Testing strategies, quality gates, and acceptance criteria
DevOps Engineers: Infrastructure setup, deployment processes, and operational procedures
Product Managers: Technical capabilities, constraints, and feature delivery timelines
Compliance Officers: PDPA and MOH regulatory implementation details
Stakeholders: High-level technical overview and decision rationale
2. System Overview
Platform Purpose
ElderCare SG addresses a critical gap in Singapore's elderly care ecosystem: the difficulty families face in discovering, evaluating, and engaging with quality daycare services. The platform provides transparent, comprehensive information about care centers, enabling informed decision-making during an emotionally challenging time.

By combining detailed facility information, authentic testimonials, professional photography, and seamless booking capabilities, the platform reduces friction in the care selection process while building trust through transparency and verification.

Target Audience
Primary Audience: Adult Children (30-55 years)

Median household income: SGD 70,000-120,000 annually
Predominantly English-speaking with secondary language proficiency (Mandarin, Malay, Tamil)
Tech-savvy, mobile-first users (70% mobile device usage expected)
Research extensively before making care decisions (avg. 8-12 touchpoints)
Value transparency, certifications, and peer reviews
Time-constrained due to work and family obligations
Secondary Audience: Family Caregivers (25-45 years)

Domestic helpers and professional caregivers
May have limited English proficiency (requiring multilingual support)
Seeking detailed information about services and facilities
Need clear transportation and contact information
Tertiary Audience: Healthcare Professionals (30-60 years)

Doctors, nurses, social workers making referrals
Require verification of licenses, accreditations, and medical facilities
Need quick access to contact information and specializations
Value professional certifications and staff credentials
Quaternary Audience: Elderly Individuals (55+ years)

Digitally literate seniors seeking independence
May have visual or motor impairments (accessibility critical)
Prefer larger text, high contrast, simple navigation
Value clear communication and respectful tone
MVP Features (Phase 1: Weeks 1-12)
Service Discovery & Information

Comprehensive center listings with detailed information
Service catalog with pricing, duration, and features
Photo galleries showcasing facilities, staff, and activities
Interactive map with transportation information (MRT, bus routes)
Staff credentials and qualifications display
MOH license verification and accreditation status
Operating hours and holiday schedules
Amenities and medical facilities information
Booking System

Pre-booking questionnaire to assess needs
Calendly-integrated scheduling for in-person visits
Real-time availability checking
Automated email confirmations
SMS reminders (3 days prior, 1 day prior) via Twilio
Booking management (reschedule, cancel)
Booking history in user dashboard
Social Proof & Trust Building

Testimonial submission from authenticated users
Moderation workflow for testimonial approval
Star rating system (1-5 stars)
Review filtering by service type and date
Response capability for service providers
User Management

User registration and authentication
Email verification
Profile management
Password reset functionality
User dashboard with booking history
PDPA-compliant consent management
Data export capability (right of access)
Account deletion (right to be forgotten)
Communication

Contact form with category selection
Newsletter subscription (Mailchimp integration)
Preference management for communications
PDPA-compliant double opt-in
Content & Information

Static informational pages (About, How It Works, FAQ)
Frequently Asked Questions by category
Blog or resource center (optional, content-driven)
Multilingual support (English, Mandarin Chinese)
Phase 2 Features (Weeks 13-16)
Virtual Tours

Video tours of care facilities
Chapter markers for different facility areas
Adaptive bitrate streaming for varying connection speeds
Accessibility features (captions, audio descriptions)
WebXR support for VR-capable devices
Tour completion tracking and analytics
Enhanced Multilingual Support

Malay language support
Tamil language support
Complete translation of all content types
Professional translation workflow
Advanced Search & Discovery

Faceted search with multiple filters
Advanced filtering (price range, location, services, amenities)
Search result sorting (relevance, distance, rating, price)
Saved searches and alerts
Comparison tool (compare up to 3 centers)
Enhanced Analytics

Video engagement tracking
Advanced user behavior analysis
Conversion funnel optimization
A/B testing framework
System Boundaries
In Scope:

Web platform (responsive, mobile-optimized)
Content management system for administrators
Booking management system
User authentication and authorization
Integration with Calendly, Mailchimp, Twilio
Analytics implementation (GA4, Hotjar)
PDPA and MOH compliance features
WCAG 2.1 AA accessibility implementation
Out of Scope (Future Considerations):

Native mobile applications (iOS, Android)
Payment processing and online transactions
Caregiver matching algorithms
Medical record integration
Telehealth consultations
Family portal for shared care management
Direct messaging between users and providers
Real-time availability booking (using scheduled availability only)
Integration with government eldercare databases
Key Constraints
Regulatory Constraints:

Full compliance with Singapore's Personal Data Protection Act (PDPA)
All personal data must reside within Singapore
Compliance with Ministry of Health (MOH) eldercare facility guidelines
Display of valid licenses and certifications required
Accessibility compliance with WCAG 2.1 AA and IMDA guidelines
Technical Constraints:

Page load time <3 seconds on 3G connection for standard pages
Lighthouse performance score >90
Lighthouse accessibility score >90
Support for modern browsers (Chrome, Safari, Firefox, Edge - last 2 versions)
Mobile-first design (mobile devices expected to account for 70% of traffic)
Business Constraints:

12-week timeline for MVP delivery
Budget limitations for third-party services
Team size and expertise considerations
Content creation and translation timelines
Operational Constraints:

24/7 availability requirement (99.5% uptime SLA)
Singapore-based hosting and data storage
Support for 4 official languages (phased approach)
Scalability to support 10,000 monthly active users initially
3. Architecture Principles
The ElderCare SG architecture is guided by seven core principles that inform every technical decision and implementation detail. These principles ensure the platform serves its users effectively while maintaining technical excellence.

1. User-Centric Design
Principle: Every architectural decision prioritizes the end-user experience, especially for elderly users and their families navigating emotional and complex care decisions.

Implementation:

Server-Side Rendering: Next.js Server Components ensure fast initial page loads, critical for users on slower connections or older devices
Progressive Enhancement: Core functionality works without JavaScript; enhancements add convenience but aren't required
Performance Budgets: Strict page weight limits (280KB for standard pages) ensure accessibility on slow connections
Mobile-First Architecture: API design, component structure, and caching strategies optimized for mobile usage patterns
Simplified Navigation: Clear information architecture with maximum 3 levels of hierarchy
Error Tolerance: Graceful degradation when services fail, with clear user communication and fallback options
Example: When Calendly API fails, the system automatically presents a contact form, notifies administrators, and queues the API call for retry—users always have a path forward.

2. Accessibility First
Principle: WCAG 2.1 AA compliance is a foundational requirement integrated from the start, not an afterthought. The platform must be usable by everyone, including those with visual, auditory, motor, or cognitive impairments.

Implementation:

Semantic HTML: Proper use of HTML5 elements (<nav>, <main>, <article>, <aside>) ensures screen reader comprehension
Radix UI Primitives: Base components include built-in keyboard navigation, focus management, and ARIA attributes
Keyboard Navigation: Full functionality accessible via keyboard (Tab, Enter, Escape, Arrow keys) with visible focus indicators
Color Contrast: Minimum 4.5:1 for normal text, 3:1 for large text, verified with automated tools
Text Resizing: Layout remains functional at 200% zoom without horizontal scrolling
Alternative Text: All images include descriptive alt text; decorative images use alt=""
Captions and Transcripts: Video content (Phase 2) includes captions, audio descriptions, and text transcripts
Reduced Motion: Respect prefers-reduced-motion media query; disable animations for users who prefer reduced motion
Automated Testing: axe-core integrated in CI/CD pipeline catches 40% of accessibility issues automatically
Manual Testing: NVDA (Windows) and VoiceOver (Mac, iOS) testing for all major features
Example: Booking form fields use explicit <label> elements with for attributes, include aria-describedby for help text, and provide inline validation with both visual and screen-reader-accessible error messages.

3. Security by Design
Principle: Security measures are integrated throughout the architecture, from database design to API endpoints to frontend forms, with particular attention to protecting sensitive elderly care data.

Implementation:

Authentication: Laravel Sanctum provides secure, token-based authentication with configurable expiration
Authorization: Role-based access control (RBAC) with granular permissions matrix
Data Encryption: TLS 1.3 for data in transit; AES-256 for sensitive data at rest; bcrypt (work factor 12) for password hashing
Input Validation: All user inputs validated on both client (immediate feedback) and server (security enforcement)
Output Encoding: React's built-in XSS protection plus Laravel Blade escaping for any server-rendered content
CSRF Protection: Laravel CSRF middleware on all state-changing requests; SameSite cookies
SQL Injection Prevention: Eloquent ORM and parameterized queries throughout
Security Headers: Content-Security-Policy, X-Frame-Options, X-Content-Type-Options, Strict-Transport-Security
Dependency Scanning: Dependabot, npm audit, composer audit in CI pipeline
Rate Limiting: API rate limits (60 req/min per IP, 1000/hour per authenticated user) prevent abuse
Audit Logging: All data access and modifications logged to audit_logs table with user, timestamp, IP address
Example: User profile update requires authenticated session, CSRF token validation, input sanitization, field-level authorization check (users can only edit their own profile), and logs the change to audit trail.

4. Performance Optimized
Principle: Fast loading times and smooth interactions are critical for user retention, especially on mobile devices and slower connections common in Singapore's older population segments.

Implementation:

Critical Rendering Path Optimization: Inline critical CSS (<14KB), defer non-critical JavaScript
Code Splitting: Route-based and component-based code splitting reduces initial bundle size
Image Optimization: WebP format with JPEG fallback, lazy loading, responsive images with srcset
Caching Strategy: Multi-layer caching (browser, CDN, Redis) with intelligent invalidation
Database Optimization: Indexed queries, read replicas for read-heavy operations, connection pooling
API Response Times: Target <200ms for 95th percentile; optimized with database indexing and Redis caching
CDN Delivery: Cloudflare CDN serves static assets from Singapore edge locations
Bundle Size Monitoring: Performance budgets enforced in CI/CD; builds fail if exceeded
Lighthouse CI: Automated performance testing on every deployment
Real User Monitoring: New Relic tracks actual user performance metrics
Example: Homepage loads in <2 seconds on 3G by serving server-rendered HTML with inlined critical CSS, lazy-loading below-fold images, and caching service listings in Redis for 5 minutes.

5. Compliance Built-In
Principle: Singapore's Personal Data Protection Act (PDPA) and Ministry of Health (MOH) regulations are not bolted on later—they're architected into data models, user flows, and system processes from day one.

Implementation:

Data Residency: All production databases and file storage in AWS Singapore region
Consent Management: Explicit, granular consent capture in consents table with versioning and audit trail
Data Subject Rights: Built-in features for data access (export), rectification (profile editing), and erasure (account deletion)
Data Minimization: Only collect data necessary for service delivery; regular audits of data collection
Retention Policies: Automated data retention enforcement (2-year inactivity triggers deletion notice)
Breach Response: Automated monitoring, incident response procedures, notification templates
License Verification: MOH license number validation, expiry tracking, display requirements
Audit Trail: Comprehensive logging of all personal data access and modifications (7-year retention)
Privacy by Default: Minimal data collection by default; marketing communications opt-in only
Example: When user requests account deletion, system initiates 30-day soft delete period, sends confirmation email, removes data from external systems (Mailchimp, Calendly), anonymizes audit logs after retention period, and provides deletion confirmation.

6. Scalable and Maintainable
Principle: The architecture supports growth from 1,000 to 100,000 monthly active users without requiring fundamental redesign, while maintaining code quality that enables rapid feature development.

Implementation:

Service-Oriented Monolith: Clear service boundaries within monolith enable future extraction to microservices if needed
Stateless Application Layer: Session data in Redis enables horizontal scaling across multiple application servers
Database Scaling: Read replicas for read-heavy operations; clear migration path to sharding if needed
Queue-Based Processing: Background jobs (emails, SMS, Mailchimp sync) processed asynchronously via Redis queues
Containerization: Docker containers enable consistent environments and easy scaling via AWS ECS
Auto-Scaling: AWS ECS auto-scaling based on CPU/memory thresholds
Monitoring: Comprehensive observability (New Relic, Sentry, CloudWatch) enables proactive scaling decisions
Code Quality: TypeScript, PHPStan (level 8), ESLint, Prettier enforce consistency
Testing: 90% code coverage requirement ensures confidence in refactoring
Documentation: Architecture Decision Records (ADRs) capture rationale for major decisions
Example: When traffic spikes during marketing campaign, AWS ECS auto-scaling launches additional container instances; load balancer distributes traffic; read replicas handle increased database queries; Redis cache reduces database load; users experience consistent performance.

7. Cultural Sensitivity
Principle: Singapore's multicultural context (Chinese, Malay, Indian, other ethnicities) requires thoughtful design that respects cultural norms, language preferences, and elder care traditions.

Implementation:

Multilingual Architecture: First-class i18n support with next-intl, database-backed content translations, professional translation workflow
Language Parity: All core features available in all supported languages (English, Mandarin, then Malay, Tamil)
Respectful Imagery: Photos reflect Singapore's demographic diversity, age-appropriate representation
Honorifics: Support for culturally appropriate titles (Mr., Mrs., Mdm.) in all communications
Calendar Awareness: System recognizes major cultural holidays (Chinese New Year, Hari Raya, Deepavali, Christmas) for scheduling and communications
Tone and Messaging: Content reviewed for cultural sensitivity; avoids idioms that don't translate
Color Psychology: Color palette considers cultural color associations (avoid inauspicious combinations)
Family Decision Patterns: Recognizes collective decision-making in Asian family structures (e.g., sharing capabilities, multiple contact persons)
Example: Booking confirmation email addresses recipient with appropriate honorific, translates all content to user's preferred language, acknowledges family member if listed as secondary contact, and respects public holidays when scheduling reminders.

4. System Architecture
Architecture Pattern: Service-Oriented Monolith
ElderCare SG employs a service-oriented monolithic architecture—a single Laravel application organized into well-defined service layers with clear boundaries. This architectural choice balances simplicity, development velocity, and operational manageability while maintaining the modularity needed for future scaling.

Why Not Microservices?

While microservices offer benefits at scale, they introduce significant complexity:

Multiple deployment pipelines and monitoring systems
Distributed transaction management
Network latency between services
Operational overhead (multiple databases, service discovery, API gateways)
Team coordination complexity
For ElderCare SG's initial scale (10,000 MAU) and 12-week timeline, a well-structured monolith provides:

Faster development (single codebase, shared utilities)
Simpler deployment (single artifact)
Easier debugging (single stack trace)
Lower operational overhead (one database, one cache, one deployment)
Clear upgrade path (service boundaries enable future extraction)
Future-Proofing: The service layer architecture uses dependency injection and interface contracts, enabling individual services to be extracted into independent microservices if traffic patterns or team structure justify the added complexity.

High-Level Architecture
mermaid

graph TB
    subgraph "Client Layer"
        Browser[Web Browser<br/>Chrome, Safari, Firefox, Edge]
        Mobile[Mobile Browser<br/>iOS Safari, Chrome Mobile]
    end
    
    subgraph "CDN Layer"
        CDN[Cloudflare CDN<br/>Singapore Edge Locations]
    end
    
    subgraph "Presentation Layer"
        NextJS[Next.js 14 Frontend<br/>Server & Client Components]
    end
    
    subgraph "API Gateway"
        Laravel[Laravel 12 API Gateway<br/>Route Handler & Middleware]
    end
    
    subgraph "Service Layer"
        AuthSvc[Authentication Service<br/>User sessions, tokens]
        BookingSvc[Booking Service<br/>Calendly integration]
        ContentSvc[Content Service<br/>Centers, services, FAQs]
        NotifySvc[Notification Service<br/>Email, SMS queuing]
        AnalyticsSvc[Analytics Service<br/>Event tracking]
        SearchSvc[Search Service<br/>MeiliSearch integration]
    end
    
    subgraph "Data Layer"
        MySQL[(MySQL 8.0<br/>Primary + Read Replica)]
        Redis[(Redis 7<br/>Cache + Queues + Sessions)]
        Meili[(MeiliSearch<br/>Full-text search)]
        S3[(AWS S3<br/>Singapore Region<br/>Media storage)]
    end
    
    subgraph "External Services"
        Calendly[Calendly API<br/>Scheduling]
        Mailchimp[Mailchimp API<br/>Newsletter]
        Twilio[Twilio SMS<br/>Singapore numbers]
        Stream[Cloudflare Stream<br/>Video hosting]
    end
    
    subgraph "Infrastructure"
        ECS[AWS ECS<br/>Container Orchestration]
        ALB[Application Load Balancer<br/>Traffic Distribution]
    end
    
    Browser --> CDN
    Mobile --> CDN
    CDN --> NextJS
    NextJS --> Laravel
    
    Laravel --> AuthSvc
    Laravel --> BookingSvc
    Laravel --> ContentSvc
    Laravel --> NotifySvc
    Laravel --> AnalyticsSvc
    Laravel --> SearchSvc
    
    AuthSvc --> MySQL
    AuthSvc --> Redis
    BookingSvc --> MySQL
    BookingSvc --> Redis
    BookingSvc --> Calendly
    ContentSvc --> MySQL
    ContentSvc --> Redis
    ContentSvc --> Meili
    NotifySvc --> Redis
    NotifySvc --> Mailchimp
    NotifySvc --> Twilio
    AnalyticsSvc --> MySQL
    SearchSvc --> Meili
    
    ContentSvc --> S3
    
    NextJS -.->|Container| ECS
    Laravel -.->|Container| ECS
    ECS --> ALB
Component Interaction Patterns
Request Flow: User Views Service Listing

Client Request: Browser requests /en/services from Next.js frontend
Server Component Rendering: Next.js Server Component fetches service data from Laravel API
API Request: Laravel route handler invokes ContentService::getServices()
Cache Check: Service checks Redis cache for key services:list:en:v1
Cache Hit: Return cached data immediately (5-minute TTL)
Cache Miss: Service queries ServiceRepository::getPublishedServices('en')
Database Query: Repository queries MySQL read replica with joins for center and translations
Transform Data: Service transforms DB models to API resource format
Cache Store: Service stores result in Redis
API Response: Laravel returns JSON response
SSR Rendering: Next.js renders HTML with service data
Client Delivery: HTML sent to browser; client-side hydration adds interactivity
CDN Caching: Cloudflare caches static assets (images, CSS, JS)
Request Flow: User Submits Booking

Form Submission: User submits booking form (client-side validation passes)
API Request: Next.js client sends POST to /api/v1/bookings
Authentication: Laravel Sanctum middleware verifies session token
Authorization: Middleware confirms user is authenticated
Validation: Laravel Form Request validates input (date, time, questionnaire)
Service Invocation: Controller calls BookingService::createBooking($data)
Database Transaction: Service begins transaction
Create Booking: Insert record to bookings table with status 'pending'
Calendly API: Service calls Calendly API to create scheduled event
Update Booking: Store calendly_event_id in booking record
Commit Transaction: Transaction commits
Queue Jobs: Dispatch SendBookingConfirmationEmail and SendBookingConfirmationSMS jobs to Redis queue
API Response: Return booking confirmation to client
Job Processing: Background worker processes queue jobs asynchronously
Email Sent: Mailgun sends confirmation email
SMS Sent: Twilio sends confirmation SMS
Analytics Event: Track booking_completed event in Google Analytics
API-First Design Philosophy
All business logic is exposed through RESTful APIs, enabling:

Frontend Flexibility: Next.js frontend consumes same APIs as potential future mobile apps
Third-Party Integration: External partners can integrate via documented API endpoints
Testing Simplicity: API endpoints testable independent of frontend
Version Management: API versioning (/api/v1/, /api/v2/) enables backward compatibility
API Conventions:

RESTful resource naming: /api/v1/centers, /api/v1/bookings
Standard HTTP methods: GET (read), POST (create), PATCH (update), DELETE (delete)
Consistent response format:
JSON

{
  "data": { ... },
  "meta": { "page": 1, "per_page": 20, "total": 150 },
  "links": { "self": "...", "next": "...", "prev": "..." }
}
Error responses:
JSON

{
  "error": {
    "message": "Validation failed",
    "code": "VALIDATION_ERROR",
    "details": {
      "email": ["The email field is required."]
    }
  }
}
Event-Driven Background Processing
Queue Architecture: Redis-backed queues handle asynchronous processing, preventing user-facing delays and improving resilience.

Job Types:

Email Jobs: SendBookingConfirmationEmail, SendPasswordResetEmail, SendNewsletterEmail
SMS Jobs: SendBookingReminderSMS, SendBookingCancellationSMS
Integration Jobs: SyncMailchimpSubscriber, UpdateCalendlyEvent
Maintenance Jobs: DeleteInactiveUsers, CleanupExpiredSessions
Retry Strategy:

Max Attempts: 3
Backoff: Exponential (1 min, 5 min, 15 min)
Failure Handling: After max attempts, job moved to failed_jobs table; admin notified via Sentry
Job Priority:

High: Booking confirmations, password resets
Medium: Welcome emails, booking reminders
Low: Newsletter subscriptions, analytics aggregation
Future Migration Path to Microservices
If future scale demands microservices (e.g., 100,000+ MAU, multiple development teams), the service layer architecture enables extraction:

Candidate Services for Extraction:

Notification Service: High volume, independent scaling needs, clear boundaries
Search Service: Resource-intensive, benefits from dedicated infrastructure
Booking Service: Complex business logic, external integrations, potential bottleneck
Migration Strategy:

Extract service to standalone Laravel application
Deploy as separate container in ECS
Create dedicated database or share via read replica
Update API gateway to route requests to new service
Implement circuit breaker pattern for resilience
Monitor performance and gradually shift traffic
Not Planned for MVP: Microservices extraction deferred until data justifies complexity.

5. Frontend Architecture
Next.js 14 Architecture Overview
The frontend leverages Next.js 14's App Router with React Server Components, providing optimal performance through server-side rendering while maintaining rich client interactivity where needed.

Key Architectural Choices:

App Router: File-system-based routing with support for layouts, loading states, error boundaries
React Server Components: Default server rendering reduces client JavaScript bundle size
TypeScript: Full type safety across components, APIs, and state management
Tailwind CSS: Utility-first styling with custom design system tokens
Radix UI: Accessible component primitives for complex interactions
Server vs. Client Component Strategy
Server Components (Default):

Service listings and detail pages
Testimonial displays
FAQ sections
Static content pages
Benefits: Zero client JavaScript, fast initial render, SEO-friendly
Client Components ('use client' directive):

Interactive forms (booking, contact, newsletter)
Language switcher
Modal dialogs
Testimonial carousel
Mobile navigation menu
Benefits: Rich interactivity, immediate feedback, local state management
Hybrid Example: Service Detail Page

TypeScript

// app/[locale]/services/[slug]/page.tsx (Server Component)
import { getServiceBySlug } from '@/lib/api/services';
import BookingForm from '@/components/BookingForm'; // Client Component

export default async function ServiceDetailPage({ 
  params: { locale, slug } 
}: Props) {
  const service = await getServiceBySlug(slug, locale); // Fetched on server
  
  return (
    <div>
      <ServiceHeader service={service} /> {/* Server Component */}
      <ServiceDescription service={service} /> {/* Server Component */}
      <BookingForm serviceId={service.id} /> {/* Client Component */}
    </div>
  );
}
Component Architecture (Atomic Design)
mermaid

graph TB
    subgraph "Pages Layer"
        HomePage[Home Page]
        ServicesPage[Services Listing Page]
        ServiceDetailPage[Service Detail Page]
        BookingPage[Booking Confirmation Page]
        AboutPage[About Page]
    end
    
    subgraph "Template Layer"
        MainLayout[Main Layout<br/>Header + Footer]
        DashboardLayout[Dashboard Layout<br/>Sidebar + Content]
    end
    
    subgraph "Organism Layer"
        Header[Header<br/>Logo + Nav + Language]
        Footer[Footer<br/>Links + Newsletter]
        ServiceCard[Service Card<br/>Image + Info + CTA]
        BookingForm[Booking Form<br/>Multi-step form]
        TestimonialCarousel[Testimonial Carousel<br/>Slides + Controls]
    end
    
    subgraph "Molecule Layer"
        NavigationMenu[Navigation Menu<br/>Menu items + Dropdown]
        FormField[Form Field<br/>Label + Input + Error]
        Card[Card<br/>Container + Padding]
        Modal[Modal<br/>Overlay + Content]
    end
    
    subgraph "Atom Layer"
        Button[Button<br/>Variants + States]
        Input[Input<br/>Text, Email, Tel]
        Label[Label<br/>Typography]
        Icon[Icon<br/>SVG components]
        Link[Link<br/>Next.js Link wrapper]
    end
    
    HomePage --> MainLayout
    ServicesPage --> MainLayout
    ServiceDetailPage --> MainLayout
    BookingPage --> DashboardLayout
    
    MainLayout --> Header
    MainLayout --> Footer
    
    Header --> NavigationMenu
    ServiceCard --> Card
    ServiceCard --> Button
    BookingForm --> FormField
    TestimonialCarousel --> Card
    
    NavigationMenu --> Link
    FormField --> Label
    FormField --> Input
    Card --> Button
    Modal --> Button
Component Organization:

text

components/
├── atoms/               # Smallest reusable components
│   ├── Button.tsx
│   ├── Input.tsx
│   ├── Label.tsx
│   ├── Icon.tsx
│   ├── Link.tsx
│   └── Typography.tsx
├── molecules/           # Simple component groups
│   ├── FormField.tsx
│   ├── Card.tsx
│   ├── Modal.tsx
│   ├── Alert.tsx
│   └── NavigationMenu.tsx
├── organisms/           # Complex component compositions
│   ├── Header.tsx
│   ├── Footer.tsx
│   ├── ServiceCard.tsx
│   ├── BookingForm.tsx
│   ├── TestimonialCarousel.tsx
│   └── ContactForm.tsx
├── templates/           # Page layouts
│   ├── MainLayout.tsx
│   ├── DashboardLayout.tsx
│   └── AuthLayout.tsx
└── features/            # Feature-specific components
    ├── booking/
    ├── testimonials/
    └── search/
State Management Strategy
Zustand (Global Client State):

TypeScript

// stores/useAppStore.ts
import { create } from 'zustand';
import { persist } from 'zustand/middleware';

interface AppState {
  // Language preference
  locale: string;
  setLocale: (locale: string) => void;
  
  // UI state
  isMobileMenuOpen: boolean;
  toggleMobileMenu: () => void;
  
  // User session (after hydration from server)
  user: User | null;
  setUser: (user: User | null) => void;
}

export const useAppStore = create<AppState>()(
  persist(
    (set) => ({
      locale: 'en',
      setLocale: (locale) => set({ locale }),
      isMobileMenuOpen: false,
      toggleMobileMenu: () => set((state) => ({ 
        isMobileMenuOpen: !state.isMobileMenuOpen 
      })),
      user: null,
      setUser: (user) => set({ user }),
    }),
    {
      name: 'app-storage',
      partialize: (state) => ({ locale: state.locale }), // Only persist locale
    }
  )
);
React Query (Server State):

TypeScript

// hooks/useServices.ts
import { useQuery } from '@tanstack/react-query';
import { getServices } from '@/lib/api/services';

export function useServices(locale: string, filters?: ServiceFilters) {
  return useQuery({
    queryKey: ['services', locale, filters],
    queryFn: () => getServices(locale, filters),
    staleTime: 5 * 60 * 1000, // 5 minutes
    cacheTime: 10 * 60 * 1000, // 10 minutes
  });
}
Local Component State:

TypeScript

// For ephemeral UI state not needed elsewhere
const [isModalOpen, setIsModalOpen] = useState(false);
const [currentStep, setCurrentStep] = useState(1);
State Management Decision Matrix:

State Type	Tool	Example
Server data (cacheable)	React Query	Services, centers, testimonials
Global app state	Zustand	Language preference, user session
URL state	Next.js router	Page number, search filters
Form state	React Hook Form	Form inputs, validation
Ephemeral UI state	useState	Modal open/closed, accordion expanded
Routing and Navigation
App Router Structure:

text

app/
├── [locale]/                    # Internationalized routes
│   ├── page.tsx                # Home page (/en, /zh)
│   ├── services/
│   │   ├── page.tsx            # Services listing (/en/services)
│   │   └── [slug]/
│   │       └── page.tsx        # Service detail (/en/services/daily-care)
│   ├── centers/
│   │   ├── page.tsx            # Centers listing
│   │   └── [slug]/
│   │       └── page.tsx        # Center detail
│   ├── about/
│   │   └── page.tsx            # About page
│   ├── contact/
│   │   └── page.tsx            # Contact page
│   ├── faq/
│   │   └── page.tsx            # FAQ page
│   └── dashboard/
│       ├── page.tsx            # User dashboard
│       ├── bookings/
│       │   └── page.tsx        # Booking history
│       └── profile/
│           └── page.tsx        # Profile settings
├── api/                        # API routes (proxy to Laravel)
│   └── [...proxy]/
│       └── route.ts            # Proxy all /api/* to Laravel
└── layout.tsx                  # Root layout
Dynamic Route Parameters:

[locale]: Language code (en, zh, ms, ta)
[slug]: URL-friendly identifier for resources
Middleware for Locale Detection:

TypeScript

// middleware.ts
import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

const locales = ['en', 'zh', 'ms', 'ta'];
const defaultLocale = 'en';

export function middleware(request: NextRequest) {
  const pathname = request.nextUrl.pathname;
  
  // Check if pathname already has locale
  const pathnameHasLocale = locales.some(
    (locale) => pathname.startsWith(`/${locale}/`) || pathname === `/${locale}`
  );
  
  if (pathnameHasLocale) return;
  
  // Redirect to locale-prefixed path
  const locale = defaultLocale; // Could detect from Accept-Language header
  request.nextUrl.pathname = `/${locale}${pathname}`;
  return NextResponse.redirect(request.nextUrl);
}

export const config = {
  matcher: ['/((?!api|_next/static|_next/image|favicon.ico).*)'],
};
Asset Optimization Strategy
Image Optimization:

TypeScript

// Using Next.js Image component
import Image from 'next/image';

<Image
  src="/images/center-photo.jpg"
  alt="Sunshine Care Center main hall"
  width={800}
  height={600}
  quality={85}
  loading="lazy"
  placeholder="blur"
  blurDataURL="data:image/jpeg;base64,..." // Low-quality placeholder
/>
Optimization Features:

Automatic WebP/AVIF conversion (with JPEG fallback)
Responsive images via srcset
Lazy loading below the fold
Blur-up placeholder for smooth loading
Image CDN delivery via Cloudflare
Font Optimization:

TypeScript

// app/layout.tsx
import { Inter } from 'next/font/google';

const inter = Inter({
  subsets: ['latin'],
  display: 'swap', // Prevents FOIT (Flash of Invisible Text)
  variable: '--font-inter',
});
JavaScript Bundle Optimization:

Route-based code splitting (automatic with App Router)
Dynamic imports for heavy components:
TypeScript

const BookingModal = dynamic(() => import('@/components/BookingModal'), {
  loading: () => <LoadingSpinner />,
  ssr: false, // Don't render on server if not needed
});
Progressive Enhancement
Core HTML Works Without JavaScript:

TypeScript

// Contact form works with server action even without JS
export default function ContactForm() {
  async function submitForm(formData: FormData) {
    'use server';
    const data = {
      name: formData.get('name'),
      email: formData.get('email'),
      message: formData.get('message'),
    };
    await sendContactEmail(data);
    redirect('/thank-you');
  }
  
  return (
    <form action={submitForm}>
      <input name="name" required />
      <input name="email" type="email" required />
      <textarea name="message" required />
      <button type="submit">Send</button>
    </form>
  );
}
JavaScript Enhances Experience:

Client-side validation for immediate feedback
Loading states during submission
Inline error messages
Success message without page reload
6. Backend Architecture
Service-Oriented Laravel Application
The Laravel backend implements a layered architecture separating concerns across Controllers (HTTP), Services (business logic), Repositories (data access), and Models (domain entities).

Directory Structure:

text

app/
├── Console/
│   └── Commands/              # CLI commands for maintenance tasks
├── Events/                    # Domain events
│   ├── BookingCreated.php
│   ├── UserRegistered.php
│   └── TestimonialSubmitted.php
├── Exceptions/
│   └── Handler.php            # Global exception handling
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── V1/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── BookingController.php
│   │   │   │   ├── CenterController.php
│   │   │   │   ├── ServiceController.php
│   │   │   │   └── TestimonialController.php
│   │   ├── Middleware/
│   │   │   ├── EnsureUserIsAuthenticated.php
│   │   │   ├── LocaleMiddleware.php
│   │   │   └── ThrottleRequests.php
│   │   ├── Requests/         # Form validation requests
│   │   │   ├── CreateBookingRequest.php
│   │   │   ├── UpdateProfileRequest.php
│   │   │   └── SubmitTestimonialRequest.php
│   │   └── Resources/        # API response transformers
│   │       ├── CenterResource.php
│   │       ├── ServiceResource.php
│   │       └── BookingResource.php
├── Jobs/                     # Queue jobs
│   ├── SendBookingConfirmationEmail.php
│   ├── SendBookingReminderSMS.php
│   └── SyncMailchimpSubscriber.php
├── Listeners/                # Event listeners
│   ├── SendWelcomeEmail.php
│   └── LogBookingCreated.php
├── Models/                   # Eloquent models
│   ├── User.php
│   ├── Center.php
│   ├── Service.php
│   ├── Booking.php
│   ├── Testimonial.php
│   └── Consent.php
├── Policies/                 # Authorization policies
│   ├── BookingPolicy.php
│   └── TestimonialPolicy.php
├── Providers/
│   ├── AppServiceProvider.php
│   ├── AuthServiceProvider.php
│   └── EventServiceProvider.php
├── Repositories/             # Data access layer
│   ├── BookingRepository.php
│   ├── CenterRepository.php
│   ├── ServiceRepository.php
│   └── UserRepository.php
└── Services/                 # Business logic layer
    ├── AuthService.php
    ├── BookingService.php
    ├── ContentService.php
    ├── NotificationService.php
    ├── AnalyticsService.php
    └── SearchService.php
Service Layer Pattern
Purpose: Encapsulate business logic, keeping controllers thin and promoting reusability.

Implementation Example:

PHP

<?php

namespace App\Services;

use App\Repositories\BookingRepository;
use App\Services\CalendlyService;
use App\Services\NotificationService;
use App\Events\BookingCreated;
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
     * @throws BookingException
     */
    public function createBooking(array $data): Booking
    {
        DB::beginTransaction();
        
        try {
            // 1. Create pending booking in database
            $booking = $this->bookingRepository->create([
                'user_id' => $data['user_id'],
                'center_id' => $data['center_id'],
                'service_id' => $data['service_id'],
                'booking_date' => $data['booking_date'],
                'booking_time' => $data['booking_time'],
                'questionnaire_responses' => $data['questionnaire_responses'],
                'status' => 'pending',
            ]);
            
            // 2. Create Calendly event
            $calendlyEvent = $this->calendlyService->createScheduledEvent([
                'event_type' => $data['event_type_uri'],
                'invitee_email' => $data['user_email'],
                'invitee_name' => $data['user_name'],
                'start_time' => $data['booking_date'] . ' ' . $data['booking_time'],
                'questions_and_answers' => $this->formatQuestionnaire(
                    $data['questionnaire_responses']
                ),
            ]);
            
            // 3. Update booking with Calendly event ID
            $booking->update([
                'calendly_event_id' => $calendlyEvent['id'],
                'calendly_event_uri' => $calendlyEvent['uri'],
                'status' => 'confirmed',
            ]);
            
            DB::commit();
            
            // 4. Dispatch event (triggers email and SMS jobs)
            event(new BookingCreated($booking));
            
            Log::info('Booking created successfully', [
                'booking_id' => $booking->id,
                'calendly_event_id' => $calendlyEvent['id'],
            ]);
            
            return $booking;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Booking creation failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            
            throw new BookingException(
                'Failed to create booking. Please try again or contact support.',
                previous: $e
            );
        }
    }
    
    /**
     * Cancel a booking and notify Calendly
     */
    public function cancelBooking(int $bookingId, string $reason = null): bool
    {
        $booking = $this->bookingRepository->findOrFail($bookingId);
        
        // Cancel in Calendly if event exists
        if ($booking->calendly_event_uri) {
            $this->calendlyService->cancelScheduledEvent($booking->calendly_event_uri);
        }
        
        // Update booking status
        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);
        
        // Send cancellation notification
        $this->notificationService->sendBookingCancellation($booking);
        
        return true;
    }
    
    private function formatQuestionnaire(array $responses): array
    {
        return collect($responses)->map(function ($answer, $question) {
            return [
                'question' => $question,
                'answer' => $answer,
            ];
        })->values()->toArray();
    }
}
Service Pattern Benefits:

Testability: Services injected via constructor; easily mocked in tests
Reusability: Services called from controllers, console commands, queue jobs
Transaction Management: Database transactions wrapped around complex operations
Error Handling: Centralized business exception handling
Logging: Comprehensive logging for debugging and audit
Repository Pattern
Purpose: Abstract data access logic, making it easier to swap data sources or add caching layers.

Implementation Example:

PHP

<?php

namespace App\Repositories;

use App\Models\Center;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CenterRepository
{
    /**
     * Get all published centers with translations
     */
    public function getPublishedCenters(string $locale, array $filters = []): Collection
    {
        $cacheKey = $this->getCacheKey('published', $locale, $filters);
        
        return Cache::remember($cacheKey, 300, function () use ($locale, $filters) {
            $query = Center::query()
                ->with(['services', 'media', 'testimonials'])
                ->where('status', 'published')
                ->orderBy('name');
            
            // Apply filters
            if (isset($filters['city'])) {
                $query->where('city', $filters['city']);
            }
            
            if (isset($filters['languages'])) {
                $query->whereJsonContains('languages_supported', $filters['languages']);
            }
            
            return $query->get()->map(function ($center) use ($locale) {
                return $this->translateCenter($center, $locale);
            });
        });
    }
    
    /**
     * Find center by slug with translations
     */
    public function findBySlug(string $slug, string $locale): ?Center
    {
        $center = Center::where('slug', $slug)
            ->with(['services', 'media', 'testimonials'])
            ->firstOrFail();
        
        return $this->translateCenter($center, $locale);
    }
    
    /**
     * Translate center fields to specified locale
     */
    private function translateCenter(Center $center, string $locale): Center
    {
        $fields = ['name', 'description'];
        
        foreach ($fields as $field) {
            $translation = $center->translations()
                ->where('locale', $locale)
                ->where('field', $field)
                ->first();
            
            if ($translation) {
                $center->setAttribute($field, $translation->value);
            }
        }
        
        $center->setAttribute('locale', $locale);
        
        return $center;
    }
    
    /**
     * Generate cache key
     */
    private function getCacheKey(string $type, string $locale, array $filters = []): string
    {
        $filterHash = md5(json_encode($filters));
        return "centers:{$type}:{$locale}:{$filterHash}";
    }
    
    /**
     * Clear center caches
     */
    public function clearCache(): void
    {
        Cache::tags(['centers'])->flush();
    }
}
API Design Standards
RESTful Routing:

PHP

// routes/api.php
Route::prefix('v1')->group(function () {
    // Public routes
    Route::get('/centers', [CenterController::class, 'index']);
    Route::get('/centers/{slug}', [CenterController::class, 'show']);
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{slug}', [ServiceController::class, 'show']);
    Route::get('/testimonials', [TestimonialController::class, 'index']);
    
    // Authentication
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/user', [UserController::class, 'show']);
        Route::patch('/user/profile', [UserController::class, 'updateProfile']);
        
        // Bookings
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/bookings/{id}', [BookingController::class, 'show']);
        Route::patch('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
        
        // Testimonials
        Route::post('/testimonials', [TestimonialController::class, 'store']);
        
        // Data export (PDPA compliance)
        Route::post('/user/export-data', [UserController::class, 'exportData']);
        Route::delete('/user/delete-account', [UserController::class, 'deleteAccount']);
    });
});
API Response Format (Laravel API Resources):

PHP

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CenterResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'address' => [
                'street' => $this->address,
                'city' => $this->city,
                'postal_code' => $this->postal_code,
            ],
            'contact' => [
                'phone' => $this->phone,
                'email' => $this->email,
            ],
            'compliance' => [
                'moh_license_number' => $this->moh_license_number,
                'accreditation_status' => $this->accreditation_status,
            ],
            'facilities' => $this->medical_facilities,
            'transport' => $this->transport_info,
            'amenities' => $this->amenities,
            'languages_supported' => $this->languages_supported,
            'operating_hours' => $this->operating_hours,
            'services' => ServiceResource::collection($this->whenLoaded('services')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'testimonials' => TestimonialResource::collection(
                $this->whenLoaded('testimonials')
            ),
            'average_rating' => $this->average_rating,
            'total_reviews' => $this->testimonials_count,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
Queue Architecture
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
use Illuminate\Support\Facades\Log;

class SendBookingReminderSMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min
    
    public function __construct(
        public Booking $booking
    ) {}
    
    public function handle(TwilioService $twilio): void
    {
        $message = "Reminder: Your visit to {$this->booking->center->name} " .
                   "is tomorrow at {$this->booking->booking_time}. " .
                   "Looking forward to seeing you!";
        
        $twilio->sendSMS(
            to: $this->booking->user->phone,
            message: $message
        );
        
        Log::info('Booking reminder SMS sent', [
            'booking_id' => $this->booking->id,
            'user_id' => $this->booking->user_id,
        ]);
    }
    
    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to send booking reminder SMS', [
            'booking_id' => $this->booking->id,
            'error' => $exception->getMessage(),
        ]);
        
        // Notify admin via Sentry
        app('sentry')->captureException($exception);
    }
}
Queue Configuration:

PHP

// config/queue.php
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
Error Handling Architecture
Global Exception Handler:

PHP

<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
    }
    
    public function render($request, Throwable $e)
    {
        if ($request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }
        
        return parent::render($request, $e);
    }
    
    private function handleApiException($request, Throwable $e)
    {
        $status = 500;
        $code = 'INTERNAL_SERVER_ERROR';
        $message = 'An unexpected error occurred';
        $details = [];
        
        if ($e instanceof ValidationException) {
            $status = 422;
            $code = 'VALIDATION_ERROR';
            $message = 'The given data was invalid';
            $details = $e->errors();
        } elseif ($e instanceof AuthenticationException) {
            $status = 401;
            $code = 'UNAUTHENTICATED';
            $message = 'Authentication required';
        } elseif ($e instanceof NotFoundHttpException) {
            $status = 404;
            $code = 'NOT_FOUND';
            $message = 'Resource not found';
        }
        
        $response = [
            'error' => [
                'message' => $message,
                'code' => $code,
            ],
        ];
        
        if (!empty($details)) {
            $response['error']['details'] = $details;
        }
        
        if (config('app.debug')) {
            $response['error']['exception'] = get_class($e);
            $response['error']['trace'] = $e->getTraceAsString();
        }
        
        return response()->json($response, $status);
    }
}
7. Data Architecture
Complete Database Schema
The database schema supports all core features while ensuring PDPA compliance, multilingual content, and comprehensive audit trails.

mermaid

erDiagram
    USERS {
        bigint id PK
        string name
        string email UK
        string phone UK
        string password
        enum role
        timestamp email_verified_at
        timestamp created_at
        timestamp updated_at
        timestamp deleted_at
    }
    
    PROFILES {
        bigint id PK
        bigint user_id FK
        string avatar
        text bio
        date birth_date
        string address
        string city
        string postal_code
        string country
        timestamp created_at
        timestamp updated_at
    }
    
    CENTERS {
        bigint id PK
        string name
        string slug UK
        text description
        string address
        string city
        string postal_code
        string phone
        string email
        string moh_license_number UK
        date license_expiry_date
        enum accreditation_status
        int staff_count
        json medical_facilities
        json transport_info
        json amenities
        json languages_supported
        json operating_hours
        enum status
        timestamp created_at
        timestamp updated_at
    }
    
    SERVICES {
        bigint id PK
        bigint center_id FK
        string name
        string slug
        text description
        decimal price
        string duration
        json features
        enum status
        int order
        timestamp created_at
        timestamp updated_at
    }
    
    BOOKINGS {
        bigint id PK
        bigint user_id FK
        bigint center_id FK
        bigint service_id FK
        string calendly_event_id UK
        string calendly_event_uri
        date booking_date
        time booking_time
        json questionnaire_responses
        enum status
        text notes
        string cancellation_reason
        timestamp cancelled_at
        timestamp created_at
        timestamp updated_at
    }
    
    TESTIMONIALS {
        bigint id PK
        bigint user_id FK
        bigint center_id FK
        string title
        text content
        int rating
        enum status
        string rejection_reason
        timestamp approved_at
        timestamp created_at
        timestamp updated_at
    }
    
    CONSENTS {
        bigint id PK
        bigint user_id FK
        enum consent_type
        boolean consent_given
        text consent_text
        string consent_version
        string ip_address
        string user_agent
        timestamp created_at
        timestamp updated_at
    }
    
    AUDIT_LOGS {
        bigint id PK
        bigint user_id FK
        string action
        string auditable_type
        bigint auditable_id
        json old_values
        json new_values
        string ip_address
        string user_agent
        timestamp created_at
    }
    
    CONTENT_TRANSLATIONS {
        bigint id PK
        string translatable_type
        bigint translatable_id
        string locale
        string field
        text value
        timestamp created_at
        timestamp updated_at
    }
    
    MEDIA {
        bigint id PK
        string mediable_type
        bigint mediable_id
        enum type
        string url
        string thumbnail_url
        int duration
        bigint size
        string mime_type
        string caption
        string alt_text
        int order
        timestamp created_at
        timestamp updated_at
    }
    
    SUBSCRIPTIONS {
        bigint id PK
        string email UK
        string mailchimp_subscriber_id
        enum status
        json preferences
        timestamp subscribed_at
        timestamp unsubscribed_at
        timestamp created_at
        timestamp updated_at
    }
    
    FAQS {
        bigint id PK
        enum category
        text question
        text answer
        int order
        enum status
        timestamp created_at
        timestamp updated_at
    }
    
    USERS ||--|| PROFILES : has
    USERS ||--o{ BOOKINGS : makes
    USERS ||--o{ TESTIMONIALS : writes
    USERS ||--o{ CONSENTS : gives
    CENTERS ||--o{ SERVICES : offers
    CENTERS ||--o{ BOOKINGS : receives
    CENTERS ||--o{ TESTIMONIALS : has
    SERVICES ||--o{ BOOKINGS : booked_for
Detailed Table Schemas
Users Table:

SQL

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin', 'content_manager', 'moderator') DEFAULT 'user',
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Centers Table:

SQL

CREATE TABLE centers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    address VARCHAR(500) NOT NULL,
    city VARCHAR(100) NOT NULL,
    postal_code VARCHAR(10) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    moh_license_number VARCHAR(50) NOT NULL UNIQUE,
    license_expiry_date DATE,
    accreditation_status ENUM('pending', 'accredited', 'not_accredited') DEFAULT 'pending',
    staff_count INT UNSIGNED DEFAULT 0,
    medical_facilities JSON COMMENT 'Array of medical facilities available',
    transport_info JSON COMMENT 'MRT stations, bus routes, parking info',
    amenities JSON COMMENT 'Array of amenities',
    languages_supported JSON COMMENT 'Array of language codes',
    operating_hours JSON COMMENT 'Operating hours by day of week',
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_city (city),
    INDEX idx_status (status),
    INDEX idx_moh_license (moh_license_number),
    FULLTEXT idx_description (name, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Bookings Table:

SQL

CREATE TABLE bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    center_id BIGINT UNSIGNED NOT NULL,
    service_id BIGINT UNSIGNED,
    calendly_event_id VARCHAR(255) UNIQUE,
    calendly_event_uri TEXT,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    questionnaire_responses JSON COMMENT 'Pre-booking questionnaire answers',
    status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'no_show') DEFAULT 'pending',
    notes TEXT,
    cancellation_reason TEXT,
    cancelled_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_center_id (center_id),
    INDEX idx_status (status),
    INDEX idx_booking_date (booking_date),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Consents Table (PDPA Compliance):

SQL

CREATE TABLE consents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    consent_type ENUM('account', 'marketing_email', 'sms_notifications', 'analytics_cookies') NOT NULL,
    consent_given BOOLEAN NOT NULL,
    consent_text TEXT NOT NULL COMMENT 'Snapshot of privacy policy at time of consent',
    consent_version VARCHAR(10) NOT NULL COMMENT 'Privacy policy version',
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_consent_type (consent_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Audit Logs Table (PDPA Compliance):

SQL

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED,
    action VARCHAR(100) NOT NULL COMMENT 'created, updated, deleted, viewed',
    auditable_type VARCHAR(255) NOT NULL COMMENT 'Model class name',
    auditable_id BIGINT UNSIGNED NOT NULL COMMENT 'Model ID',
    old_values JSON COMMENT 'Data before change',
    new_values JSON COMMENT 'Data after change',
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_auditable (auditable_type, auditable_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Content Translations Table:

SQL

CREATE TABLE content_translations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    translatable_type VARCHAR(255) NOT NULL COMMENT 'Model class name',
    translatable_id BIGINT UNSIGNED NOT NULL COMMENT 'Model ID',
    locale VARCHAR(5) NOT NULL COMMENT 'en, zh, ms, ta',
    field VARCHAR(100) NOT NULL COMMENT 'Field name being translated',
    value TEXT NOT NULL COMMENT 'Translated content',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_translation (translatable_type, translatable_id, locale, field),
    INDEX idx_translatable (translatable_type, translatable_id),
    INDEX idx_locale (locale)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
Indexing Strategy
Primary Keys: All tables use auto-incrementing BIGINT UNSIGNED primary keys for scalability.

Foreign Keys: All relationships enforced with foreign key constraints; appropriate ON DELETE actions (CASCADE for dependent data, SET NULL for optional references).

Unique Indexes:

users.email, users.phone - Prevent duplicate accounts
centers.slug, centers.moh_license_number - Ensure uniqueness
bookings.calendly_event_id - Prevent duplicate Calendly events
subscriptions.email - One subscription per email
Composite Indexes:

(center_id, status) on bookings - Common query pattern: fetch center's active bookings
(user_id, created_at) on bookings - User's booking history sorted by date
(translatable_type, translatable_id, locale, field) on content_translations - Fast translation lookups
Full-Text Indexes:

(name, description) on centers - Support search queries
(name, description) on services - Service search
Data Retention & Archival Policy
Active Data:

Users with activity in last 2 years: Retained in primary tables
Bookings within 2 years of service date: Retained for customer service
Archival Process:

Inactive users (2 years no login): Deletion notice email sent → 30-day grace period → soft delete → 30 days → hard delete
Historical bookings (>2 years old): Anonymize personal data (keep aggregated analytics only)
Audit logs: Retained 7 years (legal requirement), then purged
Deletion Workflow:

SQL

-- Automated job runs daily
-- Step 1: Identify inactive users
SELECT id FROM users 
WHERE last_login_at < DATE_SUB(NOW(), INTERVAL 2 YEAR)
  AND deleted_at IS NULL;

-- Step 2: Send deletion notice email

-- Step 3: After 30 days, soft delete
UPDATE users 
SET deleted_at = NOW() 
WHERE last_login_at < DATE_SUB(NOW(), INTERVAL 2 YEAR + 30 DAY);

-- Step 4: After 30 days of soft delete, hard delete
DELETE FROM users 
WHERE deleted_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
Backup & Recovery Strategy
Automated Backups:

AWS RDS Automated Backups: Daily snapshots at 2 AM SGT, 7-day retention
Point-in-Time Recovery: 5-minute granularity, 7-day window
Cross-Region Replication: Snapshots copied to secondary AWS region (DR)
Manual Backups:

Monthly snapshots retained for 1 year (compliance/audit)
Pre-deployment backups before major releases
Recovery Testing:

Quarterly DR drills: Restore backup to staging, verify data integrity
Recovery Time Objective (RTO): 4 hours
Recovery Point Objective (RPO): 5 minutes (via point-in-time recovery)
Backup Verification:

Automated checksum validation
Weekly backup restoration test to isolated environment
Alert if backup size deviates >20% from expected
8. Integration Architecture
Calendly API Integration
Purpose: Enable seamless appointment scheduling with automated calendar management.

Authentication:

Initial Setup: OAuth 2.0 authorization flow for admin to connect Calendly account
Server-to-Server: Personal Access Token for API calls from Laravel backend
Endpoints Used:

text

GET https://api.calendly.com/event_types
- List available appointment types (e.g., "Initial Consultation", "Facility Tour")
- Called when: Admin configures booking settings
- Response: Array of event type objects with URIs and settings

POST https://api.calendly.com/scheduled_events
- Create new scheduled appointment
- Called when: User submits booking form
- Payload: {
    "event": "https://api.calendly.com/event_types/{uuid}",
    "invitee": {
      "email": "user@example.com",
      "name": "John Tan"
    },
    "answers": [ ... ] // Pre-booking questionnaire responses
  }
- Response: Event object with confirmation details

DELETE https://api.calendly.com/scheduled_events/{uuid}
- Cancel scheduled appointment
- Called when: User or admin cancels booking
Webhook Configuration:

JSON

{
  "url": "https://eldercare-sg.example.com/api/webhooks/calendly",
  "events": [
    "invitee.created",
    "invitee.canceled"
  ],
  "signing_key": "..."
}
Webhook Handler:

PHP

<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CalendlyWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Verify webhook signature
        if (!$this->verifySignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }
        
        $event = $request->input('event');
        $payload = $request->input('payload');
        
        match($event) {
            'invitee.created' => $this->handleInviteeCreated($payload),
            'invitee.canceled' => $this->handleInviteeCanceled($payload),
            default => Log::warning("Unhandled Calendly webhook: {$event}"),
        };
        
        return response()->json(['status' => 'processed']);
    }
    
    private function handleInviteeCreated(array $payload): void
    {
        $calendlyEventId = $payload['event']['uri'];
        
        $booking = Booking::where('calendly_event_id', $calendlyEventId)->first();
        
        if ($booking && $booking->status === 'pending') {
            $booking->update(['status' => 'confirmed']);
            
            // Trigger confirmation email and SMS
            app(NotificationService::class)->sendBookingConfirmation($booking);
        }
    }
    
    private function handleInviteeCanceled(array $payload): void
    {
        $calendlyEventId = $payload['event']['uri'];
        
        $booking = Booking::where('calendly_event_id', $calendlyEventId)->first();
        
        if ($booking && $booking->status !== 'cancelled') {
            $booking->update([
                'status' => 'cancelled',
                'cancellation_reason' => 'Canceled via Calendly',
                'cancelled_at' => now(),
            ]);
            
            app(NotificationService::class)->sendBookingCancellation($booking);
        }
    }
    
    private function verifySignature(Request $request): bool
    {
        $signature = $request->header('Calendly-Webhook-Signature');
        $body = $request->getContent();
        $secret = config('services.calendly.webhook_secret');
        
        $expected = hash_hmac('sha256', $body, $secret);
        
        return hash_equals($expected, $signature);
    }
}
Error Handling:

PHP

try {
    $event = $this->calendlyService->createScheduledEvent($data);
} catch (CalendlyApiException $e) {
    // Log error
    Log::error('Calendly API error', [
        'message' => $e->getMessage(),
        'status_code' => $e->getStatusCode(),
    ]);
    
    // Fallback: Save booking without Calendly integration
    $booking->update([
        'status' => 'pending_manual_confirmation',
        'notes' => 'Calendly integration unavailable. Manual confirmation required.',
    ]);
    
    // Notify admin
    Notification::route('slack', config('services.slack.webhook'))
        ->notify(new CalendlyIntegrationFailure($booking));
    
    // User sees: "Booking received. We'll confirm via email within 24 hours."
}
Rate Limiting: Calendly API allows 1000 requests/hour; our usage estimated at <100/hour (booking creation is infrequent).

Monitoring:

Track API response times (New Relic)
Alert if error rate >5%
Dashboard showing Calendly integration health
Mailchimp API Integration
Purpose: Manage newsletter subscriptions with PDPA-compliant double opt-in process.

Authentication: API Key in Authorization: Bearer header

Endpoints Used:

text

POST https://{dc}.api.mailchimp.com/3.0/lists/{list_id}/members
- Add subscriber to mailing list
- Called when: User submits newsletter form
- Payload: {
    "email_address": "user@example.com",
    "status": "pending", // PDPA double opt-in
    "merge_fields": {
      "FNAME": "John",
      "LNAME": "Tan"
    },
    "tags": ["website_signup"]
  }

PATCH https://{dc}.api.mailchimp.com/3.0/lists/{list_id}/members/{email_hash}
- Update subscriber preferences
- Called when: User updates newsletter preferences

DELETE https://{dc}.api.mailchimp.com/3.0/lists/{list_id}/members/{email_hash}
- Permanently delete subscriber
- Called when: User requests account deletion (PDPA compliance)
Subscription Workflow:

User submits email on website
API call to Mailchimp with status: "pending"
Mailchimp sends double opt-in email
User clicks confirmation link in email
Mailchimp updates status to "subscribed"
Mailchimp webhook notifies our system
We update subscriptions table
Webhook Handler:

PHP

public function handleMailchimpWebhook(Request $request)
{
    $event = $request->input('type');
    $data = $request->input('data');
    
    match($event) {
        'subscribe' => $this->handleSubscribe($data),
        'unsubscribe' => $this->handleUnsubscribe($data),
        'profile' => $this->handleProfileUpdate($data),
        default => Log::info("Unhandled Mailchimp webhook: {$event}"),
    };
    
    return response()->json(['status' => 'ok']);
}

private function handleSubscribe(array $data): void
{
    Subscription::updateOrCreate(
        ['email' => $data['email']],
        [
            'mailchimp_subscriber_id' => $data['id'],
            'status' => 'subscribed',
            'subscribed_at' => now(),
        ]
    );
}

private function handleUnsubscribe(array $data): void
{
    Subscription::where('email', $data['email'])->update([
        'status' => 'unsubscribed',
        'unsubscribed_at' => now(),
    ]);
}
PDPA Compliance:

Double opt-in ensures explicit consent
Store consent timestamp in subscriptions.subscribed_at
Webhook syncs unsubscribes back to our database
Account deletion triggers Mailchimp API call to permanently delete subscriber
Error Handling:

Queue-based subscription sync (retry on failure)
If Mailchimp unavailable, store locally and sync later
User sees: "Subscription pending. Please check your email to confirm."
Twilio SMS Integration
Purpose: Send booking confirmations and reminders via SMS to Singapore phone numbers.

Authentication: Account SID and Auth Token

Configuration:

Sender Number: Singapore number (+65) registered with Twilio
Service: Twilio Messaging Service for reliability
Message Templates:

PHP

// config/sms-templates.php
return [
    'booking_confirmation' => "Your visit to {center_name} is confirmed for {date} at {time}. Address: {address}. Contact: {phone}.",
    
    'booking_reminder_3days' => "Reminder: Your visit to {center_name} is in 3 days on {date} at {time}. Looking forward to seeing you!",
    
    'booking_reminder_1day' => "Reminder: Your visit to {center_name} is tomorrow at {time}. See you soon!",
    
    'booking_cancellation' => "Your booking for {date} at {time} with {center_name} has been canceled. Contact us at {phone} if you have questions.",
];
Implementation:

PHP

<?php

namespace App\Services;

use Twilio\Rest\Client;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    private Client $client;
    private string $fromNumber;
    
    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.account_sid'),
            config('services.twilio.auth_token')
        );
        
        $this->fromNumber = config('services.twilio.from_number');
    }
    
    public function sendBookingConfirmationSMS(Booking $booking): void
    {
        $message = $this->formatTemplate('booking_confirmation', [
            'center_name' => $booking->center->name,
            'date' => $booking->booking_date->format('d M Y'),
            'time' => $booking->booking_time,
            'address' => $booking->center->address,
            'phone' => $booking->center->phone,
        ]);
        
        $this->sendSMS($booking->user->phone, $message);
    }
    
    public function sendSMS(string $to, string $message): void
    {
        try {
            $this->client->messages->create($to, [
                'from' => $this->fromNumber,
                'body' => $message,
            ]);
            
            Log::info('SMS sent successfully', ['to' => $to]);
            
        } catch (\Twilio\Exceptions\TwilioException $e) {
            Log::error('Failed to send SMS', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
            
            throw $e; // Re-throw for job retry mechanism
        }
    }
    
    private function formatTemplate(string $template, array $variables): string
    {
        $message = config("sms-templates.{$template}");
        
        foreach ($variables as $key => $value) {
            $message = str_replace("{{$key}}", $value, $message);
        }
        
        return $message;
    }
}
Scheduling Reminders:

PHP

// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Send reminder SMS 3 days before booking
    $schedule->call(function () {
        $bookings = Booking::where('booking_date', now()->addDays(3)->toDateString())
            ->where('status', 'confirmed')
            ->get();
        
        foreach ($bookings as $booking) {
            SendBookingReminderSMS::dispatch($booking, '3days');
        }
    })->dailyAt('09:00'); // 9 AM SGT
    
    // Send reminder SMS 1 day before booking
    $schedule->call(function () {
        $bookings = Booking::where('booking_date', now()->addDay()->toDateString())
            ->where('status', 'confirmed')
            ->get();
        
        foreach ($bookings as $booking) {
            SendBookingReminderSMS::dispatch($booking, '1day');
        }
    })->dailyAt('09:00');
}
Cost Optimization:

SMS sent only for confirmed bookings (not pending)
No SMS for same-day bookings (too late for reminders)
Rate limiting: Max 100 SMS/hour (prevents runaway costs from bugs)
Cost tracking: Log all SMS sends with cost data
Estimated Costs:

Twilio SMS (Singapore): ~SGD 0.10 per message
Expected volume: 200 bookings/month × 3 SMS each (confirmation + 2 reminders) = 600 SMS/month = ~SGD 60/month
Cloudflare Stream Integration (Phase 2)
Purpose: Host and deliver virtual
