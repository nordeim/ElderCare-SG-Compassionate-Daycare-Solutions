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

  const hjFn: HotjarFunction =
    window.hj ||
    function (...args: unknown[]) {
      ;(hjFn.q = hjFn.q || []).push(args)
    }

  window.hj = hjFn

  window._hjSettings = { hjid: Number(HOTJAR_ID), hjsv: Number(HOTJAR_VERSION) }

  const script = document.createElement('script')
  script.id = 'hotjar-script'
  script.async = true
  script.src = `https://static.hotjar.com/c/hotjar-${HOTJAR_ID}.js?sv=${HOTJAR_VERSION}`

  document.head.appendChild(script)
}

declare global {
  interface Window {
    hj: HotjarFunction
    _hjSettings: {
      hjid: number
      hjsv: number
    }
  }
}

type HotjarFunction = ((...args: unknown[]) => void) & {
  q?: unknown[][]
}
