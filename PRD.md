# Product Requirements Document (PRD)

## Repair Desk - Minimal Repair Point of Sale Management System

**Version:** 1.0  
**Date:** October 5, 2025  
**Document Owner:** Product Team  
**Status:** Draft

---

## 1. Executive Summary

### 1.1 Product Vision

Repair Desk is a minimal, cloud-based Point of Sale (POS) management system designed specifically for repair shops. The system aims to streamline daily operations including customer management, repair ticket tracking, inventory control, invoicing, and payment processing - all in a simple, intuitive interface.

### 1.2 Business Objectives

-   Provide repair shops with an affordable, easy-to-use management solution
-   Reduce administrative overhead and manual paperwork
-   Improve customer experience through faster service and transparent tracking
-   Enable data-driven business decisions through reporting and analytics
-   Create a foundation for future feature expansion

### 1.3 Target Audience

-   **Primary**: Small to medium-sized repair shops (electronics, phones, computers, appliances)
-   **Secondary**: Individual repair technicians and multi-location repair chains
-   **User Personas**:
    -   Shop Owners/Managers
    -   Front Desk Staff
    -   Repair Technicians
    -   Customers (for self-service portal)

---

## 2. Problem Statement

Repair shops currently face several operational challenges:

-   Manual paper-based ticket tracking leading to lost information
-   Difficulty managing inventory and parts procurement
-   Lack of visibility into repair status for customers
-   Time-consuming invoicing and payment collection
-   No centralized system for customer history and device information
-   Limited reporting capabilities for business insights

---

## 3. Scope

### 3.1 In Scope (MVP Features)

#### 3.1.1 Customer Management

-   Create, read, update, delete customer profiles
-   Store customer contact information (name, email, phone, address)
-   View customer repair history
-   Search and filter customers
-   Customer notes and tags

#### 3.1.2 Repair Ticket Management

-   Create repair tickets/work orders
-   Assign ticket status (New, In Progress, Waiting for Parts, Completed, Delivered)
-   Link tickets to customers and devices
-   Add problem description and technician notes
-   Assign technicians to tickets
-   Set priority levels (Low, Normal, High, Urgent)
-   Track estimated completion dates
-   Upload photos and attachments
-   Ticket timeline/activity log

#### 3.1.3 Device Management

-   Record device information (type, brand, model, serial number, IMEI)
-   Device condition assessment with photos
-   Link devices to customers
-   Device history across multiple repairs

#### 3.1.4 Inventory Management

-   Manage parts and products inventory
-   Track stock levels with low-stock alerts
-   Add/remove inventory items
-   Record part usage on repair tickets
-   Simple product catalog (name, SKU, cost, selling price, quantity)
-   Inventory adjustments

#### 3.1.5 Invoicing & Payments

-   Generate invoices from repair tickets
-   Itemized billing (labor, parts, taxes)
-   Multiple payment methods (Cash, Card, Bank Transfer)
-   Partial payments and deposits
-   Payment history tracking
-   Print/email invoices
-   Tax calculation

#### 3.1.6 Point of Sale (POS)

-   Quick checkout interface
-   Sell parts/products without repair ticket
-   Apply discounts
-   Process refunds
-   Receipt generation and printing

#### 3.1.7 Reporting & Analytics

-   Daily/weekly/monthly sales reports
-   Revenue by service type
-   Technician performance metrics
-   Inventory reports
-   Customer reports
-   Ticket status overview dashboard

#### 3.1.8 User Management & Authentication

-   User registration and login
-   Role-based access control (Admin, Manager, Technician, Front Desk)
-   User profiles and permissions
-   Activity logging

#### 3.1.9 Settings & Configuration

-   Shop profile (name, address, logo, contact info)
-   Tax settings
-   Email templates
-   Ticket status customization
-   General preferences

### 3.2 Out of Scope (Future Considerations)

-   Multi-location/franchise management
-   Advanced CRM features (marketing, email campaigns)
-   E-commerce integration
-   Advanced accounting features (P&L, balance sheet)
-   Third-party integrations (QuickBooks, Xero, etc.)
-   SMS notifications
-   Warranty management
-   Supplier management
-   Employee scheduling and time tracking
-   Mobile apps (iOS/Android)
-   Customer self-service portal
-   API for third-party integrations

---

## 4. Functional Requirements

### 4.1 Customer Management

