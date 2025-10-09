import * as React from 'react'

import { cn } from '@/lib/utils'

export interface LabelProps extends React.LabelHTMLAttributes<HTMLLabelElement> {
  requiredMarker?: boolean
  helperText?: string
  size?: 'sm' | 'md'
}

const Label = React.forwardRef<HTMLLabelElement, LabelProps>(
  ({ className, requiredMarker = false, helperText, size = 'md', children, ...props }, ref) => {
    const labelClasses = cn(
      'block text-[var(--color-text-primary)] font-medium leading-[var(--line-height-normal)]',
      size === 'sm' ? 'text-[var(--font-size-sm)]' : 'text-[var(--font-size-base)]',
      className
    )

    return (
      <label ref={ref} className={labelClasses} {...props}>
        <span className="inline-flex items-center gap-1">
          <span>{children}</span>
          {requiredMarker && (
            <span aria-hidden="true" className="text-[var(--color-danger-500)]">
              *
            </span>
          )}
        </span>
        {helperText ? (
          <span className="mt-1 block text-[var(--font-size-sm)] font-normal text-[var(--color-text-secondary)]">
            {helperText}
          </span>
        ) : null}
      </label>
    )
  }
)

Label.displayName = 'Label'

export { Label }
