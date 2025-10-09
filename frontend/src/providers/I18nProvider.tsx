"use client"

import { ReactNode } from 'react'
import { NextIntlClientProvider } from 'next-intl'

import { Locale } from '@/lib/i18n/config'
import { Messages } from '@/lib/i18n/getMessages'

interface I18nProviderProps {
  children: ReactNode
  locale: Locale
  messages: Messages
}

export function I18nProvider({ children, locale, messages }: I18nProviderProps) {
  return (
    <NextIntlClientProvider locale={locale} messages={messages}>
      {children}
    </NextIntlClientProvider>
  )
}
