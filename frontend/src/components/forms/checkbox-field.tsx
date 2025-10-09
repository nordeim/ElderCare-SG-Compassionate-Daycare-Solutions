import * as React from 'react'

import { Checkbox, CheckboxProps } from '@/components/ui/checkbox'
import { FormField, FormFieldProps } from '@/components/forms/form-field'

export interface CheckboxFieldProps extends Omit<FormFieldProps, 'children'> {
  checkboxProps?: Omit<CheckboxProps, 'id'>
  helperText?: string
}

export const CheckboxField = React.forwardRef<HTMLDivElement, CheckboxFieldProps>(
  ({ label, description, helperText, errorMessage, required, checkboxProps, ...props }, ref) => {
    return (
      <FormField
        ref={ref}
        label={label}
        description={description}
        helperText={helperText}
        errorMessage={errorMessage}
        required={required}
        {...props}
      >
        <Checkbox {...checkboxProps} />
      </FormField>
    )
  }
)

CheckboxField.displayName = 'CheckboxField'
