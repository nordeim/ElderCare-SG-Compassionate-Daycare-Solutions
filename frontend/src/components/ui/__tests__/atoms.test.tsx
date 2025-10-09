import React from 'react'
import { describe, expect, it } from '@jest/globals'
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

describe('UI atoms', () => {
  it('disables Button and shows spinner when isLoading', () => {
    render(<Button isLoading>Processing</Button>)

    const button = screen.getByRole('button', { name: /processing/i })

    expect(button.hasAttribute('disabled')).toBe(true)
    expect(button.getAttribute('aria-busy')).toBe('true')
  })

  it('applies aria-invalid on Input when isInvalid', () => {
    render(<Input aria-label="Email" isInvalid />)

    const input = screen.getByRole('textbox', { name: /email/i })

    expect(input.getAttribute('aria-invalid')).toBe('true')
  })

  it('renders helper text when provided on Label', () => {
    render(
      <Label htmlFor="name" helperText="First and last name">
        Full Name
      </Label>
    )

    expect(screen.queryByText(/first and last name/i)).not.toBeNull()
  })

  it('renders indeterminate Checkbox with minus icon', () => {
    render(<Checkbox aria-label="Select all" checked indeterminate />)

    const checkbox = screen.getByRole('checkbox', { name: /select all/i })
    expect((checkbox as HTMLInputElement).checked).toBe(true)
    expect(checkbox.getAttribute('data-state')).toBe('indeterminate')
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

    expect((radio as HTMLInputElement).checked).toBe(true)
  })

  it('toggles state when Toggle is clicked', async () => {
    const user = userEvent.setup()
    render(
      <Toggle aria-label="Mute notifications">
        <span aria-hidden="true">Mute</span>
      </Toggle>
    )

    const toggle = screen.getByRole('button', { name: /mute notifications/i })
    expect(toggle.getAttribute('aria-pressed')).toBe('false')

    await user.click(toggle)

    expect(toggle.getAttribute('aria-pressed')).toBe('true')
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
    expect(description).not.toBeNull()
    expect(error).not.toBeNull()
    expect(input.getAttribute('aria-invalid')).toBe('true')
  })

  it('renders CheckboxField with helper text', () => {
    render(
      <CheckboxField
        label="Enable reminders"
        helperText="We will notify you before appointments"
        checkboxProps={{ defaultChecked: true }}
      />
    )

    expect((screen.getByRole('checkbox', { name: /enable reminders/i }) as HTMLInputElement).checked).toBe(true)
    expect(screen.queryByText(/notify you before appointments/i)).not.toBeNull()
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
    expect(radios.length).toBe(2)
    expect((radios[1] as HTMLInputElement).checked).toBe(true)
  })
})
