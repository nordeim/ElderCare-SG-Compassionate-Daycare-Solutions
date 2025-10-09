import { getRequestConfig } from 'next-intl/server'

import { DEFAULT_LOCALE, type Locale, isLocale } from '@/lib/i18n/config'
import { loadMessages } from '@/lib/i18n/getMessages'

export default getRequestConfig(async ({ requestLocale }) => {
  const localeFromRequest = await requestLocale
  const normalizedLocale: Locale = isLocale(localeFromRequest) ? localeFromRequest : DEFAULT_LOCALE
  const messages = await loadMessages(normalizedLocale)

  return {
    locale: normalizedLocale,
    messages
  }
})
