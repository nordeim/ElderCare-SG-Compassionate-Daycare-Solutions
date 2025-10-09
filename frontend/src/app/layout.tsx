import type { Metadata } from 'next'
import { Inter, Playfair_Display } from 'next/font/google'
import './globals.css'
import { AnalyticsProvider } from '@/providers/AnalyticsProvider'

const inter = Inter({ subsets: ['latin'], variable: '--font-inter' })
const playfairDisplay = Playfair_Display({
  subsets: ['latin'],
  variable: '--font-playfair',
  weight: ['400', '600', '700']
})

export const metadata: Metadata = {
  metadataBase: new URL(process.env.NEXT_PUBLIC_APP_URL ?? 'https://eldercare-sg.example.com'),
  title: 'ElderCare SG - Compassionate Daycare Solutions',
  description: 'A warm, modern, mobile-responsive platform that introduces families in Singapore to trusted elderly daycare services.',
  keywords: ['elderly care', 'daycare', 'senior care', 'Singapore', 'compassionate care'],
  authors: [{ name: 'ElderCare SG Team' }],
  openGraph: {
    title: 'ElderCare SG - Compassionate Daycare Solutions',
    description: 'A warm, modern, mobile-responsive platform that introduces families in Singapore to trusted elderly daycare services.',
    images: ['/images/og-image.jpg'],
  },
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en" className={`${inter.variable} ${playfairDisplay.variable}`}>
      <body className="font-inter bg-eldercare-off-white text-eldercare-slate-gray-1">
        <AnalyticsProvider>{children}</AnalyticsProvider>
      </body>
    </html>
  )
}
