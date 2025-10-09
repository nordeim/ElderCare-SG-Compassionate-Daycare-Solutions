'use client'

import { ChangeEvent, useTransition } from 'react'
import { usePathname, useRouter } from 'next/navigation'

import { locales } from '@/../i18n'
import { getLocaleName } from '@/lib/i18n/config'
import { Locale } from '@/lib/i18n/config'

const localeOptions = Array.from(locales)

function buildLocalizedPath(pathname: string, nextLocale: string) {
  const segments = pathname.split('/').filter(Boolean)

  if (segments.length === 0) {
    return `/${nextLocale}`
  }

  segments[0] = nextLocale
  return `/${segments.join('/')}`
}

type LanguageSwitcherProps = {
  label: string
  locale: Locale
  inline?: boolean
}

export function LanguageSwitcher({ label, locale, inline = false }: LanguageSwitcherProps) {
  const [isPending, startTransition] = useTransition()
  const router = useRouter()
  const pathname = usePathname()

  const handleChange = (event: ChangeEvent<HTMLSelectElement>) => {
    const nextLocale = event.target.value

    if (nextLocale === locale) {
      return
    }

    const nextPath = buildLocalizedPath(pathname ?? '/', nextLocale)
    startTransition(() => {
      router.push(nextPath)
      router.refresh()
    })
  }

  return (
    <label className={`flex items-center gap-2 text-sm ${inline ? '' : 'text-eldercare-slate-gray-1'}`}>
      <span>{label}</span>
      <select
        className="rounded-md border border-eldercare-slate-gray-3 bg-white px-2 py-1 text-eldercare-slate-gray-1 focus:outline-none focus:ring-2 focus:ring-primary-500"
        value={locale}
        onChange={handleChange}
        disabled={isPending}
      >
        {localeOptions.map((value) => (
          <option key={value} value={value}>
            {getLocaleName(value)}
          </option>
        ))}
      </select>
    </label>
  )
}
