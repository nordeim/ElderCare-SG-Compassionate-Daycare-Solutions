import * as React from 'react'

import { Label } from '@/components/ui/label'
import { cn } from '@/lib/utils'

type FormFieldChild = React.ReactElement<{
  id?: string
  required?: boolean
  'aria-describedby'?: string
  'aria-invalid'?: boolean | 'true' | 'false'
}>

export interface FormFieldProps extends React.HTMLAttributes<HTMLDivElement> {
  id?: string
  label?: string
  helperText?: string
  description?: string
  errorMessage?: string
  required?: boolean
  children: FormFieldChild
}

function mergeIds(...ids: (string | undefined)[]) {
  return ids.filter(Boolean).join(' ') || undefined
}

export const FormField = React.forwardRef<HTMLDivElement, FormFieldProps>(
  (
    {
      id,
      label,
      helperText,
      description,
      errorMessage,
      required = false,
      children,
      className,
      ...props
    },
    ref
  ) => {
    const autoId = React.useId()
    const fieldId = id ?? children.props.id ?? `field-${autoId}`
    const descriptionId = description ? `${fieldId}-description` : undefined
    const errorId = errorMessage ? `${fieldId}-error` : undefined

    const childProps = {
      id: fieldId,
      required: required ?? children.props.required,
      'aria-describedby': mergeIds(children.props['aria-describedby'], descriptionId, errorId),
      'aria-invalid': errorMessage ? true : children.props['aria-invalid']
    }

    return (
      <div ref={ref} className={cn('space-y-2', className)} {...props}>
        {label ? (
          <Label htmlFor={fieldId} helperText={helperText} requiredMarker={required}>
            {label}
          </Label>
        ) : null}

        {description ? (
          <p id={descriptionId} className="text-[var(--font-size-sm)] text-[var(--color-text-secondary)]">
            {description}
          </p>
        ) : null}

        {React.cloneElement(children, childProps)}

        {errorMessage ? (
          <p id={errorId} className="text-[var(--font-size-sm)] text-[var(--color-danger-500)]">
            {errorMessage}
          </p>
        ) : null}
      </div>
    )
  }
)

FormField.displayName = 'FormField'
