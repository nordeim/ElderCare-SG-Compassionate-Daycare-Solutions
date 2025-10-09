import type { Meta, StoryObj } from '@storybook/react'
import { Mail } from 'lucide-react'

import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const meta = {
  title: 'Atoms/Input',
  component: Input,
  decorators: [
    (Story) => (
      <div className="flex max-w-sm flex-col gap-2">
        <Label htmlFor="storybook-input" helperText="We will never share your email.">
          Email address
        </Label>
        <Story />
      </div>
    )
  ]
} satisfies Meta<typeof Input>

export default meta

export const Playground: StoryObj<typeof Input> = {
  args: {
    id: 'storybook-input',
    placeholder: 'you@example.com'
  }
}

export const WithAdornment: StoryObj<typeof Input> = {
  args: {
    id: 'storybook-input-adorned',
    placeholder: 'you@example.com',
    startAdornment: <Mail className="h-4 w-4" />
  }
}

export const Invalid: StoryObj<typeof Input> = {
  args: {
    id: 'storybook-input-invalid',
    placeholder: 'you@example.com',
    isInvalid: true
  }
}
