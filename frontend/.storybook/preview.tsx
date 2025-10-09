import type { Preview } from '@storybook/react'
import type { ReactNode } from 'react'
import { useEffect } from 'react'
import { NextIntlClientProvider } from 'next-intl'

import '../src/styles/design-tokens.css'
import '../src/app/globals.css'

interface ProviderProps {
  theme: string
  locale: string
  children: ReactNode
}

function StoryProvider({ theme, locale, children }: ProviderProps) {
  useEffect(() => {
    const root = document.documentElement
    root.setAttribute('data-theme', theme)

    return () => {
      root.removeAttribute('data-theme')
    }
  }, [theme])

  return (
    <NextIntlClientProvider locale={locale} messages={{}}>
      {children}
    </NextIntlClientProvider>
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
  decorators: [
    (Story, context) => (
      <StoryProvider theme={context.globals.theme as string} locale={context.globals.locale as string}>
        <Story />
      </StoryProvider>
    )
  ]
}

export default preview
