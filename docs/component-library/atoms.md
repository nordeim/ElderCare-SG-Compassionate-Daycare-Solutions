# Component Library — Atom Reference

_Last updated: 2025-10-09 12:15 SGT_

## Button
- **File:** `frontend/src/components/ui/button.tsx`
- **Variants:** `default`, `secondary`, `outline`, `ghost`, `destructive`, `success`, `link`, `cta`, `subtle`
- **Sizes:** `sm`, `default`, `lg`, `icon`
- **Props:**
  - `isLoading` disables the button and renders a spinner.
  - `leftIcon`/`rightIcon` slots support Lucide icons.
  - `emphasis` toggles tone (`solid`, `minimal`, `outline`).
- **Design Tokens:** Utilises CSS variables from `design-tokens.css` for color, spacing, typography, focus rings, and shadows.

## Input
- **File:** `frontend/src/components/ui/input.tsx`
- **Props:** `startAdornment`, `endAdornment`, `isInvalid`
- **Accessibility:**
  - Applies `aria-invalid` when `isInvalid` is true.
  - Maintains focus ring with `focus-visible` styles.
- **Tokens:** Border, background, typography, and spacing align with neutral palette tokens.

## Label
- **File:** `frontend/src/components/ui/label.tsx`
- **Props:** `requiredMarker`, `helperText`, `size` (`sm | md`)
- **Usage:** Pair with `Input`, `Checkbox`, `Radio`, `Toggle`. Helper text renders below label using secondary text color.

## Checkbox
- **File:** `frontend/src/components/ui/checkbox.tsx`
- **Underlying Primitive:** `@radix-ui/react-checkbox`
- **Props:** `indeterminate`, `error`
- **States:** Indeterminate renders `Minus` icon; error state updates border and focus ring to `--color-danger-500`.

## Radio & RadioGroup
- **File:** `frontend/src/components/ui/radio.tsx`
- **Underlying Primitive:** `@radix-ui/react-radio-group`
- **Props:**
  - `RadioGroup` orientation (`horizontal` | `vertical`)
  - `Radio` `error` state
- **States:** Selected indicator fills with primary color token.

## Toggle
- **File:** `frontend/src/components/ui/toggle.tsx`
- **Underlying Primitive:** `@radix-ui/react-toggle`
- **Props:** `pressedIcon`, `unpressedIcon`
- **States:** Uses `data-state="on"` to switch between pressed/unpressed visuals with tokenized colors.

## Testing Strategy
- Initial smoke tests placed in `frontend/src/components/ui/__tests__/atoms.test.tsx`.
- Tests cover rendering, accessible attributes (`aria-invalid`, `role`, `data-state`), and key state changes (`isLoading`, indeterminate checkbox).

## Next Steps
- Expand Storybook coverage with controls for variant switching.
- Integrate atoms into `FormField` molecule and document compound usage patterns.
