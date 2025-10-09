import * as React from 'react'
import { Slot } from '@radix-ui/react-slot'
import { cva, type VariantProps } from 'class-variance-authority'

import { cn } from '@/lib/utils'

const buttonVariants = cva(
  'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-[var(--radius-md)] font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-400)] focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 focus-visible:ring-offset-[var(--color-surface-default)]',
  {
    variants: {
      variant: {
        default: 'bg-[var(--color-primary-500)] text-[var(--color-text-inverse)] shadow-[var(--shadow-sm)] hover:bg-[var(--color-primary-600)] focus-visible:bg-[var(--color-primary-600)]',
        secondary: 'bg-[var(--color-secondary-200)] text-[var(--color-text-primary)] hover:bg-[var(--color-secondary-300)] focus-visible:bg-[var(--color-secondary-300)]',
        outline: 'border border-[var(--color-border-strong)] bg-transparent text-[var(--color-text-primary)] hover:bg-[var(--color-surface-subtle)] focus-visible:bg-[var(--color-surface-subtle)]',
        ghost: 'bg-transparent text-[var(--color-text-primary)] hover:bg-[var(--color-surface-subtle)] focus-visible:bg-[var(--color-surface-subtle)]',
        destructive: 'bg-[var(--color-danger-500)] text-[var(--color-text-inverse)] hover:bg-[#b43737] focus-visible:bg-[#b43737]',
        success: 'bg-[var(--color-success-500)] text-[var(--color-text-inverse)] hover:bg-[#168454] focus-visible:bg-[#168454]',
        link: 'bg-transparent text-[var(--color-primary-500)] underline-offset-4 hover:underline focus-visible:underline',
        cta: 'bg-[var(--color-secondary-500)] text-[var(--color-surface-default)] shadow-[var(--shadow-md)] hover:bg-[var(--color-secondary-600)] focus-visible:bg-[var(--color-secondary-600)]',
        subtle: 'bg-[var(--color-surface-subtle)] text-[var(--color-text-primary)] hover:bg-[var(--color-neutral-200)] focus-visible:bg-[var(--color-neutral-200)]'
      },
      size: {
        default: 'h-10 px-6 text-[var(--font-size-base)]',
        sm: 'h-9 px-4 text-[var(--font-size-sm)] rounded-[var(--radius-sm)]',
        lg: 'h-12 px-8 text-[var(--font-size-lg)] rounded-[var(--radius-lg)]',
        icon: 'h-10 w-10 p-0 justify-center'
      },
      emphasis: {
        solid: 'shadow-[var(--shadow-sm)]',
        minimal: 'shadow-none border border-transparent',
        outline: 'shadow-none'
      },
      loading: {
        true: 'cursor-wait',
        false: ''
      }
    },
    defaultVariants: {
      variant: 'default',
      size: 'default',
      emphasis: 'solid',
      loading: false
    }
  }
)

export interface ButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  asChild?: boolean
  isLoading?: boolean
  leftIcon?: React.ReactNode
  rightIcon?: React.ReactNode
}

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  (
    {
      className,
      variant,
      size,
      emphasis,
      loading,
      isLoading,
      leftIcon,
      rightIcon,
      asChild = false,
      children,
      disabled,
      ...props
    },
    ref
  ) => {
    const Comp = asChild ? Slot : 'button'
    const isButtonLoading = isLoading ?? loading ?? false
    const content = (
      <span className="flex items-center justify-center gap-2">
        {(isButtonLoading || leftIcon) && (
          <span className="flex h-4 w-4 items-center justify-center">
            {isButtonLoading ? (
              <span className="h-4 w-4 animate-spin rounded-full border-2 border-[var(--color-text-inverse)] border-t-transparent" />
            ) : (
              leftIcon
            )}
          </span>
        )}
        <span className="inline-flex items-center">{children}</span>
        {rightIcon && !isButtonLoading && (
          <span className="flex h-4 w-4 items-center justify-center">{rightIcon}</span>
        )}
      </span>
    )

    return (
      <Comp
        className={cn(
          buttonVariants({ variant, size, emphasis, loading: isButtonLoading, className })
        )}
        aria-busy={isButtonLoading || undefined}
        disabled={disabled || isButtonLoading}
        ref={ref}
        {...props}
      >
        {content}
      </Comp>
    )
  }
)
Button.displayName = "Button"
