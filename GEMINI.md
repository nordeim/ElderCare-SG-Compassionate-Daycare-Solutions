# GEMINI.md — ElderCare SG Project Context

This document provides a comprehensive overview for the Gemini AI agent to understand the ElderCare SG project's purpose, architecture, and development conventions. It is sourced from the project's extensive documentation.

## 1. Project Overview

### Purpose & Vision
ElderCare SG is a compassionate, accessibility-first web platform designed to connect Singaporean families with trustworthy elderly daycare services. The core mission is to empower families to make informed decisions through a transparent, culturally sensitive, and seamless digital experience. The platform is engineered to the highest standards of regulatory compliance (Singapore PDPA & MOH), performance, and accessibility (WCAG 2.1 AA).

### Architecture
The system is a **service-oriented monolith** designed for modularity and scalability.

-   **Frontend (`frontend/`):** A modern, server-first application built with **Next.js 14** (App Router), **React 18**, and **TypeScript**. Styling is managed by **Tailwind CSS** using a design token system, and components are built with **Radix UI** for accessibility. State management is handled by **React Query** (server state) and **Zustand** (global client state).

-   **Backend (`backend/`):** A robust API built on **Laravel 12** and **PHP 8.2**. It follows a service-layer architecture with thin controllers delegating business logic to dedicated service classes and data access to repositories. Authentication is handled by **Laravel Sanctum**.

-   **Database & Caching:** The primary database is **MySQL 8.0**. **Redis** is used for caching, session storage, and managing background job queues.

-   **Infrastructure & DevOps:** The application is fully containerized using **Docker** for local development. Staging and production environments are hosted on **AWS ECS Fargate**, with infrastructure managed via **Terraform**. The CI/CD pipeline is built with **GitHub Actions**, enabling automated testing and deployment to staging.

### Current Status
The project has successfully completed its foundational phases:
-   **Phase 1: Foundation, Infrastructure & Analytics:** The project structure, Docker environment, CI/CD pipeline, and database schema are all in place.
-   **Phase 2: Design System, UI Components & i18n:** A comprehensive, accessible component library has been built and documented in Storybook, and the internationalization framework is complete for English and Mandarin.

The project is currently in a **Pre-Phase 3 Remediation Stage**, addressing minor gaps from the initial phases before commencing **Phase 3: Core Backend Services & PDPA Compliance**. This next phase involves the complete implementation of the Laravel backend API, including authentication, all core business logic, booking system integration, and robust PDPA/MOH compliance features, as detailed in the Phase 3 execution sub-plan.

## 2. Backend Architecture & Data Model

### Data Model & Schema
The backend is built upon a comprehensive **18-table MySQL 8.0 schema** that is meticulously designed for compliance, scalability, and multilingual support.
-   **Compliance-First:** The schema has dedicated tables and columns for **PDPA (Singapore)** and **MOH** regulations. This includes a polymorphic `audit_logs` table for tracking all data changes, a `consents` table for versioned user consent, and soft deletes on critical tables. MOH compliance is handled via specific fields in the `centers` and `staff` tables (e.g., `moh_license_number`).
-   **Relational Core:** The core entities include `users`, `profiles`, `centers`, `services`, `bookings`, and `testimonials`, with well-defined relationships and constraints.
-   **Advanced Data Structures:** The design leverages **polymorphic relationships** for reusable `media` (S3-backed) and `content_translations` tables, enabling flexible content management. **JSON columns** are used for semi-structured data like operating hours, amenities, and questionnaire responses.
-   **Performance:** The schema is optimized with a full suite of indexes, including composite and full-text indexes, and includes pre-built `VIEWS` for complex queries like center summaries.
-   **Integration-Ready:** Columns are pre-defined to store unique identifiers from external services like Calendly, Mailchimp, and Twilio.