#### 4.1.1 Create Customer

**User Story**: As a front desk staff member, I want to create a new customer profile so that I can store their information for future reference.

**Acceptance Criteria**:

-   Required fields: First name, phone number
-   Optional fields: Last name, email, address, notes
-   Phone number validation
-   Email validation if provided
-   Duplicate customer detection by phone/email
-   Auto-generate unique customer ID
-   Timestamp creation date

#### 4.1.2 View Customer Details

**User Story**: As a staff member, I want to view complete customer information including their repair history so that I can provide better service.

**Acceptance Criteria**:

-   Display all customer information
-   Show list of all associated devices
-   Display repair history with ticket status
-   Show payment history
-   Display total revenue from customer
-   Show customer notes and tags

#### 4.1.3 Search Customers

**User Story**: As a staff member, I want to quickly search for customers so that I can find their information efficiently.

**Acceptance Criteria**:

-   Search by name, phone, email, or customer ID
-   Real-time search results as user types
-   Display matching results with key information
-   Filter by customer tags
-   Sort by name, creation date, or last service date

### 4.2 Repair Ticket Management

#### 4.2.1 Create Repair Ticket

**User Story**: As a front desk staff member, I want to create a repair ticket so that we can track the repair job from start to finish.

**Acceptance Criteria**:

-   Select or create customer
-   Select or add device information
-   Enter problem description
-   Set priority level
-   Assign technician (optional)
-   Set estimated completion date
-   Add initial photos of device
-   Generate unique ticket number
-   Print/email ticket receipt to customer
-   Default status: "New"

#### 4.2.2 Update Ticket Status

**User Story**: As a technician, I want to update ticket status so that everyone knows the current state of the repair.

**Acceptance Criteria**:

-   Update status (New → In Progress → Waiting for Parts → Completed → Delivered)
-   Add status notes with each change
-   Timestamp each status change
-   Notify assigned users of status changes
-   Update completion date when marked as completed

#### 4.2.3 Add Ticket Notes

**User Story**: As a technician, I want to add notes to a ticket so that I can document my findings and work performed.

**Acceptance Criteria**:

-   Add timestamped notes
-   Associate notes with user who created them
-   Support formatted text
-   Attach photos or files to notes
-   View note history chronologically
-   Internal notes vs. customer-visible notes

#### 4.2.4 View Ticket Details

**User Story**: As a staff member, I want to view all ticket information in one place so that I understand the complete repair status.

**Acceptance Criteria**:

-   Display customer and device information
-   Show current status and priority
-   Display assigned technician
-   Show problem description and all notes
-   Display parts used and labor hours
-   Show cost breakdown
-   Display timeline of all activities
-   Show attached photos/documents

### 4.3 Inventory Management

#### 4.3.1 Add Inventory Item

**User Story**: As a manager, I want to add new parts to inventory so that technicians can use them for repairs.

**Acceptance Criteria**:

-   Enter item name, SKU, description
-   Set cost price and selling price
-   Set initial quantity
-   Set reorder level (low stock alert threshold)
-   Categorize items
-   Add product images
-   Set item status (Active/Inactive)

#### 4.3.2 Use Parts on Ticket

**User Story**: As a technician, I want to add parts to a repair ticket so that the customer can be billed correctly.

**Acceptance Criteria**:

-   Search and select parts from inventory
-   Specify quantity used
-   Automatically deduct from inventory
-   Add to ticket invoice
-   Track cost vs. selling price
-   Option to add custom parts not in inventory

#### 4.3.3 View Inventory Status

**User Story**: As a manager, I want to view inventory levels so that I know what needs to be reordered.

**Acceptance Criteria**:

-   Display all inventory items with current quantities
-   Highlight low-stock items
-   Show item valuation (cost × quantity)
-   Filter by category or status
-   Search by name or SKU
-   Sort by various fields

#### 4.3.4 Adjust Inventory

**User Story**: As a manager, I want to adjust inventory quantities so that I can correct discrepancies from physical counts.

**Acceptance Criteria**:

-   Add or remove quantity
-   Provide reason for adjustment
-   Log adjustment with user and timestamp
-   Update inventory value
-   Adjustment history per item

### 4.4 Invoicing & Payments

#### 4.4.1 Generate Invoice

**User Story**: As a front desk staff member, I want to generate an invoice from a completed ticket so that I can collect payment.

