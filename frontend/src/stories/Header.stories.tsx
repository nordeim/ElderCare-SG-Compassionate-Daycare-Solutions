import type { Meta, StoryObj } from '@storybook/react'
import React from 'react'

import Header from '@/components/layout/header'
import { isLocale, type Locale } from '@/lib/i18n/config'
import { within, userEvent } from '@storybook/test'

const locales = ['en', 'zh', 'ms', 'ta'] as const

type LocaleKey = (typeof locales)[number]

type HeaderCopy = {
  appName: string
  navigation: {
    programs: string
    philosophy: string
    testimonials: string
    contact: string
  }
  actions: {
    bookVisit: string
    languageSwitch: string
  }
}

const copyByLocale: Record<LocaleKey, HeaderCopy> = {
  en: {
    appName: 'ElderCare SG',
    navigation: {
      programs: 'Programmes',
      philosophy: 'Our philosophy',
      testimonials: 'Testimonials',
      contact: 'Contact'
    },
    actions: {
      bookVisit: 'Book a centre tour',
      languageSwitch: 'Language'
    }
  },
  zh: {
    appName: 'ElderCare SG',
    navigation: {
      programs: '服务项目',
      philosophy: '照护理念',
      testimonials: '家庭见证',
      contact: '联系我们'
    },
    actions: {
      bookVisit: '预约参观',
      languageSwitch: '语言'
    }
  },
  ms: {
    appName: 'ElderCare SG',
    navigation: {
      programs: 'Program',
      philosophy: 'Falsafah kami',
      testimonials: 'Testimoni',
      contact: 'Hubungi'
    },
    actions: {
      bookVisit: 'Tempah lawatan pusat',
      languageSwitch: 'Bahasa'
    }
  },
  ta: {
    appName: 'ElderCare SG',
    navigation: {
      programs: 'பிரோகிராம்கள்',
      philosophy: 'எங்கள் நோக்கம்',
      testimonials: 'சான்றுகள்',
      contact: 'தொடர்பு'
    },
    actions: {
      bookVisit: 'மையத்தை முன்பதிவு செய்யவும்',
      languageSwitch: 'மொழி'
    }
  }
}

const resolveCopy = (locale: string | undefined) =>
  copyByLocale[(isLocale(locale) ? locale : 'en') as LocaleKey] ?? copyByLocale.en

const meta = {
  title: 'Sections/Header',
  component: Header,
  parameters: {
    layout: 'fullscreen'
  },
  tags: ['autodocs'],
  args: {
    locale: 'en' as Locale,
    appName: copyByLocale.en.appName,
    navigation: copyByLocale.en.navigation,
    actions: copyByLocale.en.actions
  }
} satisfies Meta<typeof Header>

export default meta

type Story = StoryObj<typeof meta>

const renderWithPage = (args: Story['args'], localeString: string | undefined) => {
  const copy = resolveCopy(localeString)
  const locale = (isLocale(localeString) ? localeString : args?.locale) as Locale

  return (
    <div style={{ minHeight: '200vh', background: 'var(--color-surface-subtle)' }}>
      <Header
        locale={locale}
        appName={copy.appName}
        navigation={copy.navigation}
        actions={copy.actions}
      />
      <main style={{ paddingTop: '120px', maxWidth: '720px', margin: '0 auto', color: 'var(--color-text-secondary)' }}>
        <h2 style={{ fontFamily: 'var(--font-family-display)', color: 'var(--color-text-primary)', marginBottom: '1rem' }}>
          {copy.navigation.programs}
        </h2>
        <p>
          Scroll to see the sticky header transition. Use the toolbar locale switcher to observe translated navigation labels and
          call-to-action text.
        </p>
      </main>
    </div>
  )
}

export const Overview: Story = {
  render: (args, { globals }) => renderWithPage(args, globals.locale as string)
}

export const MobileMenuOpen: Story = {
  render: (args, { globals }) => renderWithPage(args, globals.locale as string),
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    const toggleButton = await canvas.findByRole('button', { name: /toggle menu/i })
    await userEvent.click(toggleButton)
  }
}
