export function initHotjar(): void {
  if (typeof window === 'undefined') return;

  const id = process.env.NEXT_PUBLIC_HOTJAR_ID;
  const version = process.env.NEXT_PUBLIC_HOTJAR_VERSION || '6';
  if (!id) {
    // eslint-disable-next-line no-console
    console.info('Hotjar not initialized: NEXT_PUBLIC_HOTJAR_ID missing');
    return;
  }

  try {
    // Hotjar loader (lightweight) — inject script if not present
    if (!window.hj) {
      const script = document.createElement('script');
      script.async = true;
      script.src = `https://static.hotjar.com/c/hotjar-${id}.js?sv=${version}`;
      document.head.appendChild(script);
    }
  } catch (err) {
    // eslint-disable-next-line no-console
    console.warn('Hotjar init error', err);
  }
}
