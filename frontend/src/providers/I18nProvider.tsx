"use client"

import { ReactNode } from 'react'
import { NextIntlClientProvider } from 'next-intl'

import { Locale } from '@/lib/i18n/config'
import { loadMessages, Messages } from '@/lib/i18n/getMessages'

type AsyncMessages = Messages | Promise<Messages>

interface I18nProviderProps {
  children: ReactNode
  locale: Locale
  messages: AsyncMessages
}

export async function I18nProvider({ children, locale, messages }: I18nProviderProps) {
  const resolvedMessages = await messages

  return (
    <NextIntlClientProvider locale={locale} messages={resolvedMessages}>
      {children}
    </NextIntlClientProvider>
  )
}

export async function I18nProviderWithLoader({
  children,
  locale
}: Omit<I18nProviderProps, 'messages'>) {
  const messages = loadMessages(locale)
  return <I18nProvider locale={locale} messages={messages}>{children}</I18nProvider>
}
