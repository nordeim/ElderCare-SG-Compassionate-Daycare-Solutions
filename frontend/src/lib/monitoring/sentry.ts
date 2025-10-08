import * as Sentry from '@sentry/nextjs'

const SENTRY_DSN = process.env.NEXT_PUBLIC_SENTRY_DSN
const SENTRY_ENVIRONMENT = process.env.NEXT_PUBLIC_SENTRY_ENVIRONMENT ?? 'staging'
const SENTRY_TRACES_SAMPLE_RATE = Number(process.env.NEXT_PUBLIC_SENTRY_TRACES_SAMPLE_RATE ?? '0.1')

export const isSentryEnabled = Boolean(SENTRY_DSN)

let sentryInitialized = false

export function initSentry(): void {
  if (!isSentryEnabled || sentryInitialized) {
    return
  }

  Sentry.init({
    dsn: SENTRY_DSN,
    environment: SENTRY_ENVIRONMENT,
    tracesSampleRate: SENTRY_TRACES_SAMPLE_RATE,
    replaysSessionSampleRate: 0,
    replaysOnErrorSampleRate: 0
  })

  sentryInitialized = true
}
