import type { Meta, StoryObj } from '@storybook/react'
import { Volume2, VolumeX } from 'lucide-react'

import { Toggle } from '@/components/ui/toggle'

const meta = {
  title: 'Atoms/Toggle',
  component: Toggle,
  args: {
    'aria-label': 'Mute notifications'
  }
} satisfies Meta<typeof Toggle>

export default meta

export const Playground: StoryObj<typeof Toggle> = {
  args: {
    children: 'Notifications'
  }
}

export const WithIcons: StoryObj<typeof Toggle> = {
  args: {
    pressedIcon: <VolumeX className="h-4 w-4" />,
    unpressedIcon: <Volume2 className="h-4 w-4" />
  }
}
