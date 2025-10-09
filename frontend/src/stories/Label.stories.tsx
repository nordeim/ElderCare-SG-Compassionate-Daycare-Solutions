import type { Meta, StoryObj } from '@storybook/react'

import { Label } from '@/components/ui/label'

const copyByLocale = {
  en: {
    default: 'Primary caregiver contact',
    helper: 'We will reach out between 9am–6pm SGT.'
  },
  zh: {
    default: '主要照护者联络方式',
    helper: '我们会在新加坡时间上午9点至下午6点之间联系。'
  },
  ms: {
    default: 'Hubungan penjaga utama',
    helper: 'Kami akan menghubungi antara 9 pagi–6 petang SGT.'
  },
  ta: {
    default: 'முதன்மை பராமரிப்பாளர் தொடர்பு',
    helper: 'நாங்கள் காலை 9 மணி முதல் மாலை 6 மணி வரை (SGT) தொடர்பு கொள்வோம்.'
  }
} satisfies Record<string, { default: string; helper: string }>

type Locale = keyof typeof copyByLocale

const resolveCopy = (locale: string | undefined) =>
  copyByLocale[(locale as Locale) ?? 'en'] ?? copyByLocale.en

const meta = {
  title: 'Atoms/Label',
  component: Label,
  parameters: {
    layout: 'padded'
  },
  args: {
    size: 'md'
  },
  tags: ['autodocs']
} satisfies Meta<typeof Label>

export default meta

type Story = StoryObj<typeof meta>

export const Playground: Story = {
  render: (args, { globals }) => {
    const copy = resolveCopy(globals.locale as string)

    return (
      <Label {...args} helperText={copy.helper} requiredMarker>
        {copy.default}
      </Label>
    )
  }
}

export const Small: Story = {
  args: {
    size: 'sm'
  },
  render: (args, { globals }) => {
    const copy = resolveCopy(globals.locale as string)

    return (
      <Label {...args} helperText={copy.helper}>
        {copy.default}
      </Label>
    )
  }
}
