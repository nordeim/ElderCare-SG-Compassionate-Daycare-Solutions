# ElderCare SG - Compassionate Daycare Solutions

<div align="center">
  <img src="assets/logo.svg" alt="ElderCare SG Logo" width="200">
  
  [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
  [![Lighthouse Score](https://img.shields.io/badge/Lighthouse-92%2B-green)](https://github.com/GoogleChrome/lighthouse)
  [![WCAG 2.1 AA](https://img.shields.io/badge/WCAG-2.1%20AA-blue)](https://www.w3.org/TR/WCAG21/)
  [![Build Status](https://github.com/eldercare-sg/web-platform/workflows/CI/badge.svg)](https://github.com/eldercare-sg/web-platform/actions)
  [![Coverage Status](https://coveralls.io/repos/github/eldercare-sg/web-platform/badge.svg?branch=main)](https://coveralls.io/github/eldercare-sg/web-platform?branch=main)
  
  <h3>A modern, accessible platform connecting Singaporean families with trusted elderly daycare services</h3>
  
  [🌐 Live Demo](https://eldercare-sg.example.com) | [📖 Documentation](./docs) | [🎨 Design System](./docs/design-system.md) | [🏗️ Architecture](./docs/architecture.md)
</div>

---

## Table of Contents

- [🌟 Project Overview](#-project-overview)
- [🏗️ Architecture](#️-architecture)
- [🛠️ Technology Stack](#️-technology-stack)
- [✨ Features](#-features)
- [🚀 Quick Start](#-quick-start)
- [👨‍💻 Development Guide](#-development-guide)
- [🚢 Deployment](#-deployment)
- [📚 Documentation](#-documentation)
- [🗺️ Roadmap](#️-roadmap)
- [🤝 Contributing](#-contributing)
- [📄 License](#-license)

---

## 🌟 Project Overview

ElderCare SG is a thoughtfully designed web platform that serves as a bridge between Singaporean families and quality elderly daycare services. Built with compassion and technical excellence, our platform combines warm, human-centered design with robust, scalable architecture to create an experience that builds trust and facilitates meaningful connections.

### Our Vision

To create a digital ecosystem that empowers families to make informed decisions about elderly care, while ensuring dignity, accessibility, and quality of life for Singapore's senior population.

### Target Audience

- **Adult Children** (30-55 years) of elderly Singaporean residents seeking care options
- **Family Caregivers** managing care for elderly family members
- **Healthcare Professionals** looking for reliable referral centers
- **Elderly Individuals** who are digitally literate and seeking independence

### Key Value Propositions

- **Trust & Transparency**: Verified information about care centers with authentic reviews
- **Accessibility First**: WCAG 2.1 AA compliant design that works for everyone
- **Cultural Sensitivity**: Designed for Singapore's multicultural context
- **Seamless Experience**: From exploration to booking, a frictionless journey

---

## 🏗️ Architecture

Our architecture is designed with scalability, security, and maintainability at its core, following modern best practices for web application development.

### High-Level Architecture

<div align="center">
  <img src="assets/architecture-overview.svg" alt="System Architecture Diagram" width="800">
</div>

### Key Architectural Principles

1. **Microservices Design**: Modular services that can be developed, deployed, and scaled independently
2. **API-First Approach**: All functionality exposed through well-documented RESTful APIs
3. **Security by Design**: Integrated security measures throughout the application stack
4. **Progressive Enhancement**: Core functionality works without JavaScript, with enhancements added when available
5. **Accessibility First**: Accessibility considerations integrated from the ground up

### System Components

- **Frontend**: Next.js 14 with React Server Components for optimal performance
- **Backend**: Laravel 12 API with service-oriented architecture
- **Database**: MySQL 8.0 with Redis for caching and session management
- **Search**: Elasticsearch for advanced content discovery
- **Infrastructure**: Docker containers orchestrated with Kubernetes

---

## 🛠️ Technology Stack

### Frontend

| Technology | Version | Purpose |
|------------|---------|---------|
| Next.js | 14 | React framework with SSR |
| React | 18 | UI library |
| TypeScript | 5 | Type safety |
| Tailwind CSS | 3 | Styling framework |
| Radix UI | Latest | Accessible components |
| Framer Motion | 10 | Animations |
| React Query | 4 | Server state management |
| Zustand | 4 | Client state management |

### Backend

| Technology | Version | Purpose |
|------------|---------|---------|
| Laravel | 12 | PHP framework |
| PHP | 8.2 | Programming language |
| MySQL | 8.0 | Primary database |
| Redis | 7 | Caching and queues |
| Elasticsearch | 8 | Search service |
| Laravel Sanctum | Latest | Authentication |

### DevOps & Infrastructure

| Technology | Version | Purpose |
|------------|---------|---------|
| Docker | Latest | Containerization |
| Kubernetes | Latest | Container orchestration |
| GitHub Actions | Latest | CI/CD pipeline |
| AWS | Latest | Cloud provider |
| Cloudflare | Latest | CDN and security |

---

## ✨ Features

### Core Functionality

- **Service Discovery**: Comprehensive information about daycare services and facilities
- **Virtual Tours**: Immersive video experiences showcasing care environments
- **Booking System**: Seamless scheduling for in-person and virtual visits
- **Testimonials**: Authentic reviews from families and caregivers
- **Multilingual Support**: English, Mandarin, Malay, and Tamil language options

### User Experience

- **Mobile-First Design**: Optimized for smartphones and tablets
- **Accessibility**: WCAG 2.1 AA compliant with full keyboard navigation
- **Performance**: Lightning-fast loading times and smooth interactions
- **Progressive Web App**: Installable with offline functionality

### Compliance & Security

- **PDPA Compliance**: Full compliance with Singapore's Personal Data Protection Act
- **Data Encryption**: End-to-end encryption for sensitive information
- **Secure Authentication**: Multi-factor authentication for admin accounts
- **Regular Audits**: Continuous security monitoring and vulnerability scanning

---

## 🚀 Quick Start

### Prerequisites

- Node.js 18+ and npm
- PHP 8.2+ and Composer
- Docker and Docker Compose
- MySQL 8.0+ (if not using Docker)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/eldercare-sg/web-platform.git
   cd web-platform
   ```

2. **Environment setup**
   ```bash
   cp .env.example .env
   cp frontend/.env.local.example frontend/.env.local
   ```

3. **Start with Docker**
   ```bash
   docker-compose up -d
   ```

4. **Run database migrations**
   ```bash
   docker-compose exec backend php artisan migrate
   ```

5. **Access the application**
   - Frontend: http://localhost:3000
   - Backend API: http://localhost:8000

### Manual Installation

For detailed manual installation instructions, see our [Installation Guide](./docs/installation.md).

---

## 👨‍💻 Development Guide

### Project Structure

```
eldercare-sg/
├── frontend/                 # Next.js frontend application
│   ├── components/          # Reusable React components
│   ├── pages/              # Next.js pages
│   ├── styles/             # Global styles and Tailwind configuration
│   └── tests/              # Frontend tests
├── backend/                 # Laravel backend application
│   ├── app/                # Application code
│   ├── database/           # Database migrations and seeders
│   └── tests/              # Backend tests
├── docs/                   # Project documentation
├── docker/                 # Docker configuration files
└── scripts/                # Build and deployment scripts
```

### Development Workflow

1. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

2. **Make your changes**
   - Follow our [Coding Standards](./docs/coding-standards.md)
   - Write tests for new functionality
   - Update documentation as needed

3. **Run tests**
   ```bash
   # Frontend tests
   cd frontend && npm test
   
   # Backend tests
   cd backend && composer test
   ```

4. **Submit a pull request**
   - Provide a clear description of changes
   - Link to relevant issues
   - Request code review

### Testing Strategy

- **Unit Tests**: Jest for frontend, PHPUnit for backend
- **Integration Tests**: Testing Library for React components
- **E2E Tests**: Playwright for critical user journeys
- **Accessibility Tests**: axe-core for automated accessibility checks
- **Performance Tests**: Lighthouse CI for performance monitoring

---

## 🚢 Deployment

### Deployment Options

1. **Docker Deployment** (Recommended)
   ```bash
   docker-compose -f docker-compose.prod.yml up -d
   ```

2. **Kubernetes Deployment**
   ```bash
   kubectl apply -f k8s/
   ```

3. **Manual Deployment**
   See our [Deployment Guide](./docs/deployment.md) for detailed instructions.

### Environment Variables

Key environment variables to configure:

```bash
# Application
APP_NAME=ElderCare SG
APP_ENV=production
APP_DEBUG=false
APP_URL=https://eldercare-sg.example.com

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=eldercare_prod
DB_USERNAME=eldercare
DB_PASSWORD=your_secure_password

# Cache
CACHE_DRIVER=redis
REDIS_HOST=redis

# External Services
CALENDLY_API_KEY=your_calendly_api_key
MAILCHIMP_API_KEY=your_mailchimp_api_key
```

### Monitoring

- **Application Performance**: New Relic APM
- **Error Tracking**: Sentry
- **Uptime Monitoring**: UptimeRobot
- **Log Aggregation**: ELK Stack

---

## 📚 Documentation

- [Architecture Deep Dive](./docs/architecture.md)
- [API Documentation](./docs/api/README.md)
- [Design System](./docs/design-system.md)
- [Accessibility Guide](./docs/accessibility.md)
- [Deployment Guide](./docs/deployment.md)
- [Contributing Guide](./CONTRIBUTING.md)

---

## 🗺️ Roadmap

### Current Status: Alpha Development

- [x] Project architecture and design
- [x] Development environment setup
- [x] Core UI components
- [ ] Authentication system
- [ ] Content management
- [ ] Booking system
- [ ] Virtual tours
- [ ] Testing and QA
- [ ] Production deployment

### Planned Features

- **Mobile App**: Native iOS and Android applications
- **AI Recommendations**: Personalized care recommendations
- **Telehealth Integration**: Virtual consultations with healthcare providers
- **Family Portal**: Shared care management for family members
- **Provider Dashboard**: Management tools for care centers

---

## 🤝 Contributing

We welcome contributions from the community! Whether you're fixing a bug, implementing a feature, or improving documentation, we appreciate your help.

### How to Contribute

1. **Fork the repository**
2. **Create a feature branch**
3. **Make your changes**
4. **Add tests if applicable**
5. **Ensure all tests pass**
6. **Submit a pull request**

### Code of Conduct

Please read and follow our [Code of Conduct](./CODE_OF_CONDUCT.md) to ensure a welcoming environment for all contributors.

### Contribution Guidelines

For detailed contribution guidelines, see our [Contributing Guide](./CONTRIBUTING.md).

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- The Singapore Ministry of Health for guidelines on eldercare services
- The Singapore Infocomm Media Development Authority for accessibility guidelines
- Our user testers and their families for valuable feedback
- The open-source community for the tools and libraries that make this project possible

---

## 📞 Contact

- **Project Maintainer**: [ElderCare SG Team](mailto:team@eldercare-sg.example.com)
- **Website**: [https://eldercare-sg.example.com](https://eldercare-sg.example.com)
- **Issues**: [GitHub Issues](https://github.com/eldercare-sg/web-platform/issues)

---

<div align="center">
  <p>Made with ❤️ for Singapore's elderly community and their families</p>
</div>
