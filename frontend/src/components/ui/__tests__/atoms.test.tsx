/// <reference types="@testing-library/jest-dom" />

import React from 'react'
import { describe, expect, it } from '@jest/globals'
import '@testing-library/jest-dom'
import matchers from '@testing-library/jest-dom/matchers'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { RadioGroup, Radio } from '@/components/ui/radio'
import { Toggle } from '@/components/ui/toggle'
import { FormField } from '@/components/forms/form-field'
import { CheckboxField } from '@/components/forms/checkbox-field'
import { RadioField } from '@/components/forms/radio-field'

expect.extend(matchers)

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

  it('links FormField labels and descriptions correctly', () => {
    render(
      <FormField
        label="Care recipient"
        description="Select the individual you are managing"
        errorMessage="Please make a selection"
      >
        <Input />
      </FormField>
    )

    const input = screen.getByRole('textbox', { name: /care recipient/i })
    const description = screen.getByText(/select the individual/i)
    const error = screen.getByText(/please make a selection/i)

    const describedBy = (input.getAttribute('aria-describedby') ?? '').split(' ').filter(Boolean)

    expect(describedBy).toHaveLength(2)
    describedBy.forEach((id) => {
      expect(document.getElementById(id)).not.toBeNull()
    })
    expect(input).toHaveAttribute('aria-invalid', 'true')
  })

  it('renders CheckboxField with helper text', () => {
    render(
      <CheckboxField
        label="Enable reminders"
        helperText="We will notify you before appointments"
        checkboxProps={{ defaultChecked: true }}
      />
    )

    expect(screen.getByRole('checkbox', { name: /enable reminders/i })).toBeChecked()
    expect(screen.getByText(/notify you before appointments/i)).toBeInTheDocument()
  })

  it('renders RadioField and selects default option', () => {
    render(
      <RadioField
        label="Preferred centre"
        options={[
          { label: 'Central Clinic', value: 'central' },
          { label: 'East Point', value: 'east' }
        ]}
        radioGroupProps={{ defaultValue: 'east' }}
      />
    )

    const radios = screen.getAllByRole('radio')
    expect(radios).toHaveLength(2)
    expect(radios[1]).toBeChecked()
  })
})
