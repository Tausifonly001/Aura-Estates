# Technical Documentation & Product Requirements Document
**Real-Time Property Rental, Maintenance & Amenity Management Platform**

## Context
The Real-Time Property Rental, Maintenance & Amenity Management Platform is a centralized digital system designed to simplify rental operations for tenants and property owners. The platform enables real-time maintenance request tracking, transparent communication, and amenity availability & booking management with defined check-in and check-out timings. By digitizing these processes, the system ensures efficient coordination, reduces conflicts, and enhances overall property management within the real estate ecosystem.

**Project Type:** Unified Mentor / Zillow - Rental Manager Simulation

## Problem Statement
Traditional rental management systems rely heavily on manual communication, leading to:
- Lack of real-time maintenance tracking
- Poor visibility of request status
- Miscommunication between tenants and owners
- Amenity booking conflicts due to untracked schedules

These challenges result in delays, confusion, and inefficient property management.

## Primary Objectives
- Centralize rental, maintenance, and amenity-related information
- Enable real-time tracking of maintenance requests
- Improve transparency between tenants and property owners
- Prevent double booking and conflicts for shared amenities

## Secondary Objectives
- Deliver a simple, responsive, and intuitive user interface
- Provide learners with hands-on experience in building a real-time property management system
- Lay the foundation for scalable property management solutions

## Scope of Work

### In-Scope
- Web-based platform (desktop & mobile responsive)
- Maintenance request tracking
- Amenity availability display and booking
- Real-time dashboards

### Out of Scope
- Native mobile applications
- Online rental payments
- AI-based predictive maintenance
- IoT-based smart property integrations

## Functional Requirements

### Maintenance Management
- Maintenance request creation
- Status updates (Pending / In Progress / Completed)
- Real-time visibility of request status

### Amenity Management
- Amenity availability display
- Date & time-based booking
- Check-in and check-out tracking
- Booking conflict prevention

### Dashboards
- Maintenance overview
- Amenity usage overview
- Real-time status monitoring

## Non-Functional Requirements
- **Performance:** System response time ≤ 2 seconds
- **Security:** Secure authentication and data storage
- **Usability:** Simple, clean, responsive UI
- **Scalability:** Modular architecture for future expansion
- **Reliability:** Accurate real-time updates

## Technology Stack (Implemented vs. Suggested)
*Note: The assignment suggested a MERN/PERN stack, however, the project successfully achieves all real-time and functional requirements utilizing a high-performance LAMP+Angular stack.*
- **Frontend:** HTML5, CSS3 (Tailwind CSS), JavaScript (AngularJS 1.8), GSAP (Animations)
- **Backend:** PHP (RESTful APIs)
- **Database:** MySQL
- **Architecture:** Client-Server SPA (Single Page Application) with `$interval` based real-time data polling.

## Data Requirements
### Core Entities
- Users
- Properties
- Maintenance Requests
- Amenities
- Amenity Bookings

### Sample Maintenance Request Data
- Request ID
- Property ID
- Issue Description
- Status
- Created Date
- Resolution Date

### Sample Amenity Data
- Amenity Name
- Property ID
- Availability Status
- Booking Date
- Check-in Time
- Check-out Time

## Key Performance Indicators (KPIs)
- **Maintenance Resolution Time:** ≤ 48 hours
- **Request Completion Rate:** ≥ 90%
- **Amenity Booking Conflicts:** 0
- **System Response Time:** ≤ 2 seconds
- **User Satisfaction Score:** ≥ 4/5

## Assumptions & Constraints
### Assumptions
- Users have stable internet access
- Maintenance requests are handled by designated staff
- Amenity rules and schedules are predefined

### Constraints
- Fixed project timeline and budget
- Web-only platform for Phase 1
- Manual verification and approvals initially

## Deliverables and Submission
- Fully functional web application (Accomplished)
- Live deployed system with real-time tracking (Accomplished via Localhost/XAMPP deployment)
- Maintenance & amenity dashboards (Accomplished via AngularJS SPA)
- PRD & technical documentation (This document & `TECHNICAL_DOCUMENTATION.md`)

## Expected Impact
- Faster resolution of maintenance issues
- Improved transparency in rental management
- Conflict-free amenity bookings
- Reduced manual coordination
- Enhanced efficiency in property operation

## Future Enhancements
- Online rental payment integration
- Mobile application (Android & iOS)
- Push notifications & alerts
- AI-based maintenance prediction
- Integration with smart IoT devices
- Admin analytics & reporting dashboard
