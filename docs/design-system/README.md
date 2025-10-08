# ElderCare SG Design System

## Overview

This design system provides guidelines and components for the ElderCare SG platform, ensuring consistency, accessibility, and a user-friendly experience.

## Brand Identity

### Logo

The ElderCare SG logo represents care, compassion, and community. It should be used consistently across all materials.

### Color Palette

| Color | Hex Code | Usage |
|-------|----------|-------|
| Deep Blue | #1C3D5A | Primary brand color, headers, important CTAs |
| Off-White | #F7F9FC | Main background |
| Gold | #F0A500 | Highlights, accents, primary CTAs |
| Soft Amber | #FCDFA6 | Card backgrounds, secondary highlights |
| Calming Green | #3D9A74 | Success states, wellness features |
| Slate Gray 1 | #334155 | Primary text |
| Slate Gray 2 | #64748B | Secondary text |

### Typography

#### Fonts

- **Headings**: Playfair Display
- **Body Text**: Inter

#### Typography Scale

| Style | Font | Weight | Size | Line Height |
|-------|------|--------|------|-------------|
| H1 | Playfair Display | 700 | 40-64px | 1.25em |
| H2 | Playfair Display | 600 | 32-48px | 1.25em |
| H3 | Playfair Display | 600 | 24-32px | 1.25em |
| Body | Inter | 400 | 16-18px | 1.5em |
| Small | Inter | 400 | 14px | 1.5em |

## Components

### Buttons

#### Primary Button
Used for primary actions and CTAs.

```jsx
<Button variant="default">Button Text</Button>
```

#### Secondary Button
Used for secondary actions.

```jsx
<Button variant="secondary">Button Text</Button>
```

#### CTA Button
Used for important call-to-action elements.

```jsx
<Button variant="cta">Button Text</Button>
```

### Cards

Used to group related content and actions.

```jsx
<Card>
  <CardHeader>
    <CardTitle>Card Title</CardTitle>
    <CardDescription>Card Description</CardDescription>
  </CardHeader>
  <CardContent>
    Card content goes here
  </CardContent>
  <CardFooter>
    <Button>Action</Button>
  </CardFooter>
</Card>
```

## Layout

### Grid System

- **Container**: Max width of 1280px with 24px gutters
- **Columns**: 12-column responsive grid
- **Breakpoints**:
  - Mobile: < 768px
  - Tablet: 768px - 1023px
  - Desktop: ≥ 1024px

### Spacing

- **Base unit**: 4px
- **Scale**: 4, 8, 12, 16, 20, 24, 32, 40, 48, 64, 80, 96px

## Icons

We use Lucide icons for consistency and clarity. Icons should be used to:

- Enhance meaning
- Improve navigation
- Indicate status
- Represent actions

## Imagery

### Photography

- Focus on authentic, warm interactions
- Diverse representation of elderly individuals
- Bright, natural lighting
- High-resolution images

### Videos

- Professional production quality
- Subtitles for accessibility
- Autoplay with user controls
- Optimized for web (WebM format preferred)

## Accessibility

### Color Contrast

All text and background color combinations must meet WCAG 2.1 AA standards:
- Normal text: 4.5:1
- Large text: 3:1
- Non-text elements: 3:1

### Keyboard Navigation

All interactive elements must be accessible via keyboard:
- Clear focus indicators
- Logical tab order
- Skip navigation links

### Screen Reader Support

- Semantic HTML5 elements
- ARIA labels and roles
- Alt text for images
- Descriptive link text

## Animation

### Principles

- Purposeful and meaningful
- Subtle and natural
- Respectful of user preferences
- Performance optimized

### Guidelines

- Duration: 200-500ms for most animations
- Easing: ease-out for entrance animations
- Reduced motion: Respect prefers-reduced-motion setting

## Voice and Tone

### Principles

- Warm and compassionate
- Clear and simple
- Respectful and dignified
- Professional yet approachable

### Guidelines

- Use simple language
- Avoid jargon
- Be direct and clear
- Show empathy and understanding

## Usage

### Implementation

1. Import components from the design system
2. Use established color and typography tokens
3. Follow layout and spacing guidelines
4. Ensure accessibility standards are met

### Customization

While the design system provides a solid foundation, it's designed to be flexible:

- Extend with additional components as needed
- Adjust spacing within the established scale
- Use color palette for additional UI states
- Follow animation principles for custom animations

## Maintenance

The design system is a living document that evolves with the product:

- Regular reviews and updates
- Component versioning
- Documentation updates
- User feedback incorporation
