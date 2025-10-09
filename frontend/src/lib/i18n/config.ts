export const SUPPORTED_LOCALES = ['en', 'zh', 'ms', 'ta'] as const
export const DEFAULT_LOCALE = 'en'

export type Locale = (typeof SUPPORTED_LOCALES)[number]

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
