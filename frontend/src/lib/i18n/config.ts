import { locales as SUPPORTED_LOCALES, defaultLocale as DEFAULT_LOCALE, type Locale } from '../../../i18n'

export { SUPPORTED_LOCALES, DEFAULT_LOCALE }
export type { Locale }

const localeNames: Record<Locale, string> = {
  en: 'English',
  zh: '中文 (简体)',
  ms: 'Bahasa Melayu',
  ta: 'தமிழ்'
}

export function getLocaleName(locale: Locale): string {
  return localeNames[locale]
}

export function isLocale(value: string | undefined): value is Locale {
  return Boolean(value && SUPPORTED_LOCALES.includes(value as Locale))
}
