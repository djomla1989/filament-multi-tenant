# Work Order Management System

## Overview

The Work Order Management System is a core feature of the Core Tenant platform, designed to help service-based businesses efficiently track, manage, and communicate about customer work orders. This system enables businesses to create detailed work orders, track their progress through customizable status workflows, and provide customers with real-time visibility into their service requests.

## Key Features

### Work Categories and Statuses

- **Work Categories**: Define different service categories that your business offers (e.g., "Phone Repair", "Screen Replacement", "Battery Service")
- **Status Workflows**: Create customized status workflows for each category with sequential order (e.g., "Waiting" → "In Progress" → "Ready for Pickup" → "Completed")

### Customer Management

- **Customer Database**: Maintain a comprehensive database of customers with contact information
- **Quick Customer Creation**: Create new customers on-the-fly during work order creation
- **Customer Association**: Link customers to their work orders for easy tracking and history

### Work Order Creation and Tracking

- **Detailed Work Orders**: Create work orders with comprehensive information about the service needed
- **Custom Details**: Add unlimited custom key-value details specific to each type of service
- **Status Tracking**: Track the progress of work orders through the defined workflow
- **Work History**: Maintain a complete history of status changes and comments
- **Estimated Completion**: Set and track estimated completion dates

### Customer Notifications

- **Multiple Channels**: Send notifications through email, SMS, WhatsApp, or Viber
- **Tracking Links**: Generate unique tracking links for customers to monitor their order status
- **QR Codes**: Create QR codes that customers can scan to access their tracking page
- **Status Updates**: Automatically notify customers of status changes (optional per update)

### Public Tracking Portal

- **No Login Required**: Customers can track their orders without creating an account
- **Real-time Updates**: Show customers the current status and history of their order
- **Responsive Design**: Mobile-friendly interface accessible on any device

## Technical Architecture

### Database Schema

#### Tables

1. **customers**
   - id (primary key)
   - name
   - email
   - phone
   - organization_id (foreign key to organizations)
   - user_id (nullable foreign key to users)
   - timestamps

2. **work_orders**
   - id (primary key)
   - order_number (unique within organization)
   - title
   - description
   - customer_id (foreign key to customers)
   - work_category_id (foreign key to work_categories)
   - current_status_id (foreign key to work_category_statuses)
   - tracking_token (unique)
   - notification_channel (enum: email, sms, whatsapp, viber)
   - estimated_completion_date (nullable)
   - created_by_id (foreign key to users)
   - organization_id (foreign key to organizations)
   - timestamps

3. **work_order_history**
   - id (primary key)
   - work_order_id (foreign key to work_orders)
   - status_id (foreign key to work_category_statuses)
   - notes (nullable)
   - is_public (boolean)
   - created_by_id (nullable foreign key to users)
   - timestamps

4. **work_order_details**
   - id (primary key)
   - work_order_id (foreign key to work_orders)
   - key (string)
   - value (text)
   - timestamps

### Key Components

#### Models

- **Customer**: Represents a customer who brings in items for service
- **WorkOrder**: The central model representing a service request
- **WorkOrderHistory**: Records each status change and comment in the work order's lifecycle
- **WorkOrderDetail**: Stores custom key-value pairs for work order details

#### Services

- **WorkOrderService**: Handles the business logic for creating and updating work orders
- **NotificationService**: Manages sending notifications across different channels
- **QrCodeGenerator**: Generates QR codes for work order tracking

#### Notification Channels

- **EmailNotificationChannel**: Sends notifications via email
- **SmsNotificationChannel**: Sends notifications via SMS
- **WhatsAppNotificationChannel**: Sends notifications via WhatsApp
- **ViberNotificationChannel**: Sends notifications via Viber

#### Controllers and Views

- **WorkOrderTrackingController**: Handles public tracking requests
- **tracking.blade.php**: Public tracking view for customers

## Work Order Lifecycle

### 1. Creation

When a tenant receives an item from a customer:

1. Tenant creates a new work order by filling out a form with:
   - Customer information (existing or new)
   - Work category
   - Initial status
   - Description and details
   - Notification preferences

2. The system:
   - Creates or updates the customer record
   - Generates a unique work order with an auto-incremented number
   - Creates an initial history entry
   - Generates a unique tracking token and URL
   - Creates a QR code linking to the tracking URL
   - Sends a notification to the customer with tracking information

### 2. Updates

As work progresses:

1. Tenant updates the work order status
2. The system:
   - Updates the current status
   - Creates a history entry with notes
   - Optionally notifies the customer of the change

### 3. Completion

When the work is finished:

1. Tenant sets the status to "Completed" or similar final status
2. The system:
   - Updates the current status
   - Creates a history entry
   - Notifies the customer that their order is complete

## Customer Experience

1. **Notification**: Customer receives an email, SMS, WhatsApp, or Viber message with:
   - Order information
   - Current status
   - Tracking link
   - QR code (for email)

2. **Tracking**: Customer can:
   - Click the tracking link or scan the QR code
   - View the current status of their order
   - See the complete public history of the order
   - View estimated completion time
   - See detailed information about their service

## Tenant Administration

Through the Filament admin interface, tenants can:

1. **Manage Work Categories**:
   - Create and edit service categories
   - Define status workflows for each category

2. **Manage Customers**:
   - Create and edit customer records
   - View customer history and work orders

3. **Manage Work Orders**:
   - Create detailed work orders
   - Update status and add notes
   - View complete work history
   - Generate QR codes and tracking links
   - Send manual notifications

## Implementation Notes

### Tenant Isolation

All data is properly isolated by tenant using Filament's multi-tenancy features:

- Global scopes ensure that users can only see data from their organization
- Resources are filtered to show only the tenant's data
- Tracking URLs are secured with random tokens, not sequential IDs

### Notification Channels

The system uses a flexible notification channel architecture:

- Common interface for all channels via the `NotificationChannel` contract
- Easy addition of new channels in the future
- Channel-specific implementations for each notification type

### Customization

The system is designed for easy customization:

- Work categories and statuses are fully customizable per tenant
- Work order details use a flexible key-value approach to adapt to any service type
- The notification system can be extended with new channels

## Future Enhancements

Potential future enhancements to the system:

1. **Payment Integration**: Add payment processing for services
2. **Inventory Management**: Track parts used in repairs
3. **Customer Portal**: Allow customers to create accounts and view all their orders
4. **Service Packages**: Define standard service packages with set pricing
5. **Staff Assignment**: Assign work orders to specific staff members
6. **SLA Tracking**: Monitor and ensure service level agreements are met
7. **Analytics Dashboard**: Provide insights into service performance and trends
