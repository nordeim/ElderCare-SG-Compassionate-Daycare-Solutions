import * as React from 'react'
import * as TogglePrimitive from '@radix-ui/react-toggle'

import { cn } from '@/lib/utils'

export interface ToggleProps extends TogglePrimitive.ToggleProps {
  pressedIcon?: React.ReactNode
  unpressedIcon?: React.ReactNode
}

const Toggle = React.forwardRef<
  React.ElementRef<typeof TogglePrimitive.Root>,
  ToggleProps
>(({ className, pressedIcon, unpressedIcon, disabled, ...props }, ref) => (
  <TogglePrimitive.Root
    ref={ref}
    className={cn(
      'inline-flex h-9 min-w-[2.5rem] items-center justify-center gap-2 rounded-[var(--radius-md)] border border-[var(--color-border-default)] bg-[var(--color-surface-default)] px-3 py-2 text-[var(--color-text-primary)] transition-colors data-[state=on]:bg-[var(--color-primary-500)] data-[state=on]:text-[var(--color-text-inverse)] data-[state=on]:border-[var(--color-primary-500)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-400)] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60',
      className
    )}
    disabled={disabled}
    {...props}
  >
    <span className="flex h-4 w-4 items-center justify-center">
      {props['aria-pressed'] || props.pressed ? pressedIcon : unpressedIcon}
    </span>
    <span className="sr-only">Toggle</span>
  </TogglePrimitive.Root>
))

Toggle.displayName = TogglePrimitive.Root.displayName

export { Toggle }
