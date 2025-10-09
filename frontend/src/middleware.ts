import createMiddleware from 'next-intl/middleware'

import { locales, defaultLocale } from '../i18n'

export default createMiddleware({
  locales: Array.from(locales),
  defaultLocale,
  localeDetection: true
})

export const config = {
  matcher: [
    '/((?!api|_next|.*\\..*).*)'
  ]
}
