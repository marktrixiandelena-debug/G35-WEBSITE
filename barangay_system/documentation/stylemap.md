# Barangay System — Complete Web Style Map & Design System

This document captures the entire visual and structural design system of the Flood and Drainage Incident Reporting and Management System. It serves as the definitive source of truth for UI/UX patterns, frontend components, and styling conventions used across both the Admin and Resident interfaces.

---

## 1. BRAND OVERVIEW
- **Brand Personality:** Authoritative, trustworthy, operational, and responsive.
- **Visual Tone:** Professional administrative interface balancing high data-density with legible layouts.
- **Design Philosophy:** "Function heavily supported by form." Prioritizes clarity, responsive utility, clear hierarchies, and explicit user feedback.
- **Core Visual Themes:** 
  - **Admin Portal:** Deep Emerald Greens (conveying official status, safety, and administrative control).
  - **Resident Portal:** Ocean Teals (conveying water management, public service, and approachability).
- **Emotional Feel:** Secure, efficient, modern, and reliable.
- **Industry/Style Category:** Government Tech / Civic Admin Dashboard.

---

## 2. COLOR SYSTEM

### Primary Palette (Admin)
- **Primary Base:** `#065f46` (Deep Emerald - Used for headers, primary buttons)
- **Primary Hover:** `#047857` (Forest Green - Used for button hovers)
- **Primary Gradient:** `linear-gradient(135deg, #065f46 0%, #047857 100%)`
- **Sidebar Background:** `#0d4a3a` (Very Dark Green)

### Primary Palette (Resident Segment)
- **Resident Base:** `#1F7A8C` (Mid Teal)
- **Resident Dark:** `#155E6C` (Dark Teal - Used for headers, bold text)
- **Resident Light/Accent:** `#4FB3BF` (Light Teal - Used for active states, highlights)
- **Resident Gradient:** `linear-gradient(135deg, #155E6C, #1F7A8C)`

### Neutral Palette
- **Background (App Base):** `#f0f2f5` (Cool Off-White)
- **Surface (Cards/Modals):** `#ffffff` (Pure White)
- **Primary Text:** `#111827` (Near Black)
- **Muted/Secondary Text:** `#6b7280` or `#4b5563` (Mid-Grays)
- **Borders (Standard):** `#e5e7eb`
- **Borders (Subtle):** `#f3f4f6`
- **Hover States (Tables/Rows):** `#f9fafb`

### Semantic Colors
- **Success:**
  - Background: `#dcfce7` | Text/Border: `#166534` / `#10b981`
- **Warning / Advisory:**
  - Background: `#fef3c7` / `#ffedd5` | Text/Border: `#92400e` / `#c2410c` / `#f59e0b`
- **Danger / Error:**
  - Background: `#fef2f2` / `#fee2e2` | Text/Border: `#dc2626` / `#ef4444` / `#b91c1c`
- **Info / General:**
  - Background: `#eff6ff` | Text/Border: `#1d4ed8` / `#3b82f6`

---

## 3. TYPOGRAPHY SYSTEM
- **Primary Font Family:** `'Inter', system-ui, -apple-system, sans-serif`
- **Font Source:** Google Fonts
- **Font Weights:** 
  - Regular (`400` - body text)
  - Medium (`500` - secondary buttons, small labels)
  - Semi-Bold (`600` - table headers, subheadings, primary buttons)
  - Bold (`700` - card titles, active navigation)
  - Extra Bold (`800` - main page `<H2>` titles, stat numbers)

**Scaling & Line Heights:**
- **H1 (Page Title Equivalent - `<h2>` markup):** `1.875rem` (Desktop) / `1.5rem` (Mobile), weight `800`.
- **H2/H3 (Card Titles):** `1rem` or `0.85rem` uppercase, weight `700`, letter-spacing `0.5px`.
- **Body Text:** `0.95rem` (Desktop) / `0.85rem` (Mobile), line-height `1.6`.
- **Labels/Captions:** `0.8rem` or `0.875rem`, weight `600`, color `#6b7280`. Always uppercase for `.filter-group label`.

---

