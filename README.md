# Event Management System - WordPress Assignment

A complete custom event management system built for WordPress that allows users to view, browse, and register for events with a comprehensive admin panel for managing registrations.

## 📋 Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Setup Instructions](#setup-instructions)
- [Usage](#usage)
- [Optional Features](#optional-features)
- [File Structure](#file-structure)
- [Technologies Used](#technologies-used)

## ✨ Features

### Core Features

✅ **Custom Post Type: Events**
- Event Title and Description
- Event Date (Custom field)
- Event Location (Custom field)
- Event Capacity (Custom field)
- Featured Image support

✅ **Frontend Display**
- Events listing page with pagination
- Archive page for all events
- Single event detail page
- Remaining seats counter
- Responsive design (mobile-friendly)

✅ **Event Registration System**
- Custom registration form with validation
- Fields: Name, Email, Number of Attendees
- Form validation (client-side & server-side)
- Security: Nonce verification, data sanitization
- Prevents duplicate email registrations
- Capacity check before registration

✅ **Admin Panel**
- View all registrations
- Filter by specific event
- Registration statistics (capacity, registered, remaining)
- Export registrations as CSV file

✅ **Security**
- Nonce verification on all forms
- Data sanitization and validation
- SQL injection prevention
- XSS protection

### Optional Features (Included)

✅ **Email Confirmation**
- Automatic confirmation email sent to attendees
- Includes event details and registration info

✅ **Remaining Seats Counter**
- Real-time display on event cards
- Color-coded indicators (green/orange/red)
- "FULL" status when capacity reached

✅ **Google Maps Integration**
- Event location displayed on map
- Embedded on single event page

## 📦 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Active WordPress theme (included: hashedsystem theme)

## 🚀 Installation

### Step 1: Activate the Plugin

1. Navigate to WordPress admin panel
2. Go to **Plugins > Installed Plugins**
3. Find **Event Registration System**
4. Click **Activate**

The plugin will automatically create the required database table (`wp_event_registrations`) upon activation.

### Step 2: Verify Theme

The custom theme "hashedsystem" should already be activated. If not:

1. Go to **Appearance > Themes**
2. Activate **hashedsystem** theme

## 🔧 Setup Instructions

### 1. Create Events

1. Go to **Events > Add New** in WordPress admin
2. Fill in the event details:
   - **Title**: Event name
   - **Description**: Full event description
   - **Event Date**: Select date and time
   - **Event Location**: Enter full address
   - **Event Capacity**: Set maximum attendees
   - **Featured Image**: Upload event image (recommended: 800x600px)
3. Click **Publish**

### 2. Create Events Listing Page

1. Go to **Pages > Add New**
2. Enter page title: "Events" or "Upcoming Events"
3. In the **Template** dropdown (right sidebar), select **Events Listing**
4. Click **Publish**
5. Note the page URL for navigation

### 3. Configure Navigation (Optional)

1. Go to **Appearance > Menus**
2. Add the Events page to your menu
3. Save menu

### 4. Configure Email Settings (Optional)

For email confirmations to work properly:

1. Install an SMTP plugin (e.g., WP Mail SMTP)
2. Configure your email settings
3. Test email functionality

## 📖 Usage

### For Site Visitors

#### View Events
- Visit the Events listing page
- Browse all upcoming events
- See event details, date, location, and availability
- Click "Read More" to view full event details

#### Register for an Event
1. Click on an event to view details
2. Scroll down to the registration form
3. Fill in:
   - Full Name
   - Email Address
   - Number of Attendees
4. Click **Register Now**
5. Receive confirmation message and email

### For Administrators

#### Manage Events
- Go to **Events > All Events**
- Edit, delete, or view events
- See registration count for each event

#### View Registrations
1. Go to **Events > Registrations**
2. Select an event from dropdown (or view all)
3. See registration details:
   - Attendee name
   - Email address
   - Number of attendees
   - Registration date

#### Export Registrations
1. Go to **Events > Registrations**
2. Select an event
3. Click **Export CSV** button
4. CSV file will download automatically

## 🎯 Optional Features

### 1. Email Confirmation ✅
Automatically sends confirmation emails to attendees after successful registration.

**Included in:** `includes/class-event-registration.php` (line 70-98)

### 2. Remaining Seats Counter ✅
Displays real-time seat availability on event cards and single event pages.

**Included in:**
- `template-events.php` (line 51-56)
- `single-event.php` (line 45-55)

### 3. Google Maps Integration ✅
Embeds Google Maps showing event location on single event pages.

**Included in:** `single-event.php` (line 80-91)

### 4. Event Duplicate Feature ✅
Quick duplicate functionality in admin panel to clone events with all metadata.

**Included in:** `includes/class-event-post-type.php` (line 173-242)
- One-click event duplication from Events list
- Auto-increments numbering (Event 1, Event 2, etc.)
- Copies all meta fields and featured image

## 📁 File Structure

```
hashedsystem/
├── wp-content/
│   ├── plugins/
│   │   └── event-registration-system/
│   │       ├── event-registration-system.php (Main plugin file)
│   │       ├── includes/
│   │       │   ├── class-event-post-type.php (Custom post type)
│   │       │   ├── class-event-database.php (Database operations)
│   │       │   ├── class-event-registration.php (Registration handler)
│   │       │   └── class-event-admin.php (Admin panel & CSV export)
│   │       └── assets/
│   │           ├── css/
│   │           │   ├── styles.css (Frontend styles)
│   │           │   └── admin.css (Admin styles)
│   │           └── js/
│   │               └── registration.js (Form validation & AJAX)
│   │
│   └── themes/
│       └── hashedsystem/
│           ├── functions.php (Theme functions)
│           ├── header.php (Theme header)
│           ├── footer.php (Theme footer)
│           ├── template-events.php (Events listing template)
│           ├── single-event.php (Single event template)
│           └── archive-event.php (Event archive template)
│
└── README.md (This file)
```

## 🛠 Technologies Used

1. **WordPress Core**
   - Custom Post Types API
   - Meta Boxes API
   - Settings API
   - Hooks & Filters

2. **PHP**
   - OOP (Object-Oriented Programming)
   - WordPress best practices
   - Security functions (sanitization, validation, nonces)

3. **MySQL**
   - Custom database table
   - Prepared statements (SQL injection prevention)

4. **JavaScript/jQuery**
   - AJAX form submission
   - Client-side validation
   - Dynamic UI updates

5. **HTML5 & CSS3**
   - Semantic markup
   - Responsive design (Flexbox, Grid)
   - Mobile-first approach

6. **WordPress REST API**
   - Used in custom post type registration
   - Enables future API integrations

## 🔐 Security Features

- ✅ Nonce verification on all forms
- ✅ Data sanitization (sanitize_text_field, sanitize_email, absint)
- ✅ Data validation (server-side & client-side)
- ✅ SQL prepared statements (prevents SQL injection)
- ✅ Capability checks (manage_options for admin)
- ✅ XSS prevention (esc_html, esc_attr, esc_url)
- ✅ CSRF protection via nonces

## 📱 Responsive Design

The system is fully responsive and mobile-friendly:

- ✅ Tablet (768px): Single column grid
- ✅ Mobile (480px): Optimized forms and layouts
- ✅ Desktop (1200px+): Multi-column grid layout

## 🧪 Testing Checklist

- [ ] Activate plugin successfully
- [ ] Create new event with all fields
- [ ] View events on listing page
- [ ] Register for an event
- [ ] Receive confirmation email
- [ ] View registrations in admin
- [ ] Export CSV file
- [ ] Test capacity limit
- [ ] Test duplicate email prevention
- [ ] Test responsive design on mobile
- [ ] Test form validation

## 📝 Development Approach

This project was developed following WordPress coding standards:

- No page builders used (all hand-coded)
- Clean, well-documented code
- Modular architecture (OOP classes)
- Reusable components
- Security-first approach
- Performance optimized

## 👨‍💻 Author

**HashedSystem**
- Developed for: Job Application Assignment
- Completion Time: Within 36 hours deadline
- Contact: [Your Contact Information]

## 📄 License

GPL v2 or later

---

**Note:** This is a demonstration project created for a job application assignment. All requirements have been implemented including optional features (email confirmation, remaining seats counter, and Google Maps integration).

## 🎉 Submission

This assignment includes:

✅ Custom Post Type with meta fields
✅ Custom Plugin for registrations
✅ Custom database table
✅ Frontend templates (listing, single, archive)
✅ Admin panel with CSV export
✅ Security implementation
✅ Responsive design
✅ All optional features
✅ Complete documentation

**Ready for deployment and testing!**
