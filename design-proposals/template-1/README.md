# Le Petit Kangaroo — Template 1 (Mobile-first)

This proposal includes two responsive UI screens:

1. `events-listing.html`
2. `event-detail.html`

## Design notes
- **Mobile-first Auto Layout equivalent**: all sections use stacked flex/grid containers that expand into multi-column at desktop breakpoints.
- **Reusable components**:
  - Buttons (`.btn`, `.btn-primary`, `.btn-secondary`) with hover + pressed states.
  - Chips (`.chip`) with hover + pressed states.
  - Date badges (`.date-badge`).
  - Event cards (`.event-card`).
- **Brand tone**: warm premium neutrals using dusty rose / beige / cream / soft brown.
- **Typography**: serif headings + clean sans body.

## Access paths
- Source files are kept in this folder for design iteration.
- WordPress-friendly preview files are also available under:
  - `public_html/fr/design-proposals/template-1/events-listing.htm`
  - `public_html/fr/design-proposals/template-1/event-detail.htm`

These `.htm` routes are provided to match `/fr/...` redirects and avoid 404s.
