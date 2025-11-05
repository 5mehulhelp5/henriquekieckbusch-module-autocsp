# HenriqueKieckbusch_AutoCSP

## Overview

This Magento 2 module automates the management of CSP whitelists and can dynamically capture CSP violations, add them to a database, and manage inline script CSP via nonce.

## Features

- Automatically capture and store CSP violation reports.
- Add collected CSP policies from DB to CSP headers.
- Capture mode: enable `report-uri` and `report-only`.
- Inline scripts support: auto-add nonce to `<script>` tags lacking one, and add the nonce to `script-src`.

## Installation

1. Place under `app/code/HenriqueKieckbusch/AutoCSP/`.
2. `bin/magento setup:upgrade`
3. `bin/magento cache:flush`
4. Configure in Admin: Stores > Configuration > Security > Auto CSP.

## Configuration

Navigate to **Stores > Configuration > Security > Auto CSP** in your Magento Admin.

### Available Options

#### Enable Auto CSP
Enable or disable the entire module.

#### Enable Capture Mode
When enabled, the module collects CSP violations and stores them in the database. This forces the site into `report-only` mode to collect violations without blocking them.

**Recommended workflow:**
1. Enable this mode on staging/development
2. Test your site thoroughly (checkout, admin, catalog, etc.)
3. Once CSP violations stop appearing in the browser console, disable this mode
4. Review and approve collected policies in the admin panel

#### Override CSP Mode
Controls whether this module overrides Magento's native CSP configuration.

- **No (Default):** Respects Magento's native per-scope CSP settings. This allows you to configure different CSP modes for different areas (e.g., strict enforcement on checkout, report-only on catalog pages).
- **Yes:** Module takes control of CSP mode site-wide based on the "Enforced Mode" setting below.

**When to use:**
- Keep **disabled** if you want Magento's native CSP behavior (strict on checkout only, per PCI requirements)
- Enable if you need strict CSP enforcement across your entire site

#### Enforced Mode
Only applies when "Override CSP Mode" is enabled.

- **Yes (Default):** Force strict CSP enforcement on all pages (violations are blocked)
- **No:** Force report-only mode on all pages (violations are logged but not blocked)

#### Enable Auto Inline Script CSP
Automatically adds `nonce` attributes to inline `<script>` tags that don't have one, and includes the nonce in the `script-src` CSP directive.

**Recommended:** Yes

### CSP Mode Priority

The module uses a 3-tier priority system:

1. **Priority 1 (Highest): Capture Mode** - When enabled, always uses `report-only` mode with reporting endpoint
2. **Priority 2: Override Mode** - When enabled (and capture is off), uses configured "Enforced Mode" setting site-wide
3. **Priority 3 (Default): Magento Native** - When both above are disabled, respects Magento's native per-scope CSP configuration

## Usage

### Basic Workflow

1. **Initial Setup:**
   - Enable module
   - Enable capture mode
   - Browse your site, test checkout, admin operations
   - Monitor browser console for CSP violations

2. **Review & Approve:**
   - Check collected policies in Admin panel
   - Approve legitimate resources
   - Remove any unwanted violations

3. **Production Configuration:**
   - Disable capture mode
   - Choose your CSP mode:
     - **Option A (Recommended):** Keep "Override CSP Mode" disabled to use Magento's native behavior
     - **Option B:** Enable "Override CSP Mode" + "Enforced Mode" for site-wide strict CSP

### Use Cases

#### Use Case 1: PCI-Compliant Checkout Only (Recommended)
```
Enable Auto CSP: Yes
Enable Capture Mode: No (after initial testing)
Override CSP Mode: No
Enable Auto Inline Script CSP: Yes
```
Result: Strict CSP on checkout/admin, report-only on catalog (Magento's native behavior)

#### Use Case 2: Site-Wide Strict CSP
```
Enable Auto CSP: Yes
Enable Capture Mode: No (after initial testing)
Override CSP Mode: Yes
Enforced Mode: Yes
Enable Auto Inline Script CSP: Yes
```
Result: Strict CSP enforcement across the entire site

#### Use Case 3: Development/Testing
```
Enable Auto CSP: Yes
Enable Capture Mode: Yes
Override CSP Mode: No
Enable Auto Inline Script CSP: Yes
```
Result: Collect violations site-wide without blocking anything
