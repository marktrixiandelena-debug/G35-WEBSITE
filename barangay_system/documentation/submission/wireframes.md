# FIGMA WIREFRAME & UI DESIGN STRATEGY

Since you've created everything in Figma, your documentation should highlight the **High-Fidelity Mockups** of the most critical screens. These screens demonstrate the core value of your system: transparency, ease of reporting, and efficient management.

Below are the core parts recommended for inclusion in your documentation.

### Note on Low-Fidelity (Low-Fi) Requirements:
If your teacher requires **Low-Fi wireframes**, using **Draw.io** is a perfect and fast solution. When creating them in Draw.io:
*   **Search for "Mockups":** Use the "Mockup" or "Wireframe" shape libraries in Draw.io. They provide ready-made buttons, inputs, and browser frames.
*   **Focus on Structure:** Use only black, white, and gray. Don't worry about the specific shades of teal or emerald yet.
*   **Placeholders:** Use a simple box with an "X" for images and "Lorum Ipsum" or generic text for labels.
*   **Contrast:** This shows your teacher that you planned the **user interface logic** before adding the "skin" (colors/images) in Figma.

---

## 1. Login / Landing Page
The first impression for both Residents and Administrators.
*   **Core Elements:** Hero section with a clear call-to-action (CTA) such as "Report a Flood" or "Resident Login," and a brief overview of the system's purpose.
*   **Description:** This page serves as the gateway to the system. It emphasizes accessibility and immediate action, ensuring residents know exactly where to go when an emergency happens.

## 2. Submit Report Page
The most important interactive part of the system for the community.
*   **Core Elements:** Multi-step or clearly grouped form fields (Location, Severity, Detailed Description), and a prominent **Photo Upload** area.
*   **Description:** Designed for high stress and urgency. The wireframe shows how the UI simplifies complex data entry into a few clicks, making it easy for residents to provide high-quality evidence (photos) even during active rainfall.

## 3. My Reports Page (Tracking)
The "Transparency" hub for the user.
*   **Core Elements:** A list of "My Reports" with visual **Status Badges** (Pending, In Progress, Resolved) and a timeline or detail view for individual reports.
*   **Description:** This screen validates the resident's effort. It provides peace of mind by showing real-time updates on their reports, reducing the need for follow-up calls or physical visits to the barangay hall.

## 4. Admin Dashboard
The centralized data hub for Barangay Officials.
*   **Core Elements:** **Stat Cards** (Total Alerts, Active Floods, Resolved Today) and a **Severity Triage Table** highlighting critical incidents.
*   **Description:** The primary workspace for decision-makers. The wireframe demonstrates how the system consolidates scattered data into actionable insights, allowing officials to see the "Big Picture" of the barangay's situation at a glance.

## 5. Admin Report Details
The "Execution" screen where action is taken.
*   **Core Elements:** Full incident details, resident-uploaded photos, and a **Response Assignment** section to dispatch specific teams.
*   **Description:** This screen shows the bridge between data and action. It highlights how the system creates accountability by recording which team is handling which report and tracking the resolution process.

## 6. Analytics Page (Optional)
The "Long-Term Planning" tool.
*   **Core Elements:** Charts showing flood frequency or a map view marking chronic drainage problem areas.
*   **Description:** Showcases the system’s data-driven capabilities. This wireframe illustrates how the accumulated data can be used to justify future infrastructure projects (like canal widening) to the City Government.

---

## Technical Design Considerations (UI/UX)
When presenting these in your Figma file/documentation, note the following:
*   **Mobile Responsiveness:** Show how the Resident reporting form scales down for mobile use, as most residents will report from their phones during a flood.
*   **Accessibility:** Mention the high-contrast colors used for Status Badges to ensure they are readable in various lighting conditions.
*   **Consistency:** Link back to your `stylemap.md` to show that the Figma designs follow the established Teal and Emerald branding.
