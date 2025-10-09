import type { Meta, StoryObj } from '@storybook/react'
import { ArrowRight, Loader2 } from 'lucide-react'

import { Button } from '@/components/ui/button'

const copyByLocale = {
  en: {
    primary: 'Book a centre tour',
    secondary: 'Explore services',
    loading: 'Processing request'
  },
  zh: {
    primary: '预约参观中心',
    secondary: '探索服务',
    loading: '处理中'
  },
  ms: {
    primary: 'Tempah lawatan pusat',
    secondary: 'Teroka perkhidmatan',
    loading: 'Sedang diproses'
  },
  ta: {
    primary: 'மையத்தை பார்வையிட முன்பதிவு',
    secondary: 'சேவைகளை ஆராயுங்கள்',
    loading: 'செயலாக்கப்படுகிறது'
  }
} satisfies Record<string, { primary: string; secondary: string; loading: string }>

type Locale = keyof typeof copyByLocale

const resolveCopy = (locale: string | undefined): (typeof copyByLocale)[Locale] =>
  copyByLocale[(locale as Locale) ?? 'en'] ?? copyByLocale.en

const meta = {
  title: 'Atoms/Button',
  component: Button,
  argTypes: {
    onClick: { action: 'click' }
  },
  parameters: {
    controls: { exclude: ['leftIcon', 'rightIcon'] }
  }
} satisfies Meta<typeof Button>

export default meta

type Story = StoryObj<typeof meta>

export const Playground: Story = {
  args: {
    children: copyByLocale.en.primary
  } as Story['args'],
  render: (args, { globals }) => {
    const copy = resolveCopy(globals.locale as string)

    return <Button {...args}>{copy.primary}</Button>
  }
}

export const WithIcons: Story = {
  args: {
    variant: 'secondary',
    rightIcon: <ArrowRight className="h-4 w-4" />
  },
  render: (args, { globals }) => {
    const copy = resolveCopy(globals.locale as string)

    return <Button {...args}>{copy.secondary}</Button>
  }
}

export const Loading: Story = {
  args: {
    isLoading: true,
    leftIcon: <Loader2 className="h-4 w-4 animate-spin" />
  },
  render: (args, { globals }) => {
    const copy = resolveCopy(globals.locale as string)

    return <Button {...args}>{copy.loading}</Button>
  }
}

export const ThemeSampler: Story = {
  name: 'Theme sampler',
  parameters: {
    backgrounds: {
      default: 'surface',
      values: [
        { name: 'surface', value: 'var(--color-surface-default)' },
        { name: 'inverse', value: 'var(--color-surface-inverse)' }
      ]
    }
  },
  render: (_args, { globals }) => {
    const copy = resolveCopy(globals.locale as string)

    return (
      <div className="flex flex-col gap-4 sm:flex-row">
        <Button>{copy.primary}</Button>
        <Button variant="secondary">{copy.secondary}</Button>
        <Button variant="outline">{copy.secondary}</Button>
        <Button variant="ghost">{copy.secondary}</Button>
      </div>
    )
  }
}
