import type { Meta, StoryObj } from '@storybook/react'

import { RadioGroup, Radio } from '@/components/ui/radio'
import { RadioField } from '@/components/forms/radio-field'

const meta = {
  title: 'Atoms/Radio',
  component: Radio,
  args: {
    value: 'option'
  }
} satisfies Meta<typeof Radio>

export default meta

export const Playground: StoryObj<typeof Radio> = {
  render: (args) => (
    <RadioGroup defaultValue="option">
      <Radio {...args} value="option" />
    </RadioGroup>
  )
}

export const WithField: StoryObj<typeof RadioField> = {
  render: (args) => <RadioField {...args} />,
  args: {
    label: 'Select your plan',
    options: [
      { label: 'Basic', value: 'basic', description: 'Core services' },
      { label: 'Premium', value: 'premium', description: 'Includes wellness add-ons' }
    ],
    radioGroupProps: {
      defaultValue: 'premium'
    }
  }
}
