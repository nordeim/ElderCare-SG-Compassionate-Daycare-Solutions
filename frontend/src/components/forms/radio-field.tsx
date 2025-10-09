import * as React from 'react'

import { RadioGroup, Radio, RadioGroupProps, RadioProps } from '@/components/ui/radio'
import { FormField, FormFieldProps } from '@/components/forms/form-field'

export interface RadioOption {
  label: string
  value: string
  description?: string
}

export interface RadioFieldProps extends Omit<FormFieldProps, 'children'> {
  options: RadioOption[]
  radioGroupProps?: Omit<RadioGroupProps, 'children'>
  radioProps?: Omit<RadioProps, 'value'>
}

export const RadioField = React.forwardRef<HTMLDivElement, RadioFieldProps>(
  (
    {
      label,
      description,
      helperText,
      errorMessage,
      required,
      options,
      radioGroupProps,
      radioProps,
      ...props
    },
    ref
  ) => {
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
        <RadioGroup {...radioGroupProps}>
          {options.map((option) => (
            <div key={option.value} className="flex items-start gap-3">
              <Radio value={option.value} visuallyHiddenLabel={option.label} {...radioProps} />
              <div className="space-y-1">
                <span className="text-[var(--color-text-primary)] text-[var(--font-size-base)]">
                  {option.label}
                </span>
                {option.description ? (
                  <span className="block text-[var(--font-size-sm)] text-[var(--color-text-secondary)]">
                    {option.description}
                  </span>
                ) : null}
              </div>
            </div>
          ))}
        </RadioGroup>
      </FormField>
    )
  }
)

RadioField.displayName = 'RadioField'
