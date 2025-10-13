export function initGA4(): void {
  if (typeof window === 'undefined') return;

  const id = process.env.NEXT_PUBLIC_GA4_ID || process.env.NEXT_PUBLIC_GA_MEASUREMENT_ID;
  if (!id) {
    // Telemetry intentionally disabled when env var missing
    // eslint-disable-next-line no-console
    console.info('GA4 not initialized: measurement id missing');
    return;
  }

  // Ensure gtag is present
  // If using Next.js script loader, the gtag script will attach window.gtag
  // This helper simply configures the measurement id if present
  try {
    // @ts-ignore
    if (typeof window.gtag === 'function') {
      // @ts-ignore
      window.gtag('config', id);
    }
  } catch (err) {
    // swallow telemetry errors
    // eslint-disable-next-line no-console
    console.warn('GA4 init error', err);
  }
}
