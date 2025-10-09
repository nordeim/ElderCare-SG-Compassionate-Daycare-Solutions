import { redirect } from 'next/navigation'

import { DEFAULT_LOCALE } from '@/lib/i18n/config'

export default function RootRedirectPage() {
  redirect(`/${DEFAULT_LOCALE}`)
}
