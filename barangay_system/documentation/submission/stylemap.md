# STYLE GUIDE

A style guide is a document that outlines the visual and design elements of an application to ensure consistency in its branding and user interface. Here's an overview of the style guide for the **Barangay Calauag Flood and Drainage Incident Reporting and Management System**.

---

## 1. Typography
The system uses a modern, clean, and highly readable typeface to ensure clarity during emergency reporting and administrative tasks.

*   **Primary Font:** `Inter`, sans-serif (via Google Fonts).
*   **Fallback Fonts:** `system-ui`, `-apple-system`, `BlinkMacSystemFont`, `Segoe UI`, `Roboto`, `Helvetica Neue`, `Arial`.
*   **Base Line Height:** `1.6`
*   **Scale:**
    *   **Stat Numbers:** `32px` (Extra Bold)
    *   **Page Headers (H2):** `30px` (Emerald/Dark Green, Bold)
    *   **Section Titles:** `20px`
    *   **Body Text:** `15px` / `14px`
    *   **Captions/Labels:** `13px` (Semi-bold, often Uppercase)

---

## 2. Color Palette
The color scheme is designed to evoke a sense of authority, cleanliness (water-focused), and clear status differentiation.

### Primary Branding (Teal & Emerald)
Used for Headers, Sidebars, and Primary Action Buttons.
*   **Deep Teal:** `#155E6C` (Sidebar Background)
*   **Primary Teal:** `#1F7A8C` (Navbar / Header Background)
*   **Emerald Dark:** `#065f46` (Table Headers, Dashboard H2 Titles)
*   **Success Green:** `#059669` (Active Buttons, Form Focus Border)

### Dashboard & Analytics Palette
Used for Stat Cards, Summary Boxes, and Data Visualization.
*   **Blue (Reports Info):** `#3b82f6` (Border), `#eff6ff` (Icon Background) - *Used for "Total Reports" and "Active Inquiries"*
*   **Green (Success/Resolved):** `#10b981` (Border), `#ecfdf5` (Icon Background) - *Used for "Resolved Incidents" and "Completed Tasks"*
*   **Amber (Warning/Pending):** `#f59e0b` (Border), `#fffbeb` (Icon Background) - *Used for "Pending Approval" and "Ongoing Alerts"*
*   **Red (Danger/Critical):** `#ef4444` (Border), `#fef2f2` (Icon Background) - *Used for "High Severity Floods" and "Emergency Requests"*
*   **Indigo (Teams/Staff):** `#6366f1` (Border), `#eef2ff` (Icon Background) - *Used for "Dispatched Teams" and "Personal Profiles"*
*   **Teal (System/Misc):** `#14b8a6` (Border), `#f0fdfa` (Icon Background) - *Used for "System Announcements" and "User Logs"*

### Neutral & Backgrounds
*   **App Background:** `#f0f2f5` (Main contrast for Cards)
*   **Card/Surface:** `#FFFFFF` (White containers)
*   **Input Background:** `#f9fafb` (Light gray for form fields)
*   **Text (Primary):** `#111827` (Near Black)
*   **Text (Muted):** `#6b7280` (Gray - used for labels and descriptions)
*   **Border Color:** `#e5e7eb` (Standard separators)

### Semantic / Status Colors (Pills & Badges)
Categorizes incident reports and user states.
*   **Critical:** `#7f1d1d` (Background), `#FFFFFF` (Text)
*   **High/Danger:** `#fee2e2` (Background), `#b91c1c` (Text)
*   **Medium/Warning:** `#fef3c7` (Background), `#92400e` (Text)
*   **Low/Success:** `#dcfce7` (Background), `#166534` (Text)
*   **In Progress:** `#e0e7ff` (Background), `#3730a3` (Text)

---

## 3. UI Components & Layout

### Layout Structure
*   **Sticky Header:** Uses a teal gradient with a subtle shadow and backdrop filter.
*   **Sidebar:** Fixed-width (`260px`) navigation with active state indicators in Cyan (`#4FB3BF`).
*   **Content Area:** Spacious padding (`32px - 40px`) to ensure data doesn't feel cramped.

### Visual Containers (Cards)
*   **Border Radius:** `16px` for cards, `10px` for buttons/inputs.
*   **Elevations:** Subtle shadows used to separate layers: `0 2px 12px rgba(0, 0, 0, 0.07)`.
*   **Interactions:** Cards use a lift effect on hover (`transform: translateY(-3px)`).

### Forms & Inputs
*   **Design:** Floating or labeled inputs with icons for better context.
*   **Focus State:** Emerald border (`#059669`) with a soft outer glow.
*   **Buttons:** Rich gradients for primary actions; subtle gray/white borders for secondary actions.

### Data Presentation (Tables)
*   **Header:** Bold Emerald with white text.
*   **Rows:** Alternating hover states for better row tracking.
*   **Badges:** Pill-shaped labels with icons for quick visual scanning of incident status.
