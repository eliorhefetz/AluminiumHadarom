# Aluminium Hadarom — Active Business Website & Operations Platform

> **Portfolio note**  
> This project powers a **live production business**. For that reason, the repository shared publicly is intentionally limited, and full source access is provided **only to authorized parties**.

## Overview

**Aluminium Hadarom** is a custom-built web platform that combines a public-facing business website with a private internal management system for day-to-day operations.

The platform was designed to support both sides of the business:

- a marketing and sales website for customers
- a structured back-office system for internal workflows
- operational tools for finance, CRM, staff management, attendance, reporting, and document processing

This is not a generic template or a one-purpose admin panel. It is a multi-module production system built around real business processes, with dedicated flows for sales, operations, payments, reporting, employee activity, and content management.

---

## Production Access Policy

Because this is an **active business system**, the full codebase is **not published publicly**.

The private implementation contains production-only assets such as:

- internal business logic
- protected operational endpoints
- environment-based service integrations
- production infrastructure and deployment configuration
- restricted administration modules

For security and operational reasons, **full file access, implementation details, and deployment-level configuration are available only to approved reviewers, employers, or authorized collaborators**.

---

## Live Platform Scope

The system is split into two main layers:

### 1. Public Website
A customer-facing website used to present the business, showcase work, and support lead generation and product discovery.

### 2. Internal Management System
A private administrative environment used to manage customers, finances, reports, employees, work documentation, attendance, and business operations.

---

## Public Website Features

The public website includes core business pages and conversion-oriented routes such as:

- **Homepage**
- **Gallery / project showcase**
- **Shop**
- **Product pages**
- **Terms**
- **Privacy Policy**
- **Accessibility page**

Known public routes include:

- `/`
- `/gallery`
- `/shop`
- `/product/custom-roller-shutter`
- `/terms`
- `/privacy-policy`
- `/accessibility`

The website is designed to support a real aluminium business brand, combining visual presentation with operational utility and future growth.

---

## Admin / Back-Office Modules

The private management system is modular and includes dedicated areas for business operations.

### Customer & CRM Operations
- Add new clients and projects
- Enter and update client data
- Manage customer records through a CRM interface
- Internal communication workflows, including SMS-oriented infrastructure

### Expenses & Procurement
- Materials expense management
- Operating expense tracking
- Future expense planning
- Future expense management workflows

### Income & Financial Planning
- General income entry
- Future income tracking
- Future income management
- Payment state handling for pending vs paid items
- Installment / checks / payment allocation logic

### Management & Business Control
- Employee management
- Business KPI monitoring
- OCR-based document scanning workflows
- Quote / proposal management

### Utility Tools
- Product pricing tools
- Tax bracket utilities
- Price list tools
- Account summary tools
- Work documentation tools

### Additional Internal Areas
- Calendar management
- Reports center
- Cashflow management

---

## Attendance & Workforce Features

The system includes a dedicated attendance layer with operational APIs and employee workflows.

Implemented capabilities include:

- employee clock-in / clock-out flows
- lunch break start / end handling
- open shift validation
- employee time log retrieval
- device trust / approval workflows
- exception approval and rejection flows
- kiosk-oriented attendance actions
- attendance status and history endpoints

The attendance logic also supports paid time calculations with break-aware handling and operational time-window control.

---

## Reporting & Decision Support

A dedicated reports engine powers business visibility and decision-making.

Supported report domains include:

- **Warehouse reports**
- **Vehicle reports**
- **Business reports**
- **Performance / KPI reports**

The reporting layer includes logic for:

- configurable report categories
- amortization loading
- active vehicle profile usage
- future income and future expense analysis
- materials and operating cost aggregation
- cashflow breakdowns
- VAT-oriented financial calculations
- business target and performance evaluation

This module was built to serve as an internal decision-support layer rather than only a simple visual dashboard.

---

## OCR & Intelligent Document Processing

One of the more advanced parts of the platform is the OCR and receipt/document processing pipeline.

This workflow includes:

- image upload handling
- preprocessing and enhancement
- multi-crop image generation for extraction accuracy
- supplier recognition logic
- tax ID matching
- address and recipient validation
- structured JSON extraction
- automated category mapping
- VAT / net / gross computation
- optional automatic database persistence when strict validation passes
- review flags when confidence or validation rules fail

The document-processing flow is designed for real operational use, not just experimental OCR output.

---

## Work Documentation & Media Pipeline

The platform includes a work documentation module used to preserve business activity and prepare media for showcase use.

Capabilities include:

- uploading images and videos for work records
- organizing files into structured storage folders
- generating gallery-ready outputs
- converting supported assets to web-friendly formats
- writing structured metadata for uploaded work items
- separating archival documentation from public gallery assets

This creates a bridge between internal field documentation and public-facing showcase content.

---

## Progressive Web App & Offline Support

The platform includes PWA-related functionality and service worker behavior for selected routes.

Implemented capabilities include:

- service worker caching
- offline fallback handling
- pre-cached login and attendance assets
- installable web-app behavior through a web manifest
- push notification event handling
- notification click routing back into the application

These capabilities improve resilience and usability for operational flows that may be used in real-world mobile scenarios.

---

## Router & Asset Strategy

The application uses a custom route-based structure with separated concerns for:

- public routes
- API handlers
- middleware-based access control
- page-level asset loading
- management-specific layouts
- cache-busting through file modification versions

This makes the platform more maintainable than a flat PHP site and supports modular growth over time.

---

## Integrations

The production system includes integration points for external services and infrastructure commonly required by business software.

Examples visible from the project structure and configuration include:

- payment gateway integration
- push notification / VAPID configuration
- maps integration
- translation service integration
- OCR / AI document extraction integration
- SMS integration infrastructure
- email configuration

Public repository snapshots intentionally omit production secrets and private environment values.

---

## Technical Profile

This platform is built as a **custom PHP application** with a modular internal structure and environment-driven configuration.

Project characteristics include:

- PHP-based server-side architecture
- PDO/database-driven logic
- custom routing layer
- session-protected internal modules
- JSON API endpoints for operational features
- production-oriented file and media handling
- environment-based secret management
- modular folder separation between public entry, source code, storage, and vendor packages

---

## Why This Project Matters

This project demonstrates the ability to build and maintain more than a visual website.

It reflects work across:

- full-stack development
- internal business systems
- workflow design
- operational automation
- file and media processing
- financial logic
- employee-facing tooling
- production architecture decisions
- integration-heavy web development

In practice, this platform acts as both a **business website** and a **custom operating system for a real company**.

---

## Repository Availability

Because the application is connected to a live business environment, the **full repository is not distributed publicly**.

### Publicly shareable materials may include:
- high-level documentation
- selected screenshots
- architecture summaries
- feature overviews
- portfolio-safe code excerpts

### Restricted materials remain private:
- full source code
- production endpoints
- internal business rules
- credentials and secrets
- deployment configuration
- live operational data structures tied to the business

---

## Access Requests

If you are reviewing this project for:

- employment
- freelance collaboration
- technical due diligence
- partnership evaluation

access to additional implementation details can be discussed **case by case** and shared only when appropriate.

---

## Status

**Status:** Active production project  
**Visibility:** Public overview only  
**Source access:** Restricted to authorized parties
