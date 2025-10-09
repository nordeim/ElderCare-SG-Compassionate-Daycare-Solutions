import * as React from 'react'
import * as RadioGroupPrimitive from '@radix-ui/react-radio-group'

import { cn } from '@/lib/utils'

export interface RadioGroupProps extends RadioGroupPrimitive.RadioGroupProps {
  orientation?: 'horizontal' | 'vertical'
}

const RadioGroup = React.forwardRef<
  React.ElementRef<typeof RadioGroupPrimitive.Root>,
  RadioGroupProps
>(({ className, orientation = 'vertical', ...props }, ref) => (
  <RadioGroupPrimitive.Root
    ref={ref}
    className={cn(
      'grid gap-3',
      orientation === 'horizontal' ? 'grid-flow-col auto-cols-auto items-center' : 'grid-flow-row',
      className
    )}
    {...props}
  />
))

RadioGroup.displayName = RadioGroupPrimitive.Root.displayName

export interface RadioProps extends RadioGroupPrimitive.RadioGroupItemProps {
  error?: boolean
}

const Radio = React.forwardRef<
  React.ElementRef<typeof RadioGroupPrimitive.Item>,
  RadioProps
>(({ className, error = false, disabled, ...props }, ref) => (
  <RadioGroupPrimitive.Item
    ref={ref}
    className={cn(
      'flex h-5 w-5 items-center justify-center rounded-full border border-[var(--color-border-strong)] bg-[var(--color-surface-default)] text-[var(--color-primary-500)] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-400)] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60',
      error && 'border-[var(--color-danger-500)] focus-visible:ring-[var(--color-danger-500)]',
      className
    )}
    disabled={disabled}
    {...props}
  >
    <RadioGroupPrimitive.Indicator className="relative flex h-2.5 w-2.5 items-center justify-center">
      <span className="absolute inset-0 rounded-full bg-[var(--color-primary-500)]" />
    </RadioGroupPrimitive.Indicator>
  </RadioGroupPrimitive.Item>
))

Radio.displayName = RadioGroupPrimitive.Item.displayName

export { RadioGroup, Radio }
