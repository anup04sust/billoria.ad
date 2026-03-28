# Billoria.ad Color Palette

This document describes the color system used in the Billoria.ad frontend application.

## Color Palette

### Primary Colors
- **Primary Red**: `#C1121F` - Main brand color for CTAs, links, and primary actions
- **Dark Maroon**: `#780000` - Hover states and emphasis

### Secondary Colors
- **Dark Blue**: `#003049` - Secondary actions, headers, and navigation
- **Light Blue**: `#669BBC` - Info messages, subtle accents, and secondary elements

### Accent Colors
- **Cream**: `#FDF0D5` - Backgrounds for highlighted sections, cards, and featured content

## Usage Guidelines

### Backgrounds
```css
/* Primary background - use for hero sections, featured areas */
background-color: var(--color-cream);

/* Dark background - use for footers, contrast sections */
background-color: var(--color-secondary);
```

### Buttons
```html
<!-- Primary Action Button -->
<button class="btn-primary">Book Now</button>

<!-- Secondary Action Button -->
<button class="btn-secondary">Learn More</button>

<!-- Outline Button -->
<button class="btn-outline-primary">View Details</button>
```

### Text Colors
```css
/* Primary text for emphasis */
color: var(--color-primary);

/* Secondary text for less important content */
color: var(--color-secondary-light);

/* Dark text for headers */
color: var(--color-secondary);
```

### Cards & Sections
```html
<!-- Light card with cream background -->
<div class="card-cream">
  <h3>Featured Billboard</h3>
  <p>Premium location in Dhaka</p>
</div>

<!-- Dark card with secondary background -->
<div class="card-dark">
  <h3>Contact Us</h3>
  <p>Get in touch with our team</p>
</div>
```

## CSS Custom Properties

All colors are available as CSS custom properties:

```css
--color-primary: #C1121F;
--color-primary-hover: #780000;
--color-primary-dark: #780000;
--color-secondary: #003049;
--color-secondary-light: #669BBC;
--color-accent: #C1121F;
--color-cream: #FDF0D5;
```

## Tailwind Classes

You can also use Tailwind's arbitrary value syntax:

```html
<div class="bg-[#C1121F] text-white">
  Red background with white text
</div>

<div class="bg-[#FDF0D5] text-[#003049]">
  Cream background with dark blue text
</div>
```

## Color Combinations

### Recommended Pairings

1. **High Contrast (For CTAs)**
   - Primary Red (#C1121F) on White
   - White on Dark Blue (#003049)

2. **Soft & Inviting (For Info Sections)**
   - Dark Blue (#003049) on Cream (#FDF0D5)
   - Light Blue (#669BBC) on White

3. **Dramatic (For Hero Sections)**
   - White on Dark Maroon (#780000)
   - Cream (#FDF0D5) on Dark Blue (#003049)

## Accessibility

- Ensure text has sufficient contrast (WCAG AA: 4.5:1 for normal text)
- Primary Red (#C1121F) on white: ✓ Passes
- Dark Blue (#003049) on cream (#FDF0D5): ✓ Passes
- Light Blue (#669BBC) on white: ⚠️ Use for larger text or increase weight

## Example Components

### Hero Section
```html
<section class="bg-[#003049] text-white py-20">
  <div class="container">
    <h1 class="text-5xl font-bold mb-4">Find Your Perfect Billboard</h1>
    <p class="text-[#FDF0D5] text-xl mb-8">Premium locations across Bangladesh</p>
    <button class="btn-primary">Get Started</button>
  </div>
</section>
```

### Feature Card
```html
<div class="card-cream">
  <div class="inline-block p-3 bg-[#C1121F] rounded-lg mb-4">
    <svg class="w-6 h-6 text-white"><!-- icon --></svg>
  </div>
  <h3 class="text-2xl font-semibold text-[#003049] mb-3">Wide Coverage</h3>
  <p class="text-gray-700">Access billboards in all major cities</p>
</div>
```

### Navigation
```html
<nav class="bg-white border-b border-gray-200">
  <div class="container flex items-center justify-between py-4">
    <div class="text-2xl font-bold text-[#C1121F]">Billoria.ad</div>
    <button class="btn-primary">Sign In</button>
  </div>
</nav>
```