## 4. LAYOUT SYSTEM
- **Global Structure:** Fixed sticky top header + responsive sidebar menu + fluid `.content` area.
- **App Breakpoints:**
  - **Desktop:** `> 768px` (Standard layout, persistent left sidebar).
  - **Mobile:** `<= 768px` (Drawer overlay sidebar for Admin, bottom navigation bar for Resident; heavily stacked forms).
  - **Deep Mobile:** `<= 480px` (Ultra-compact stacked actions).
- **Z-Index Layering:**
  - `100`: Sticky Dashboard Header
  - `999 - 1000`: Responsive Sidebars / Bottom Nav / Fixed Top Mobile Nav
  - `1000+`: Modal Overlays (`.form-modal`)
- **Spacing Scale:** Standard 4pt/8pt based rem measurements (`0.25rem`, `0.5rem`, `1rem`, `1.5rem`, `2rem`). Desktop containers rely on `2rem` padding; Mobile shrinks to `1rem` - `1.15rem` for higher density.

---

## 5. UI COMPONENT LIBRARY

### Buttons
- **Primary Submit (`.btn-submit`, `.btn`):** Gradient backgrounds, deep shadow (`0 2px 8px rgba`), hover translates `Y(-2px)`. Text white. Uses `0.625rem` border-radius.
- **Secondary (`.btn-cancel`, `.btn-reset`):** Gray standard (`#f3f4f6`), bordered (`#e5e7eb`), minimal shadow.
- **Action Buttons (`.action-btn`):** Miniature operational buttons assigned specific semantic colors (`.btn-view` [Blue], `.btn-enable` [Green], `.btn-disable` [Red]). Outline styling with tinted backgrounds.

### Forms
- **Input Fields (`.form-control`):** `0.5rem` to `0.625rem` radius. Soft gray background (`#f9fafb`), transitioning to white on focus with a strong semantic border outline (`box-shadow: 0 0 0 3px rgba(...)`). Height scaled for touch (approx ~42px).
- **Selects:** Custom SVG chevron backgrounds embedded seamlessly via `background-image` for a clean native-override feel.

### Navigation
- **Navbar:** Sticky gradient bar, rounded large profile picture `.logo-lg`.
- **Sidebar:** Dark monolithic background. Navigation links (`a`) pad beautifully and use heavy left borders (`3px solid transparent`) that color-fill on hover/active states.
- **Mobile Nav (Resident):** Clean, iOS-style bottom icon-based navigation bar.

### Content Components
- **Dashboard Plates (`.dashboard-panel`, `.summary-box`):** White backgrounds, `1rem` radius. Titles underlined with subtle gradients.
- **Stat Cards (`.stat-card`):** Bold left-colored border (`4px solid`) corresponding to semantic meaning. Distinct geometric icon wrappers (`.stat-icon`).
- **Data Tables:** Clear horizontal lines, gray headers (`#065f46` or `#1F7A8C` bg), padding `1rem`, hovering row highlights. On mobile, aggressively transforms into responsive stacked "cards" with synthetic `data-label` prefixes.
- **Badges (`.status-badge`, `.severity-badge`, `.role-badge`):** Aggressive pill borders (`border-radius: 2rem`), distinct FA-icon prefix, heavily tinted background colors correlating to data status.

---

## 6. ICONOGRAPHY
- **Library:** Font Awesome 6.5.0 (`all.min.css`)
- **Style:** Solid weight preferred (`fa-solid`) over regular.
- **Integration:** Used extensively in sidebar nav (`fa-gauge`, `fa-users`), table action buttons, status pills, and big dashboard stat cards. Icons inside buttons are given a fixed `pointer-events: none` interaction logic.

---

## 7. SHAPE SYSTEM
- **Corner Philosophy:** A modern mix of soft accessibility with sharp authoritative elements. 
- **Buttons / Inputs:** Slightly squared curves (`0.375rem` to `0.625rem` radius).
- **Cards / Tables / Modals:** Pronounced soft curves (`0.875rem` to `1rem` radius) making large data containers feel approachable.
- **Badges / Avatars:** Complete circles or heavy pills (`50%` or `2rem`).

