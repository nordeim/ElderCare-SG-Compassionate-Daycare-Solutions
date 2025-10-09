import React, { ReactNode, useEffect } from 'react'
import type { Decorator, Preview } from '@storybook/react'
import { NextIntlClientProvider } from 'next-intl'

import '../src/styles/design-tokens.css'
import '../src/app/globals.css'

interface ProviderProps {
  theme: string
  locale: string
  children: ReactNode
}

const StoryProviders = ({ theme, locale, children }: ProviderProps) => {
  useEffect(() => {
    const root = document.documentElement
    root.setAttribute('data-theme', theme)

    return () => {
      root.removeAttribute('data-theme')
    }
  }, [theme])

  return <NextIntlClientProvider locale={locale} messages={{}}>{children}</NextIntlClientProvider>
}

const withProviders: Decorator = (Story, context) => {
  const { theme, locale } = context.globals as { theme: string; locale: string }

  return (
    <StoryProviders theme={theme} locale={locale}>
      <Story />
    </StoryProviders>
  )
}

const preview: Preview = {
  parameters: {
    layout: 'centered',
    actions: { argTypesRegex: '^on[A-Z].*' },
    controls: {
      matchers: {
        color: /(background|color)$/i,
        date: /Date$/
      }
    }
  },
  globalTypes: {
    theme: {
      name: 'Theme',
      description: 'Design token theme',
      defaultValue: 'light',
      toolbar: {
        icon: 'circlehollow',
        items: [
          { value: 'light', title: 'Light' },
          { value: 'dark', title: 'Dark' }
        ]
      }
    },
    locale: {
      name: 'Locale',
      description: 'Active locale for next-intl',
      defaultValue: 'en',
      toolbar: {
        icon: 'globe',
        items: [
          { value: 'en', title: 'English' },
          { value: 'zh', title: '中文' },
          { value: 'ms', title: 'Bahasa Melayu' },
          { value: 'ta', title: 'தமிழ்' }
        ]
      }
    }
  },
  decorators: [withProviders]
}

export default preview
