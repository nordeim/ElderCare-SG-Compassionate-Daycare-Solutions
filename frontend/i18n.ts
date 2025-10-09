export const locales = ['en', 'zh', 'ms', 'ta'] as const
export const defaultLocale = 'en'
export type Locale = (typeof locales)[number]
