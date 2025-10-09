import type { Meta, StoryObj } from '@storybook/react'

import { Checkbox } from '@/components/ui/checkbox'
import { CheckboxField } from '@/components/forms/checkbox-field'

const meta = {
  title: 'Atoms/Checkbox',
  component: Checkbox,
  args: {
    checked: true
  }
} satisfies Meta<typeof Checkbox>

export default meta

export const Playground: StoryObj<typeof Checkbox> = {}

export const WithLabel: StoryObj<typeof CheckboxField> = {
  render: (args) => <CheckboxField {...args} />,
  args: {
    label: 'Subscribe to newsletter',
    helperText: 'Receive program updates and caregiving tips.',
    checkboxProps: {
      defaultChecked: true
    }
  }
}
