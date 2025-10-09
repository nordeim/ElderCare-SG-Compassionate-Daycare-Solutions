import createMiddleware from 'next-intl/middleware'

import { DEFAULT_LOCALE, SUPPORTED_LOCALES } from './lib/i18n/config'

export default createMiddleware({
  locales: Array.from(SUPPORTED_LOCALES),
  defaultLocale: DEFAULT_LOCALE,
  localeDetection: true
})

export const config = {
  matcher: [
    '/((?!api|_next|.*\\..*).*)'
  ]
}
