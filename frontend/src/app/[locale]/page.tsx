import Header from '@/components/layout/header'
import Footer from '@/components/layout/footer'
import Hero from '@/components/sections/hero'
import ProgramHighlights from '@/components/sections/program-highlights'

export default function LocaleHomePage() {
  return (
    <main className="min-h-screen">
      <Header />
      <Hero />
      <ProgramHighlights />
      <Footer />
    </main>
  )
}
