const HOTJAR_ID = process.env.NEXT_PUBLIC_HOTJAR_ID
const HOTJAR_VERSION = process.env.NEXT_PUBLIC_HOTJAR_VERSION ?? '6'

export const isHotjarEnabled = Boolean(HOTJAR_ID)

export function initHotjar(): void {
  if (!isHotjarEnabled || typeof window === 'undefined') {
    return
  }

  // Prevent duplicate script injection
  if (document.getElementById('hotjar-script')) {
    return
  }

  window.hj =
    window.hj ||
    function (...args: unknown[]) {
      ;(window.hj.q = window.hj.q || []).push(args)
    }

  window._hjSettings = { hjid: Number(HOTJAR_ID), hjsv: Number(HOTJAR_VERSION) }

  const script = document.createElement('script')
  script.id = 'hotjar-script'
  script.async = true
  script.src = `https://static.hotjar.com/c/hotjar-${HOTJAR_ID}.js?sv=${HOTJAR_VERSION}`

  document.head.appendChild(script)
}

declare global {
  interface Window {
    hj: (...args: unknown[]) => void
    _hjSettings: {
      hjid: number
      hjsv: number
    }
  }
}
