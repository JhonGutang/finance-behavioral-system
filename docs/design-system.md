# Financial Garden: Design System & UI Principles

> **Philosophy:** Calm, Disciplined, and Premium. The interface should feel like a well-tended garden—orderly, organic in its growth, and high in clarity.

---

## 1. Visual Philosophy: "The Disciplined Garden"
Every element must serve a purpose. We avoid "over-designed" clutter in favor of high-contrast, solid-colored elements that convey stability and wealth.

- **High Contrast:** Never use semi-transparent or low-opacity text for critical information (e.g., avoid `text-emerald-900/40`, use `text-emerald-800`).
- **Solid Grounds:** Use solid background colors and clear borders (`border-stone-300` or `border-emerald-100`) to define sections.
- **Micro-Animations:** Use subtle, slow transitions (`duration-1000`, `animate-in`) to simulate organic growth.

---

## 2. Color Palette
Focused on the "Financial Garden" theme—Emerald for growth, Stone for foundation, and Rose for corrective feedback.

| Type | Name | Tailwind Class | Application |
| :--- | :--- | :--- | :--- |
| **Primary** | Deep Forest | `bg-emerald-950` | Headers, Primary Buttons |
| **Secondary** | Growth Emerald | `text-emerald-800` | Sub-labels, Descriptions |
| **Neutral** | Foundation Stone | `bg-[#FDFCF8]` | System Background |
| **Positive** | Harvest Green | `text-emerald-600` | Income, Positive Trends |
| **Negative** | Drought Rose | `text-rose-700` | Expenses, Behavioral Warnings |
| **Warning** | Sown Amber | `text-amber-700` | Partial Overspends, Alerts |

---

## 3. Component Standards: "The Strict Rulings"

### 3.1 Buttons (The 44px Standard)
All interactive buttons must follow the **Disciplined UI Button Spec**:
- **Height:** Strictly `h-11` (44px) for standard buttons.
- **Radius:** Strictly `rounded-full`.
- **Typography:** 
    - Case: `uppercase`.
    - Spacing: `tracking-widest` or `tracking-wider`.
    - Weight: `font-bold` or `font-semibold`.
    - Size: `text-[11px]` or `text-[10px]`.
- **Hierarchy:**
    - **Primary:** High-contrast background (`bg-emerald-900`), white text, shadow.
    - **Secondary/Ghost:** Solid text (`text-emerald-800`), hover background `bg-emerald-50`.

### 3.2 Typography Hierarchy
- **Headings (Serif):** `font-serif` (Playfair Display) for Page Titles and Card Titles.
- **Copy (Sans):** `font-inter` (Inter) for data points, labels, and instructional text.
- **Labels:** Always use solid colors. Avoid opacity under 60% for any text-based labels.

### 3.3 Cards & Atmosphere
- **The Glass Standard:** All bento cards use `bg-white/60 backdrop-blur-xl`.
- **Borders:** Subtle but defined using `border-emerald-100/50` or `border-stone-300`. This creates a sense of layered depth.
- **Atmosphere:** The system uses a fixed noise texture (`noise.svg`) and a soft eggshell background (`#FDFCF8`) to create a "tactile paper" feel.
- **Corners:** High radii strictly enforced (Modals: `rounded-[2.5rem]`, Cards: `rounded-3xl`).

---

## 4. Terminology (Editorial Tone)
We use organic, botanical metaphors to reinforce the theme of "growing wealth":
- **Transactions** → *Seeds* / *Ledger*
- **Insights** → *Wisdom* / *Harvest*
- **Acknowledge** → *Nurture* / *Keep in Mind*
- **Create Account** → *Establish Presence*
- **Login** → *Induct Presence*
- **CSV Import** → *Integration Protocol*

---

## 5. UX Best Practices
1. **Visibility First:** If a label is important enough to exist, it is important enough to be visible. Use high contrast.
2. **Deterministic Feedback:** UI states must clearly reflect the "Behavioral Wisdom" logic.
3. **Calm Loading:** Always use the `InitializationLoader` to mask data-fetching delays with a premium experience.
4. **Predictability:** Consistent button heights across the entire system prevent "mis-clicks" and lower cognitive load.
