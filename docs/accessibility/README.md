# ElderCare SG Accessibility Guide

## Overview

This guide outlines the accessibility standards and practices implemented in the ElderCare SG platform to ensure it's usable by everyone, including people with disabilities.

## Standards Compliance

The ElderCare SG platform aims to comply with:

- WCAG 2.1 AA (Web Content Accessibility Guidelines)
- Singapore's IMDA accessibility guidelines
- Section 508 of the Rehabilitation Act

## Key Accessibility Features

### Keyboard Navigation

All interactive elements are fully accessible via keyboard:

- **Tab Order**: Logical and intuitive navigation
- **Focus Indicators**: Clear visible focus states
- **Skip Links**: Quick navigation to main content
- **Keyboard Shortcuts**: Enhanced navigation for power users

### Screen Reader Support

The platform is optimized for screen readers:

- **Semantic HTML**: Proper use of HTML5 elements
- **ARIA Labels**: Descriptive labels for complex elements
- **Alt Text**: Meaningful descriptions for images
- **Heading Structure**: Logical heading hierarchy

### Visual Accessibility

Visual design considerations for accessibility:

- **Color Contrast**: All text meets WCAG AA contrast ratios
- **Text Resizing**: Text remains readable at 200% zoom
- **Color Independence**: Information not conveyed by color alone
- **Focus Indicators**: High contrast focus states

### Motor Accessibility

Features to assist users with motor impairments:

- **Large Click Targets**: Minimum 44×44px touch targets
- **Spacing**: Adequate spacing between interactive elements
- **Error Prevention**: Confirmation for destructive actions
- **Time Limits**: Sufficient time to complete tasks

## Implementation Guidelines

### HTML Structure

Use semantic HTML5 elements:

```html
<header>
  <nav>
    <ul>
      <li><a href="/">Home</a></li>
      <li><a href="/programs">Programs</a></li>
    </ul>
  </nav>
</header>

<main>
  <section>
    <h1>Page Title</h1>
    <p>Page content</p>
  </section>
</main>

<footer>
  <p>Footer content</p>
</footer>
```

### ARIA Usage

Use ARIA attributes to enhance accessibility:

```html
<button aria-expanded="false" aria-controls="menu" id="menu-button">
  Menu
</button>
<ul id="menu" aria-labelledby="menu-button" hidden>
  <li><a href="/">Home</a></li>
  <li><a href="/programs">Programs</a></li>
</ul>
```

### Form Accessibility

Ensure forms are accessible:

```html
<form>
  <fieldset>
    <legend>Contact Information</legend>
    
    <div class="form-group">
      <label for="name">Name</label>
      <input type="text" id="name" name="name" required aria-describedby="name-help">
      <div id="name-help" class="help-text">Please enter your full name</div>
    </div>
    
    <div class="form-group">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required aria-describedby="email-error">
      <div id="email-error" class="error-message" aria-live="polite"></div>
    </div>
    
    <button type="submit">Submit</button>
  </fieldset>
</form>
```

### Image Accessibility

Provide meaningful alt text for images:

```html
<img src="/images/center-activity.jpg" alt="Elderly participants engaging in a group art therapy session">
<img src="/images/logo.png" alt="ElderCare SG logo">
<img src="/images/decoration.png" alt="" role="presentation">
```

## Testing

### Automated Testing

We use automated tools to check for accessibility issues:

- **axe-core**: JavaScript library for accessibility testing
- **Lighthouse**: Performance and accessibility auditing
- **Pa11y**: Automated accessibility testing

### Manual Testing

Manual testing is performed to catch issues that automated tools might miss:

- **Keyboard Navigation**: Testing all functionality with keyboard only
- **Screen Reader Testing**: Using NVDA, VoiceOver, and JAWS
- **Color Contrast**: Verifying contrast ratios with tools
- **Zoom Testing**: Testing at 200% and 400% zoom

### User Testing

We conduct accessibility testing with users with disabilities:

- **Blind and Low Vision Users**: Screen reader usability
- **Motor Impairments**: Keyboard and alternative input methods
- **Cognitive Disabilities**: Simplified navigation and content

## Best Practices

### Content

- **Plain Language**: Use simple, clear language
- **Consistent Navigation**: Predictable navigation patterns
- **Error Messages**: Clear, helpful error messages
- **Instructions**: Provide clear instructions for complex tasks

### Design

- **Consistent Layout**: Predictable page structure
- **White Space**: Adequate spacing for readability
- **Typography**: Clear, readable fonts at appropriate sizes
- **Color**: Sufficient contrast and avoid color-only information

### Development

- **Progressive Enhancement**: Core functionality works without JavaScript
- **Focus Management**: Proper focus handling in dynamic content
- **Error Prevention**: Confirmations for destructive actions
- **Performance**: Fast loading for users with slow connections

## Resources

### Guidelines and Standards

- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [IMDA Accessibility Guidelines](https://www.imda.gov.sg/infocomm-media-landscape/digital-inclusion/accessibility)
- [ARIA Authoring Practices](https://www.w3.org/TR/wai-aria-practices-1.1/)

### Testing Tools

- [axe DevTools](https://www.deque.com/axe/devtools/)
- [WAVE Web Accessibility Evaluation Tool](https://wave.webaim.org/)
- [Colour Contrast Analyser](https://www.tpgi.com/color-contrast-checker/)
- [Screen Reader Simulators](https://webaim.org/articles/screenreader_simulator/)

### Learning Resources

- [WebAIM: Web Accessibility In Mind](https://webaim.org/)
- [A11y Project](https://www.a11yproject.com/)
- [Inclusive Design Principles](https://inclusivedesignprinciples.org/)