**Acceptance Criteria**:

-   Auto-populate from ticket (parts, labor, customer info)
-   Itemized listing with quantities and prices
-   Subtotal, tax, and total calculation
-   Add discounts (percentage or fixed amount)
-   Include shop information and logo
-   Generate unique invoice number
-   Save as PDF
-   Email to customer

#### 4.4.2 Process Payment

**User Story**: As a front desk staff member, I want to process customer payments so that I can complete the transaction.

**Acceptance Criteria**:

-   Select payment method (Cash, Card, Bank Transfer)
-   Enter payment amount
-   Handle partial payments
-   Calculate change due
-   Record payment timestamp
-   Associate payment with invoice and ticket
-   Update ticket status to "Paid"
-   Generate and print receipt

#### 4.4.3 View Payment History

**User Story**: As a manager, I want to view all payment transactions so that I can track revenue.

**Acceptance Criteria**:

-   List all payments with date, amount, method, customer
-   Filter by date range, payment method, or customer
-   Show pending invoices
-   Display total revenue
-   Export to CSV

### 4.5 Point of Sale (POS)

#### 4.5.1 Quick Sale

**User Story**: As a front desk staff member, I want to sell parts without creating a repair ticket so that I can serve walk-in customers quickly.

**Acceptance Criteria**:

-   Search and add items to cart
-   Adjust quantities
-   Apply discounts
-   Calculate tax and total
-   Process payment
-   Print receipt
-   Update inventory
-   Optional customer association

### 4.6 Dashboard & Reporting

#### 4.6.1 Dashboard Overview

**User Story**: As a manager, I want to see key metrics at a glance so that I can understand business performance.

**Acceptance Criteria**:

-   Display ticket counts by status
-   Show today's revenue
-   Display pending invoices total
-   Show low-stock inventory items
-   Recent activity feed
-   Quick actions (New Ticket, New Customer, etc.)

#### 4.6.2 Sales Reports

**User Story**: As a manager, I want to generate sales reports so that I can analyze revenue trends.

**Acceptance Criteria**:

-   Select date range
-   Display total revenue, number of transactions
-   Break down by service type (repair vs. parts sales)
-   Display payment method distribution
-   Show daily/weekly/monthly trends
-   Export to PDF/CSV

#### 4.6.3 Technician Reports

**User Story**: As a manager, I want to see technician performance so that I can evaluate productivity.

**Acceptance Criteria**:

-   Display tickets completed per technician
-   Show average resolution time
-   Display revenue generated
-   Show current workload (assigned tickets)
-   Filter by date range

### 4.7 User Management

#### 4.7.1 User Roles & Permissions

**User Story**: As an admin, I want to control what each user can do so that I can maintain security and workflow.

**Permission Matrix**:

| Feature          | Admin | Manager | Technician | Front Desk |
| ---------------- | ----- | ------- | ---------- | ---------- |
| Manage Users     | ✓     | ✗       | ✗          | ✗          |
| View Reports     | ✓     | ✓       | ✗          | ✗          |
| Manage Inventory | ✓     | ✓       | ✗          | ✗          |
| Create Tickets   | ✓     | ✓       | ✓          | ✓          |
| Update Tickets   | ✓     | ✓       | ✓          | ✓          |
| Delete Tickets   | ✓     | ✓       | ✗          | ✗          |
| Process Payments | ✓     | ✓       | ✗          | ✓          |
| Manage Customers | ✓     | ✓       | ✓          | ✓          |
| System Settings  | ✓     | ✗       | ✗          | ✗          |

---

## 5. Non-Functional Requirements

### 5.1 Performance

-   Page load time: < 2 seconds
-   Search results: < 1 second
-   Support up to 100 concurrent users
-   Database capable of storing 100,000+ tickets

### 5.2 Security

-   HTTPS encryption for all communications
-   Password strength requirements (min 8 characters, mixed case, numbers)
-   Two-factor authentication for admin accounts
-   Session timeout after 30 minutes of inactivity
-   Role-based access control
-   Activity audit logs
-   Regular security updates
-   Data encryption at rest

### 5.3 Usability

-   Intuitive interface requiring minimal training
-   Responsive design for tablet and mobile devices
-   Keyboard shortcuts for common actions
-   Clear error messages and validation feedback
-   Consistent design language throughout
-   Accessibility compliance (WCAG 2.1 Level AA)