---

## 8. SHADOWS & EFFECTS
- **Box Shadows:** Soft, diffuse elevation. 
  - Standard Card Elevation: `box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07)`
  - Elevated Input Focus: `box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.12)`
  - Modal Drop Shadow: `0 20px 25px -5px rgba(0,0,0,0.1)`
- **Hover Dynamics:** Interactive cards and buttons float up (`transform: translateY(-2px)`) expanding their drop shadows for physical interaction cues.
- **Glassmorphism:** Minor usage reserved for the `.profile-badge` and header navigation elements (`backdrop-filter: blur(4px); background: rgba(255, 255, 255, 0.12)`).

---

## 9. ANIMATION & INTERACTIONS
- **Modals:** Slide-in fade effect (`animation: modalFadeIn 0.3s ease-out`).
- **Sidebar Menu:** Slide-out translation (`transition: transform 0.3s ease`). Mobile navigation glides via translation coordinates.
- **Buttons:** Rapid CSS transitions (`transition: all 0.25s`) altering colors, borders, and shadows seamlessly. 
- **Wait States:** System relies on physical button state responses without heavy loading spinners. 

---

## 10. RESPONSIVE DESIGN RULES
- **Philosophy:** Mobile-First Layout Data logic, Desktop-First View Structure.
- **Typography Scaling:** Drops global paragraph fonts from `0.95rem` to `0.85rem` down screen sizes to push visual density.
- **Table Transformation:** Highly custom responsive table behavior (`@media (max-width: 768px)`). Destroys horizontal standard `<th>`, maps rows to block "cards" with left-aligned keys mapped dynamically using `content: attr(data-label)`.
- **Filters:** Shrink to wrap natively in a flexible grid (`flex: 1 1 120px` wrapper logic) with tight gap controls. 
- **Modals:** Width forces to `100%`, squeezing internal paddings down from `2rem/1.5rem` -> `1.15rem`, forcing button groupings to stack entirely vertically on `< 480px` devices.

---

## 11. FRONTEND IMPLEMENTATION DETAILS
- **Framework Approach:** Pure Vanilla HTML5/CSS3. ZERO heavy utility frameworks (No Tailwind, Bootstrap, or Materialize). 
- **Architecture Methodology:**
  1. `[module]_global.css` (The Shell: Header, Footer, Sidebar, Typographics).
  2. `admindashboard.css` (The OS Logic: Base styling for all forms, cards, tables, buttons, metrics).
  3. `[module]_components.css` (The Reskin: Swaps variables and gradient targets for different user roles—e.g., overriding Admin Greens to Resident Teals).
  4. `[page].css` (Micro-overrides: Hyper-specific tweaks like a distinct password reset toggle layout).
- **Variable Usage:** CSS native variables used heavily in base generic files like `loginreg.css` (`--primary-color`, `--bg-light`), but raw HEX used consistently in standard dashboards.

---

## 12. BRAND CONSISTENCY ANALYSIS
- **Strongest Design Traits:** The table rendering logic (combining `status-badge` designs, geometric flex borders, and mobile card flipping) is exceptionally cohesive and operational. The unified component abstraction in `admindashboard.css` ensures zero deviation between buttons across the 25+ system pages.
- **Unique Identify Markers:** The "Semantic Tinting" approach (The use of slightly off-white tinted backgrounds for alert boxes that match deeply colored borders, e.g., `#f0fdf4` bg with `#166534` text for success logs).
- **Potential Future Standardizations:** Hardcoded Hex values in `admindashboard.css` could be mapped to native CSS `:root` tokens globally to allow for instant thematic switching (e.g. Dark Mode implementation later).

---

## 13. STYLE MAP SUMMARY
The Barangay Calauag Incident System features a **mature, dense, and heavily polished vanilla-CSS architecture**. It relies on high-contrast semantic geometries (pill badges, geometric inputs, gradients) and predictable Z-axis shadow elevation to guide users. Without relying on external CSS library overhead, the system accomplishes highly robust, touch-friendly UI mechanics that scale uniformly from wide 4K admin dashboards down to compact mobile resident displays.
