import { DEFAULT_LOCALE, Locale } from '@/lib/i18n/config'

export type Messages = Record<string, unknown>

export async function loadMessages(locale: Locale): Promise<Messages> {
  try {
    const messages = await import(`@/locales/${locale}/index`)
    return messages.default
  } catch (error) {
    if (locale !== DEFAULT_LOCALE) {
      console.warn(`Falling back to ${DEFAULT_LOCALE} messages for locale "${locale}"`, error)
      const fallbackMessages = await import(`@/locales/${DEFAULT_LOCALE}/index`)
      return fallbackMessages.default
    }

    console.error(`Unable to load messages for default locale "${DEFAULT_LOCALE}"`, error)
    throw error
  }
}
