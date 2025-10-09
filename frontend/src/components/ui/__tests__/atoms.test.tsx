import React from 'react'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { RadioGroup, Radio } from '@/components/ui/radio'
import { Toggle } from '@/components/ui/toggle'

describe('UI atoms', () => {
  it('disables Button and shows spinner when isLoading', () => {
    render(<Button isLoading>Processing</Button>)

    const button = screen.getByRole('button', { name: /processing/i })

    expect(button).toBeDisabled()
    expect(button).toHaveAttribute('aria-busy', 'true')
  })

  it('applies aria-invalid on Input when isInvalid', () => {
    render(<Input aria-label="Email" isInvalid />)

    const input = screen.getByRole('textbox', { name: /email/i })

    expect(input).toHaveAttribute('aria-invalid', 'true')
  })

  it('renders helper text when provided on Label', () => {
    render(
      <Label htmlFor="name" helperText="First and last name">
        Full Name
      </Label>
    )

    expect(screen.getByText(/first and last name/i)).toBeInTheDocument()
  })

  it('renders indeterminate Checkbox with minus icon', () => {
    render(<Checkbox aria-label="Select all" checked indeterminate />)

    const checkbox = screen.getByRole('checkbox', { name: /select all/i })
    expect(checkbox).toBeChecked()
    expect(checkbox).toHaveAttribute('data-state', 'indeterminate')
  })

  it('selects Radio via user interaction', async () => {
    const user = userEvent.setup()
    render(
      <RadioGroup aria-label="Plan">
        <Radio value="basic" />
        <Radio value="pro" />
      </RadioGroup>
    )

    const radio = screen.getAllByRole('radio')[1]
    await user.click(radio)

    expect(radio).toBeChecked()
  })

  it('toggles state when Toggle is clicked', async () => {
    const user = userEvent.setup()
    render(
      <Toggle aria-label="Mute notifications">
        <span aria-hidden="true">Mute</span>
      </Toggle>
    )

    const toggle = screen.getByRole('button', { name: /mute notifications/i })
    expect(toggle).toHaveAttribute('aria-pressed', 'false')

    await user.click(toggle)

    expect(toggle).toHaveAttribute('aria-pressed', 'true')
  })
})
