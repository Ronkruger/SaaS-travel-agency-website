# DiscoverGroup Brand Fonts

Place the following font files in this directory to activate the complete brand typography system.

## Required Files

### LEMON MILK (Brand / Logo Font)
- `LemonMilk.woff2`         — weight 400
- `LemonMilk.woff`          — weight 400 (fallback)
- `LemonMilk-Bold.woff2`    — weight 700–900
- `LemonMilk-Bold.woff`     — weight 700–900 (fallback)
Source: Free for personal use · search "Lemon Milk font" on dafont.com or similar

### Blacksword (Script Accent — Tour / Route Names)
- `Blacksword.woff2`
- `Blacksword.woff`
Source: Commercial license required · blacksword.com or myfonts.com

### Futura (Body Copy / Descriptions)
- `Futura-Book.woff2`        — weight 400
- `Futura-Book.woff`
- `Futura-Medium.woff2`      — weight 500–600
- `Futura-Medium.woff`
- `Futura-Bold.woff2`        — weight 700–900
- `Futura-Bold.woff`
Source: Commercial license required (Bauer Foundry / Linotype)
Free alternative: use PT Sans or Nunito (already set as fallback in CSS)

## Usage in CSS
The @font-face declarations in public/css/styles.css reference these files via:
  `url('../fonts/Filename.woff2')`

Until the files are added, the site falls back to:
- Poppins (Google Fonts, loaded automatically) for headings and brand elements
- Dancing Script (Google Fonts, loaded automatically) for Blacksword labels
- Poppins for body copy