### 5.4 Reliability

-   99.5% uptime SLA
-   Automated daily backups
-   Disaster recovery plan
-   Data retention for 7 years

### 5.5 Scalability

-   Support shop growth from 1-50 users
-   Handle increasing data volume over time
-   Modular architecture for future features

### 5.6 Browser Compatibility

-   Chrome (latest 2 versions)
-   Firefox (latest 2 versions)
-   Safari (latest 2 versions)
-   Edge (latest 2 versions)

---

## 6. User Interface Requirements

### 6.1 Design Principles

-   Clean, modern interface
-   Minimal clicks to complete common tasks
-   Prominent search functionality
-   Action buttons clearly visible
-   Status indicators use color coding
-   Consistent navigation

### 6.2 Key Screens

#### 6.2.1 Dashboard

-   Summary cards (tickets, revenue, inventory alerts)
-   Quick action buttons
-   Recent activity timeline
-   Ticket status breakdown chart

#### 6.2.2 Ticket List

-   Table view with columns: Ticket #, Customer, Device, Status, Priority, Date, Assigned To
-   Filter and search bar
-   Status filters (tabs or dropdown)
-   Pagination
-   Sort by any column

#### 6.2.3 Ticket Detail

-   Left sidebar: Customer and device info
-   Main area: Problem description, notes timeline
-   Right sidebar: Status, priority, assigned tech, dates
-   Bottom: Parts used, labor, invoice preview
-   Action buttons: Update Status, Add Note, Add Parts, Generate Invoice

#### 6.2.4 Customer Detail

-   Customer info card
-   Tabs: Devices, Repair History, Payments
-   Quick actions: New Ticket, Edit Customer

#### 6.2.5 Inventory List

-   Grid or table view
-   Low-stock indicators
-   Search and category filters
-   Quick edit stock levels

#### 6.2.6 POS Screen

-   Left side: Item search and cart
-   Right side: Cart summary, payment processing
-   Large, touch-friendly buttons
-   Numeric keypad for quantities

---

## 7. Data Model

### 7.1 Core Entities

#### Users

-   id, name, email, password, role, phone, active, created_at, updated_at

#### Customers

-   id, first_name, last_name, email, phone, address, notes, tags, created_at, updated_at

#### Devices

-   id, customer_id, type, brand, model, serial_number, imei, notes, created_at, updated_at

#### Tickets

-   id, ticket_number, customer_id, device_id, problem_description, status, priority, assigned_to, estimated_completion, actual_completion, created_by, created_at, updated_at

#### Ticket_Notes

-   id, ticket_id, user_id, note, is_internal, created_at

#### Ticket_Attachments

-   id, ticket_id, file_path, file_name, uploaded_by, created_at

#### Inventory_Items

-   id, name, sku, description, category, cost_price, selling_price, quantity, reorder_level, status, created_at, updated_at

#### Ticket_Parts

-   id, ticket_id, inventory_item_id, quantity, cost_price, selling_price, created_at

#### Invoices

-   id, invoice_number, ticket_id, customer_id, subtotal, tax_rate, tax_amount, discount, total, status, created_at, updated_at

#### Payments

-   id, invoice_id, ticket_id, amount, payment_method, payment_date, processed_by, notes, created_at

#### Inventory_Adjustments

-   id, inventory_item_id, quantity_change, reason, adjusted_by, created_at

---

## 8. Technical Stack

### 8.1 Recommended Technology

-   **Backend**: Laravel 12 (PHP 8.3+)
-   **Frontend**: Livewire 3 + Volt (already in use)
-   **UI**: Flux UI Free + Tailwind CSS 4 (already in use)
    -   **Note**: Using Flux UI Free edition - Pro components are not available
    -   Free components available: avatar, badge, brand, breadcrumbs, button, callout, checkbox, dropdown, field, heading, icon, input, modal, navbar, profile, radio, select, separator, switch, text, textarea, tooltip
    -   For features requiring Pro components, we'll use custom Blade components styled with Tailwind CSS
-   **Database**: MySQL 8.0+ or PostgreSQL
-   **Authentication**: Laravel Fortify (already in use)
-   **Testing**: Pest 4 (already configured)
-   **Queue**: Laravel Queue (for async tasks)
-   **Storage**: Local or S3 for attachments
-   **Cache**: Redis (optional, for performance)

### 8.2 Development Tools

