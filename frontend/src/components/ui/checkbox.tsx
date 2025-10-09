import * as React from 'react'
import * as CheckboxPrimitive from '@radix-ui/react-checkbox'
import { Check, Minus } from 'lucide-react'

import { cn } from '@/lib/utils'

export interface CheckboxProps extends CheckboxPrimitive.CheckboxProps {
  indeterminate?: boolean
  error?: boolean
}

const Checkbox = React.forwardRef<CheckboxPrimitive.CheckboxElement, CheckboxProps>(
  ({ className, indeterminate = false, error = false, disabled, ...props }, ref) => {
    return (
      <CheckboxPrimitive.Root
        ref={ref}
        className={cn(
          'peer inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-[var(--radius-sm)] border border-[var(--color-border-strong)] bg-[var(--color-surface-default)] text-[var(--color-surface-default)] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-400)] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60',
          error && 'border-[var(--color-danger-500)] focus-visible:ring-[var(--color-danger-500)]',
          className
        )}
        data-state={indeterminate ? 'indeterminate' : undefined}
        disabled={disabled}
        {...props}
      >
        <CheckboxPrimitive.Indicator className="flex h-full w-full items-center justify-center text-current">
          {indeterminate ? <Minus className="h-3.5 w-3.5" /> : <Check className="h-3.5 w-3.5" />}
        </CheckboxPrimitive.Indicator>
      </CheckboxPrimitive.Root>
    )
  }
)

Checkbox.displayName = CheckboxPrimitive.Root.displayName

export { Checkbox }
