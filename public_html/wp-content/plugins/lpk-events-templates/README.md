# LPK Events Templates

Shortcodes (desktop only for now):

- `[lpk_events_desktop_listing limit="6"]`
- `[lpk_event_desktop_detail id="123"]` (alias: `[lpk_events_desktop_detail id="123"]`)

If `id` is omitted in detail shortcodes, plugin tries current event context first, then auto-falls back to a ticketed event.

Mobile shortcodes are intentionally disabled for now to avoid style/layout mixing while desktop template-1 is being matched exactly.

Templates are French-Canadian by default and switch labels to English when locale starts with `en`.

## Admin configuration

Go to **Events → LPK Templates** to configure:

- Default listing limit.
- Default detail event (used when detail shortcode is added without `id`).
- Featured events list (if set, listing uses this exact order).

## How to add into a page

### Elementor
1. Edit a page with Elementor.
2. Add a **Shortcode** widget.
3. Paste one shortcode, e.g. `[lpk_events_desktop_listing]` or `[lpk_event_desktop_detail]`.

### Gutenberg / Classic Editor
1. Add a **Shortcode** block.
2. Paste one shortcode.

## How to configure tickets

Tickets are configured per event in **Events → All Events → Edit Event → Tickets** (Event Tickets / Event Tickets Plus). The template automatically pulls ticket names/prices and links users to the checkout flow.
