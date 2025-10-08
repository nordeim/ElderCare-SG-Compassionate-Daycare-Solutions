declare global {
  interface Window {
    dataLayer: Record<string, unknown>[]
  }
}

const GA_MEASUREMENT_ID = process.env.NEXT_PUBLIC_GA_MEASUREMENT_ID

export const isGAEnabled = Boolean(GA_MEASUREMENT_ID)

export function initGA(): void {
  if (!isGAEnabled) {
    return
  }

  window.dataLayer = window.dataLayer || []
  window.dataLayer.push({
    event: 'js',
    js: new Date()
  })
  window.dataLayer.push({
    event: 'config',
    send_page_view: false,
    page_path: window.location.pathname,
    measurement_id: GA_MEASUREMENT_ID
  })
}

export function trackPageview(url: string): void {
  if (!isGAEnabled || typeof window === 'undefined') {
    return
  }

  window.dataLayer = window.dataLayer || []
  window.dataLayer.push({
    event: 'pageview',
    page_path: url
  })
}
