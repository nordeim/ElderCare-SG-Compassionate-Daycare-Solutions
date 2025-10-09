import React from 'react'
import { describe, expect, it } from '@jest/globals'
import { act, render, screen, waitFor } from '@testing-library/react'
import { axe } from 'jest-axe'
import userEvent from '@testing-library/user-event'

import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { CheckboxField } from '@/components/forms/checkbox-field'
import { RadioField } from '@/components/forms/radio-field'
import { Button } from '@/components/ui/button'

describe('UI molecules', () => {
  it('renders Card with content hierarchy', async () => {
    let renderResult!: ReturnType<typeof render>

    await act(async () => {
      renderResult = render(
        <Card className="max-w-sm" aria-label="Day care membership">
          <CardHeader>
            <CardTitle>Day care membership</CardTitle>
            <CardDescription>Personalised activities and health monitoring.</CardDescription>
          </CardHeader>
          <CardContent>
            <p>Our allied health team keeps loved ones active every weekday.</p>
          </CardContent>
          <CardFooter>
            <Button>Book a centre tour</Button>
          </CardFooter>
        </Card>
      )
    })

    const { container } = renderResult
    expect(screen.getByRole('heading', { name: /day care membership/i })).not.toBeNull()
    expect(screen.getByText(/personalised activities/i)).not.toBeNull()
    expect(screen.getByRole('button', { name: /book a centre tour/i })).not.toBeNull()

    const results = await axe(container)
    expect(results).toHaveNoViolations()
  })

  it('propagates helper and error messaging in CheckboxField', async () => {
    let renderResult!: ReturnType<typeof render>

    await act(async () => {
      renderResult = render(
        <CheckboxField
          label="Fall prevention support"
          helperText="Recommended for residents with previous incidents"
          errorMessage="Please confirm participation"
          required
          checkboxProps={{ 'aria-invalid': true }}
        />
      )
    })

    const { container } = renderResult
    const checkbox = screen.getByRole('checkbox', { name: /fall prevention support/i }) as HTMLInputElement
    expect(checkbox.getAttribute('aria-invalid')).toBe('true')
    expect(screen.getByText(/recommended for residents/i)).not.toBeNull()
    expect(screen.getByText(/please confirm participation/i)).not.toBeNull()
    expect(screen.getByText('*', { selector: 'span[aria-hidden="true"]' })).not.toBeNull()

    const results = await axe(container)
    expect(results).toHaveNoViolations()
  })

  it('selects radio option and exposes descriptions', async () => {
    const user = userEvent.setup()
    let renderResult!: ReturnType<typeof render>

    await act(async () => {
      renderResult = render(
        <RadioField
          label="Preferred care plan"
          helperText="Selections can be changed later"
          options={[
            { label: 'Day care', value: 'day', description: 'Weekday day-time programme.' },
            { label: 'Respite', value: 'respite', description: 'Short-term overnight stays.' }
          ]}
          radioGroupProps={{ defaultValue: 'day' }}
        />
      )
    })

    const { container } = renderResult
    const radios = await screen.findAllByRole('radio')
    expect(radios).toHaveLength(2)
    expect((radios[0] as HTMLElement).getAttribute('data-state')).toBe('checked')

    await user.click(radios[1])
    await waitFor(() => expect(radios[1].getAttribute('data-state')).toBe('checked'))

    expect(screen.getByText(/weekday day-time programme/i)).not.toBeNull()
    expect(screen.getByText(/short-term overnight stays/i)).not.toBeNull()

    const results = await axe(container)
    expect(results).toHaveNoViolations()
  })
})
