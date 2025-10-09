import type { Meta, StoryObj } from '@storybook/react'
import { ArrowRight, Loader2 } from 'lucide-react'

import { Button } from '@/components/ui/button'

const meta = {
  title: 'Atoms/Button',
  component: Button,
  argTypes: {
    onClick: { action: 'click' }
  }
} satisfies Meta<typeof Button>

export default meta

export const Playground: StoryObj<typeof Button> = {
  args: {
    children: 'Book a Visit'
  }
}

export const WithIcons: StoryObj<typeof Button> = {
  args: {
    children: 'Explore Services',
    rightIcon: <ArrowRight className="h-4 w-4" />
  }
}

export const Loading: StoryObj<typeof Button> = {
  args: {
    children: 'Processing',
    isLoading: true,
    leftIcon: <Loader2 className="h-4 w-4 animate-spin" />
  }
}
