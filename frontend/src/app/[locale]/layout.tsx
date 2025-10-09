import { ReactNode } from 'react'
import { notFound } from 'next/navigation'

import { I18nProvider } from '@/providers/I18nProvider'
import { AnalyticsProvider } from '@/providers/AnalyticsProvider'
import { loadMessages } from '@/lib/i18n/getMessages'
import { Locale, SUPPORTED_LOCALES, isLocale } from '@/lib/i18n/config'

type LocaleLayoutProps = {
  children: ReactNode
  params: {
    locale: string
  }
}

export function generateStaticParams() {
  return SUPPORTED_LOCALES.map((locale) => ({ locale }))
}

export default async function LocaleLayout({ children, params }: LocaleLayoutProps) {
  const { locale } = params

  if (!isLocale(locale)) {
    notFound()
  }

  const typedLocale = locale as Locale
  const messages = await loadMessages(typedLocale)

  return (
    <I18nProvider locale={typedLocale} messages={messages}>
      <AnalyticsProvider>{children}</AnalyticsProvider>
    </I18nProvider>
  )
}
