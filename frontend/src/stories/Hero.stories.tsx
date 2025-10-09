import type { Meta, StoryObj } from '@storybook/react'
import React from 'react'

import Hero from '@/components/sections/hero'

const locales = ['en', 'zh', 'ms', 'ta'] as const

type Locale = (typeof locales)[number]

type HeroCopy = {
  title: string
  subtitle: string
  primary: string
  video: string
}

const copyByLocale: Record<Locale, HeroCopy> = {
  en: {
    title: 'Day care designed around every family',
    subtitle: 'We combine clinical expertise with meaningful activities so seniors thrive with purpose and joy.',
    primary: 'Book a centre tour',
    video: 'Watch virtual tour'
  },
  zh: {
    title: '贴心日间照护，为每个家庭量身打造',
    subtitle: '我们将专业护理与有意义的活动结合，让长者保持活力与笑容。',
    primary: '预约参观中心',
    video: '观看虚拟导览'
  },
  ms: {
    title: 'Penjagaan harian yang dibina untuk setiap keluarga',
    subtitle: 'Kami menggabungkan kepakaran klinikal dengan aktiviti bermakna supaya warga emas terus cergas dan ceria.',
    primary: 'Tempah lawatan pusat',
    video: 'Lihat lawatan maya'
  },
  ta: {
    title: 'ஒவ்வொரு குடும்பத்திற்கும் அமைந்த பகல் பராமரிப்பு',
    subtitle: 'மருத்துவ நிபுணத்துவத்தையும் அர்த்தமுள்ள செயல்களையும் இணைத்து மூத்தவர்கள் மகிழ்ச்சியுடன் வாழ செய்கிறோம்.',
    primary: 'மைய சுற்றுப்பயணத்தை முன்பதிவு செய்யவும்',
    video: 'மெய்நிகர் சுற்றுப்பயணத்தை பார்க்கவும்'
  }
}

const isLocale = (value: unknown): value is Locale => typeof value === 'string' && locales.includes(value as Locale)

const resolveCopy = (locale: string | undefined) => (isLocale(locale) ? copyByLocale[locale] : copyByLocale.en)

const meta = {
  title: 'Sections/Hero',
  component: Hero,
  parameters: {
    layout: 'fullscreen'
  },
  tags: ['autodocs'],
  args: {
    title: copyByLocale.en.title,
    subtitle: copyByLocale.en.subtitle,
    cta: {
      primary: copyByLocale.en.primary,
      video: copyByLocale.en.video
    }
  }
} satisfies Meta<typeof Hero>

export default meta

type Story = StoryObj<typeof meta>

export const Playground: Story = {
  render: (args, { globals }) => {
    const copy = resolveCopy(globals.locale as string)

    return (
      <Hero
        {...args}
        title={copy.title}
        subtitle={copy.subtitle}
        cta={{ primary: copy.primary, video: copy.video }}
      />
    )
  }
}
