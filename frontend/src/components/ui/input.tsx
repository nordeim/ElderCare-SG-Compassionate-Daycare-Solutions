import * as React from 'react'

import { cn } from '@/lib/utils'

export interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  startAdornment?: React.ReactNode
  endAdornment?: React.ReactNode
  isInvalid?: boolean
}

const Input = React.forwardRef<HTMLInputElement, InputProps>(
  ({ className, type = 'text', startAdornment, endAdornment, isInvalid = false, ...props }, ref) => {
    const inputClasses = cn(
      'flex h-10 w-full rounded-[var(--radius-md)] border border-[var(--color-border-default)] bg-[var(--color-surface-default)] px-3 py-2 text-[var(--font-size-base)] text-[var(--color-text-primary)] placeholder:text-[var(--color-text-secondary)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-400)] focus-visible:ring-offset-0 transition-colors disabled:cursor-not-allowed disabled:opacity-60',
      {
        'border-[var(--color-danger-500)] focus-visible:ring-[var(--color-danger-500)]': isInvalid
      },
      className
    )

    return (
      <div className="relative flex items-center">
        {startAdornment && (
          <span className="pointer-events-none absolute inset-y-0 left-3 flex items-center text-[var(--color-text-secondary)]">
            {startAdornment}
          </span>
        )}
        <input
          type={type}
          className={cn(inputClasses, {
            'pl-9': Boolean(startAdornment)
          })}
          ref={ref}
          aria-invalid={isInvalid || undefined}
          {...props}
        />
        {endAdornment && (
          <span className="absolute inset-y-0 right-3 flex items-center text-[var(--color-text-secondary)]">
            {endAdornment}
          </span>
        )}
      </div>
    )
  }
)

Input.displayName = 'Input'

export { Input }
