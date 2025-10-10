# AI Agent Operational Plan — ElderCare SG

**Version:** 1.2
**Status:** Active
**Owner:** AI Coding Agent
**Primary References:** `docs/AGENT.md`, `Project_Architecture_Document.md`, `codebase_completion_master_plan.md`

---

## 1. Purpose

This document outlines the standard operating procedure (SOP) for the AI Coding Agent working on the ElderCare SG web platform. It serves as a transparent and auditable guide to ensure every action taken aligns with the project's core principles of technical excellence, strategic partnership, and compassionate design. It is a living document, intended to be updated as the project evolves.

## 2. Synthesized Understanding of the Project

I have assimilated the core documentation and understand the following key aspects of the ElderCare SG platform:

- **Mission:** To be a compassionate, accessibility-first digital bridge connecting Singaporean families with trusted elderly daycare services.
- **Architecture:** A service-oriented Laravel 12 monolith backend with a performant, server-first Next.js 14 frontend. The system is containerized via Docker and deployed on AWS ECS Fargate, with infrastructure managed by Terraform.
- **Core Principles:**
    - **Accessibility First:** WCAG 2.1 AA compliance is a mandatory quality gate.
    - **Security & Compliance by Design:** Adherence to Singapore's PDPA is paramount.
    - **Performance Optimized:** Strict Lighthouse performance budgets (>90) are enforced.
    - **Cultural Sensitivity:** The platform is multilingual and designed for Singapore's diverse culture.
- **Quality & Operations:** A robust CI/CD pipeline, comprehensive automated testing, and detailed observability are central to the development lifecycle.

## 3. Current Codebase State & Immediate Objectives (As of 2025-10-10)

### 3.1. Project Progress
**Phases 1 and 2 are COMPLETE.** The project is now in a **Pre-Phase 3 Remediation Stage** before officially beginning **Phase 3: Core Backend Services & PDPA Compliance**.

### 3.2. Codebase State Summary
- **Backend (`backend/`):** The Laravel application is structured, and all database migrations for the target schema exist. Service-level business logic is not yet implemented.
- **Frontend (`frontend/`):** The Next.js application has a fully implemented i18n layer and a comprehensive, accessible component library documented in Storybook. Core application pages and state management are not yet implemented.
- **Testing & QA:** A mature, multi-layered testing infrastructure (Jest, `jest-axe`, Percy, Playwright) is integrated into the CI pipeline.
- **DevOps & Infrastructure:** The project is fully containerized, with core infrastructure defined in Terraform and a CI/CD pipeline deploying to staging.

### 3.3. Immediate Objective: Pre-Phase 3 Remediation
My immediate priority is to execute the tasks outlined in `docs/llm/pre_phase3_remediation_execution_plan_2025-10-10.md`. This involves resolving outstanding gaps from Phases 1 and 2 to ensure a stable foundation for Phase 3. Key tasks include:

1.  **Migration Hardening:** Add database driver checks to `services`, `bookings`, and `testimonials` migrations to ensure SQLite compatibility for testing.
2.  **Analytics & Monitoring Bootstrap:** Finalize the scaffolding for GA4, Hotjar, Sentry, and New Relic, including helper modules, environment variables, and documentation.
3.  **Infrastructure Documentation:** Create `docs/deployment/cloudflare.md` and enhance the `terraform/README.md` to improve operational readiness.
4.  **Design System QA Closure:** Complete Storybook story coverage for remaining components and capture the initial Percy baseline for visual regression testing.
5.  **Phase 3 Enablement Prep:** Create architectural notes, service skeletons, and OpenAPI drafts for the upcoming authentication and consent services.

## 4. Agent's Pledge of Adherence

I commit to deep analysis, systematic planning, technical excellence, unyielding quality, transparent communication, and strategic partnership in all my work.

## 5. Standard Operational Workflow for Tasks

I will follow a structured workflow for every assigned task: Analysis & Planning, Implementation & Verification, and Delivery & Handoff. My immediate focus will be applying this workflow to the remediation tasks listed above.

### Phase 1: Analysis & Planning
1.  **Task Deconstruction:** Analyze the user request against the current project phase (starting with Pre-Phase 3 Remediation).
2.  **Contextual Immersion:** Review relevant source-of-truth documents.
3.  **Codebase Reconnaissance:** Study existing code to inform the implementation plan.
4.  **Plan Formulation:** Draft a detailed execution plan and present it for approval.

### Phase 2: Implementation & Verification
1.  **Environment Setup:** Synchronize the local Docker environment and run all existing tests.
2.  **Branch Creation:** Create a new Git branch following project conventions.
3.  **Test-Driven Development (TDD):** Write failing tests before implementation.
4.  **Code Implementation:** Write code to make tests pass, adhering to all standards.
5.  **Local Validation:** Run all relevant quality checks locally.
6.  **Documentation:** Update documentation concurrently with code.

### Phase 3: Delivery & Handoff
1.  **Pull Request (PR) Creation:** Create a PR targeting the `main` branch.
2.  **PR Checklist Completion:** Meticulously fill out the PR template.
3.  **CI/CD Validation:** Ensure all automated checks pass.
4.  **Review & Iteration:** Respond to feedback and iterate.
5.  **Merge & Cleanup:** After approval, merge and delete the feature branch.
6.  **Post-Merge Monitoring:** Observe the staging deployment.

## 6. Pull Request Checklist Template

I will use the standard PR checklist for all submissions, ensuring traceability to the task and plan, and verifying that all quality, documentation, and process gates have been met.

## 7. Handling Ambiguity and Conflict

If a request is ambiguous or conflicts with the project's architecture or principles, I will halt execution, document the conflict, propose aligned solutions, and await explicit guidance before resuming work.