### Service Layer Architecture
The Laravel backend follows a strict **service-oriented architecture** to ensure separation of concerns and maintainability. Business logic is encapsulated within dedicated service classes, keeping controllers thin and focused on handling HTTP requests.
-   **Key Services:** The architecture is composed of specialized services, including:
    -   `AuthService`: Handles user registration, login, and password resets.
    -   `ConsentService` & `AuditService`: Manage all PDPA-related logic.
    -   `UserService`: Manages user profiles, data export, and account deletion.
    -   `CenterService`: Manages eldercare center data and MOH compliance.
    -   `BookingService`: Orchestrates the entire booking workflow.
    -   `CalendlyService` & `TwilioService`: Abstract external API interactions.
    -   `NotificationService`: Manages and queues all email and SMS notifications.
    -   `MediaService`: Handles file uploads to S3 and media associations.
-   **Automation:** An `AuditObserver` is used to automatically log model changes, ensuring the PDPA audit trail is always complete.

### API Design & Infrastructure
The backend exposes a versioned, secure, and well-documented RESTful API.
-   **Versioning:** All routes are prefixed with `/api/v1`.
-   **Standardization:** The API uses a standardized JSON response format for both successes and errors, a global exception handler for predictable error codes (4xx/5xx), and Laravel API Resources for data transformation.
-   **Security:** Authentication is handled by **Laravel Sanctum**. Authorization is managed via role-based middleware and granular policies. **Rate limiting** is applied to prevent abuse.
-   **Documentation:** The entire API will be documented using the **OpenAPI 3.0** specification, with a Postman collection provided for easy testing and integration.

## 3. Building and Running

The recommended method for running the project is via Docker.

### Prerequisites
-   Docker & Docker Compose
-   Node.js 18+ and npm
-   PHP 8.2+ and Composer

### Docker Quick Start
1.  **Clone the repository:**
    ```bash
    git clone https://github.com/eldercare-sg/web-platform.git
    cd web-platform
    ```

2.  **Set up environment files:**
    ```bash
    cp .env.example .env
    cp frontend/.env.local.example frontend/.env.local
    # Edit the .env files with appropriate credentials if needed.
    ```

3.  **Start the application:**
    ```bash
    docker-compose up -d
    ```

4.  **Run database migrations:**
    ```bash
    docker-compose exec backend php artisan migrate
    ```

### Accessing the Application
-   **Frontend:** [http://localhost:3000](http://localhost:3000)
-   **Backend API:** [http://localhost:8000](http://localhost:8000)
-   **Storybook (Frontend Components):** Run `cd frontend && npm run storybook`

### Key Commands
-   **Run all backend tests (PHPUnit):**
    ```bash
    docker-compose exec backend composer test
    ```
-   **Run all frontend tests (Jest/RTL):**
    ```bash
    docker-compose exec frontend npm test
    ```
-   **Run frontend End-to-End tests (Playwright):**
    ```bash
    docker-compose exec frontend npm run test:e2e
    ```

## 3. Development Conventions

### Git Workflow
-   Work is done on feature branches (e.g., `feature/TASK-123-auth-service`, `bugfix/login-form-validation`).
-   Changes are merged into the `main` branch via Pull Requests (PRs).
-   All PRs must pass the full suite of CI checks (linting, unit tests, integration tests, accessibility checks).
-   The `main` branch is automatically deployed to the staging environment.

### Testing
A comprehensive, multi-layered testing strategy is a core project requirement.
-   **Backend:** Unit and feature tests are written with PHPUnit.
-   **Frontend:**
    -   Unit/Integration tests are written with **Jest** and **React Testing Library**.
    -   End-to-End tests for critical user journeys are written with **Playwright**.
    -   **Accessibility testing** is automated with `jest-axe` in every component test.
    -   **Visual regression testing** is performed with **Percy** on the Storybook component library.
-   High test coverage (>90%) is mandated for all new code.

### Documentation
-   The `docs/` directory is the single source of truth for all project documentation, including architecture, plans, and runbooks.
-   All code changes that impact architecture, features, or operational procedures must be accompanied by corresponding documentation updates.
-   Significant architectural decisions must be recorded in an Architectural Decision Record (ADR).