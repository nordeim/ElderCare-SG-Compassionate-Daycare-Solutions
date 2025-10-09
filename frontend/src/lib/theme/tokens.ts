export type ColorScaleToken =
  | 'primary'
  | 'secondary'
  | 'accent'
  | 'neutral'

export type SemanticColorToken =
  | 'surface-default'
  | 'surface-subtle'
  | 'surface-inverse'
  | 'text-primary'
  | 'text-secondary'
  | 'text-inverse'
  | 'border-default'
  | 'border-strong'
  | 'success'
  | 'warning'
  | 'danger'
  | 'info'

export type TypographyToken =
  | 'font-family-base'
  | 'font-family-display'
  | 'font-size-xs'
  | 'font-size-sm'
  | 'font-size-base'
  | 'font-size-lg'
  | 'font-size-xl'
  | 'font-size-2xl'
  | 'font-size-3xl'
  | 'font-size-4xl'
  | 'font-size-5xl'
  | 'line-height-tight'
  | 'line-height-snug'
  | 'line-height-normal'
  | 'line-height-relaxed'

export type SpaceToken =
  | 'space-0'
  | 'space-1'
  | 'space-2'
  | 'space-3'
  | 'space-4'
  | 'space-5'
  | 'space-6'
  | 'space-7'
  | 'space-8'
  | 'space-9'
  | 'space-10'
  | 'space-12'
  | 'space-16'
  | 'space-20'
  | 'space-24'
  | 'space-28'
  | 'space-32'

export type RadiusToken =
  | 'radius-xs'
  | 'radius-sm'
  | 'radius-md'
  | 'radius-lg'
  | 'radius-xl'
  | 'radius-pill'

export type ShadowToken = 'shadow-xs' | 'shadow-sm' | 'shadow-md' | 'shadow-lg' | 'shadow-xl'

export type MotionDurationToken =
  | 'motion-duration-xs'
  | 'motion-duration-sm'
  | 'motion-duration-md'
  | 'motion-duration-lg'
  | 'motion-duration-xl'

export type MotionEasingToken = 'motion-ease-standard' | 'motion-ease-emphasized'

const range = (prefix: string): Record<string, string> =>
  Object.fromEntries(
    ['50', '100', '200', '300', '400', '500', '600', '700', '800', '900'].map(step => [
      step,
      `var(--color-${prefix}-${step})`
    ])
  )

export const colorScales: Record<ColorScaleToken, Record<string, string>> = {
  primary: range('primary'),
  secondary: range('secondary'),
  accent: range('accent'),
  neutral: range('neutral')
}

export const semanticColors: Record<SemanticColorToken, string> = {
  'surface-default': 'var(--color-surface-default)',
  'surface-subtle': 'var(--color-surface-subtle)',
  'surface-inverse': 'var(--color-surface-inverse)',
  'text-primary': 'var(--color-text-primary)',
  'text-secondary': 'var(--color-text-secondary)',
  'text-inverse': 'var(--color-text-inverse)',
  'border-default': 'var(--color-border-default)',
  'border-strong': 'var(--color-border-strong)',
  success: 'var(--color-success-500)',
  warning: 'var(--color-warning-500)',
  danger: 'var(--color-danger-500)',
  info: 'var(--color-info-500)'
}

export const typographyTokens: Record<TypographyToken, string> = {
  'font-family-base': 'var(--font-family-base)',
  'font-family-display': 'var(--font-family-display)',
  'font-size-xs': 'var(--font-size-xs)',
  'font-size-sm': 'var(--font-size-sm)',
  'font-size-base': 'var(--font-size-base)',
  'font-size-lg': 'var(--font-size-lg)',
  'font-size-xl': 'var(--font-size-xl)',
  'font-size-2xl': 'var(--font-size-2xl)',
  'font-size-3xl': 'var(--font-size-3xl)',
  'font-size-4xl': 'var(--font-size-4xl)',
  'font-size-5xl': 'var(--font-size-5xl)',
  'line-height-tight': 'var(--line-height-tight)',
  'line-height-snug': 'var(--line-height-snug)',
  'line-height-normal': 'var(--line-height-normal)',
  'line-height-relaxed': 'var(--line-height-relaxed)'
}

export const spaceTokens: Record<SpaceToken, string> = {
  'space-0': 'var(--space-0)',
  'space-1': 'var(--space-1)',
  'space-2': 'var(--space-2)',
  'space-3': 'var(--space-3)',
  'space-4': 'var(--space-4)',
  'space-5': 'var(--space-5)',
  'space-6': 'var(--space-6)',
  'space-7': 'var(--space-7)',
  'space-8': 'var(--space-8)',
  'space-9': 'var(--space-9)',
  'space-10': 'var(--space-10)',
  'space-12': 'var(--space-12)',
  'space-16': 'var(--space-16)',
  'space-20': 'var(--space-20)',
  'space-24': 'var(--space-24)',
  'space-28': 'var(--space-28)',
  'space-32': 'var(--space-32)'
}

export const radiusTokens: Record<RadiusToken, string> = {
  'radius-xs': 'var(--radius-xs)',
  'radius-sm': 'var(--radius-sm)',
  'radius-md': 'var(--radius-md)',
  'radius-lg': 'var(--radius-lg)',
  'radius-xl': 'var(--radius-xl)',
  'radius-pill': 'var(--radius-pill)'
}

export const shadowTokens: Record<ShadowToken, string> = {
  'shadow-xs': 'var(--shadow-xs)',
  'shadow-sm': 'var(--shadow-sm)',
  'shadow-md': 'var(--shadow-md)',
  'shadow-lg': 'var(--shadow-lg)',
  'shadow-xl': 'var(--shadow-xl)'
}

export const motionDurationTokens: Record<MotionDurationToken, string> = {
  'motion-duration-xs': 'var(--motion-duration-xs)',
  'motion-duration-sm': 'var(--motion-duration-sm)',
  'motion-duration-md': 'var(--motion-duration-md)',
  'motion-duration-lg': 'var(--motion-duration-lg)',
  'motion-duration-xl': 'var(--motion-duration-xl)'
}

export const motionEasingTokens: Record<MotionEasingToken, string> = {
  'motion-ease-standard': 'var(--motion-ease-standard)',
  'motion-ease-emphasized': 'var(--motion-ease-emphasized)'
}

export const tokens = {
  colors: {
    scales: colorScales,
    semantic: semanticColors
  },
  typography: typographyTokens,
  space: spaceTokens,
  radius: radiusTokens,
  shadows: shadowTokens,
  motion: {
    duration: motionDurationTokens,
    easing: motionEasingTokens
  }
} as const

export type Tokens = typeof tokens
