import type { Meta, StoryObj } from '@storybook/react'
import { tokens, colorScales, semanticColors, typographyTokens, spaceTokens } from '@/lib/theme/tokens'
import React from 'react'

const TokenSection = ({ title, children }: { title: string; children: React.ReactNode }) => (
  <section style={{ marginBottom: '2rem' }}>
    <h3 style={{ fontSize: '1.25rem', fontWeight: 600, marginBottom: '0.75rem' }}>{title}</h3>
    {children}
  </section>
)

const ColorSwatches = ({
  palette
}: {
  palette: Record<string, string>
}) => (
  <div style={{ display: 'grid', gap: '0.75rem', gridTemplateColumns: 'repeat(auto-fit, minmax(120px, 1fr))' }}>
    {Object.entries(palette).map(([name, value]) => (
      <div
        key={name}
        style={{
          borderRadius: 'var(--radius-md)',
          overflow: 'hidden',
          border: '1px solid var(--color-border-default)',
          boxShadow: 'var(--shadow-sm)'
        }}
      >
        <div style={{ backgroundColor: value, height: '64px' }} />
        <dl style={{ padding: '0.75rem', backgroundColor: 'var(--color-surface-default)' }}>
          <dt style={{ fontWeight: 600, fontSize: '0.875rem' }}>{name}</dt>
          <dd style={{ fontFamily: 'var(--font-family-base)', fontSize: '0.75rem', opacity: 0.75 }}>{value}</dd>
        </dl>
      </div>
    ))}
  </div>
)

const TypographyTable = () => (
  <table style={{ width: '100%', borderCollapse: 'separate', borderSpacing: 0 }}>
    <thead>
      <tr>
        <th style={tableHeaderStyle}>Token</th>
        <th style={tableHeaderStyle}>Preview</th>
        <th style={tableHeaderStyle}>Value</th>
      </tr>
    </thead>
    <tbody>
      {Object.entries(typographyTokens).map(([tokenName, cssVar]) => (
        <tr key={tokenName}>
          <td style={tableCellStyle}>{tokenName}</td>
          <td style={tableCellStyle}>
            <span style={{ fontFamily: cssVar.includes('display') ? 'var(--font-family-display)' : 'var(--font-family-base)', fontSize: cssVar }}>
              The quick brown fox
            </span>
          </td>
          <td style={tableCellStyle}>{cssVar}</td>
        </tr>
      ))}
    </tbody>
  </table>
)

const SpaceScale = () => (
  <div style={{ display: 'grid', gap: '0.75rem', gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))' }}>
    {Object.entries(spaceTokens).map(([name, value]) => (
      <div key={name} style={spaceItemStyle}>
        <span style={{ fontWeight: 600 }}>{name}</span>
        <span style={{ fontFamily: 'var(--font-family-base)', fontSize: '0.75rem', opacity: 0.75 }}>{value}</span>
        <span
          aria-hidden="true"
          style={{
            display: 'block',
            marginTop: '0.5rem',
            width: value,
            height: '0.5rem',
            borderRadius: 'var(--radius-pill)',
            backgroundColor: 'var(--color-primary-500)'
          }}
        />
      </div>
    ))}
  </div>
)

const tableHeaderStyle: React.CSSProperties = {
  textAlign: 'left',
  padding: '0.75rem',
  backgroundColor: 'var(--color-surface-subtle)',
  fontFamily: 'var(--font-family-base)',
  fontSize: '0.875rem',
  borderBottom: '1px solid var(--color-border-strong)'
}

const tableCellStyle: React.CSSProperties = {
  padding: '0.75rem',
  borderBottom: '1px solid var(--color-border-default)',
  verticalAlign: 'top',
  fontFamily: 'var(--font-family-base)',
  fontSize: '0.875rem'
}

const spaceItemStyle: React.CSSProperties = {
  padding: '1rem',
  border: '1px solid var(--color-border-default)',
  borderRadius: 'var(--radius-md)',
  backgroundColor: 'var(--color-surface-default)',
  boxShadow: 'var(--shadow-xs)'
}

const meta: Meta = {
  title: 'Foundation/Tokens',
  parameters: {
    layout: 'fullscreen',
    docs: {
      description: {
        page:
          'Design tokens drive spacing, color, typography, motion and elevation across the component library. Use the controls below to reference the semantic and scale tokens when building new components.'
      }
    }
  }
}

export default meta

type Story = StoryObj

export const Overview: Story = {
  render: () => (
    <article style={{ padding: '2rem', background: 'var(--color-surface-subtle)', minHeight: '100vh' }}>
      <h2 style={{ fontSize: '2rem', fontWeight: 600, marginBottom: '1rem' }}>Design Tokens Overview</h2>
      <p style={{ maxWidth: '720px', marginBottom: '2rem', fontSize: '1rem', lineHeight: 1.6 }}>
        The palettes and scales defined here are exported to Tailwind (`tailwind.config.ts`) and available as CSS variables in
        `design-tokens.css`. Switch themes via the Storybook toolbar to validate dark mode and high-contrast behavior.
      </p>

      <TokenSection title="Color Scales">
        <ColorSwatches palette={colorScales.primary} />
        <div style={{ height: '1.5rem' }} />
        <ColorSwatches palette={colorScales.secondary} />
        <div style={{ height: '1.5rem' }} />
        <ColorSwatches palette={colorScales.accent} />
        <div style={{ height: '1.5rem' }} />
        <ColorSwatches palette={colorScales.neutral} />
      </TokenSection>

      <TokenSection title="Semantic Colors">
        <ColorSwatches palette={semanticColors} />
      </TokenSection>

      <TokenSection title="Typography">
        <TypographyTable />
      </TokenSection>

      <TokenSection title="Spacing Scale">
        <SpaceScale />
      </TokenSection>
    </article>
  )
}