-   **Version Control**: Git
-   **Code Quality**: Laravel Pint, Rector
-   **Local Environment**: Laravel Herd (already in use)
-   **Build Tool**: Vite (already configured)

---

## 9. Milestones & Phases

### Phase 1: Foundation (Weeks 1-2)

-   Database schema design
-   Authentication system
-   User management
-   Basic dashboard

### Phase 2: Core Features (Weeks 3-5)

-   Customer management
-   Device management
-   Repair ticket creation and listing
-   Ticket status workflow

### Phase 3: Inventory (Weeks 6-7)

-   Inventory item management
-   Parts usage tracking
-   Low stock alerts
-   Inventory adjustments

### Phase 4: Financial (Weeks 8-9)

-   Invoice generation
-   Payment processing
-   POS functionality
-   Receipt printing

### Phase 5: Reporting (Week 10)

-   Dashboard analytics
-   Sales reports
-   Technician reports
-   Inventory reports

### Phase 6: Polish & Testing (Weeks 11-12)

-   UI/UX refinements
-   Comprehensive testing
-   Bug fixes
-   Documentation
-   User training materials

---

## 10. Success Metrics

### 10.1 Business Metrics

-   User adoption rate: 80% of target repair shops using system daily
-   Average ticket processing time reduced by 40%
-   Customer satisfaction score: 4.5+/5
-   System uptime: 99.5%+

### 10.2 User Metrics

-   Time to create a ticket: < 2 minutes
-   Time to process payment: < 1 minute
-   User training time: < 2 hours
-   Feature utilization: 70%+ of core features used weekly

---

## 11. Risks & Mitigation

### 11.1 Technical Risks

| Risk                  | Impact | Probability | Mitigation                           |
| --------------------- | ------ | ----------- | ------------------------------------ |
| Data loss             | High   | Low         | Automated backups, redundancy        |
| Security breach       | High   | Medium      | Security audits, penetration testing |
| Performance issues    | Medium | Medium      | Load testing, optimization           |
| Browser compatibility | Low    | Low         | Cross-browser testing                |

### 11.2 Business Risks

| Risk              | Impact | Probability | Mitigation                               |
| ----------------- | ------ | ----------- | ---------------------------------------- |
| Low user adoption | High   | Medium      | User training, onboarding support        |
| Feature creep     | Medium | High        | Strict scope management, phased approach |
| Competition       | Medium | Medium      | Focus on simplicity and ease of use      |

---

## 12. Assumptions & Dependencies

### 12.1 Assumptions

-   Users have stable internet connection
-   Users have modern web browsers
-   Basic computer literacy among staff
-   Shop has receipt printer (optional)
-   Shop will provide their own hosting (or use cloud)

### 12.2 Dependencies

-   Laravel framework and ecosystem
-   Livewire for reactive components
-   Flux UI Free edition (limited component set)
-   Tailwind CSS for styling and custom components
-   Modern web browser support
-   PDF generation library (e.g., DomPDF or Laravel PDF)

---

## 13. Open Questions

1. Should we support multiple currencies?
2. Do we need warranty tracking in MVP?
3. Should customers have self-service portal access?
4. Do we need barcode scanning support?
5. Should we integrate with email providers for automated notifications?
6. Do we need multi-location support in MVP?
7. What level of customization should be allowed for ticket statuses?
8. Should we support recurring service packages?

---

## 14. Appendices

### 14.1 Glossary

-   **POS**: Point of Sale
-   **MVP**: Minimum Viable Product
-   **SKU**: Stock Keeping Unit
-   **IMEI**: International Mobile Equipment Identity
-   **SLA**: Service Level Agreement

### 14.2 References

-   RepairDesk.co (reference inspiration)
-   Laravel 12 Documentation
-   Livewire 3 Documentation
-   Flux UI Component Library

---

## Document Revision History

| Version | Date        | Author       | Changes                                              |
| ------- | ----------- | ------------ | ---------------------------------------------------- |
| 1.0     | Oct 5, 2025 | Product Team | Initial draft                                        |
| 1.1     | Oct 5, 2025 | Product Team | Updated UI stack to clarify Flux UI Free limitations |

---

**Next Steps**:

1. Review and approve PRD with stakeholders
2. Create detailed technical specifications
3. Design database schema
4. Create wireframes and mockups
5. Set up development environment
6. Begin Phase 1 development
