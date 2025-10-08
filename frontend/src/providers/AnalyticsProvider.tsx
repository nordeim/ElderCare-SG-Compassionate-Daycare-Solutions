"use client"

import { ReactNode, useEffect } from 'react'
import { usePathname } from 'next/navigation'

import { initGA, isGAEnabled, trackPageview } from '@/lib/analytics/ga'
import { initHotjar, isHotjarEnabled } from '@/lib/analytics/hotjar'
import { initSentry, isSentryEnabled } from '@/lib/monitoring/sentry'

interface AnalyticsProviderProps {
  children: ReactNode
}

export function AnalyticsProvider({ children }: AnalyticsProviderProps) {
  const pathname = usePathname()

  useEffect(() => {
    void initSentry()
    initGA()
    initHotjar()
  }, [])

  useEffect(() => {
    if (pathname && isGAEnabled) {
      trackPageview(pathname)
    }
  }, [pathname])

  return <>{children}</>
}

export const isInstrumentationEnabled = isGAEnabled || isHotjarEnabled || isSentryEnabled
