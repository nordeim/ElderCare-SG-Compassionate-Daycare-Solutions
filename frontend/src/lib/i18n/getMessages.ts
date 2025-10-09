import type { AbstractIntlMessages } from 'next-intl'

import { DEFAULT_LOCALE, Locale } from '@/lib/i18n/config'

export type Messages = AbstractIntlMessages

async function importMessages(locale: Locale): Promise<AbstractIntlMessages> {
  const messagesModule = (await import(`@/locales/${locale}/index`)) as { default: AbstractIntlMessages }
  return messagesModule.default
}

export async function loadMessages(locale: Locale): Promise<Messages> {
  try {
    return await importMessages(locale)
  } catch (error) {
    if (locale !== DEFAULT_LOCALE) {
      console.warn(`Falling back to ${DEFAULT_LOCALE} messages for locale "${locale}"`, error)
      return await importMessages(DEFAULT_LOCALE)
    }

    console.error(`Unable to load messages for default locale "${DEFAULT_LOCALE}"`, error)
    throw error
  }
}
