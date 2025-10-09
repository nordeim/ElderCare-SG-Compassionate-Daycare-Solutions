'use client'

import { useState, useEffect } from 'react'
import Link from 'next/link'
import { useLocale, useTranslations } from 'next-intl'
import { Button } from '@/components/ui/button'
import { Menu, X } from 'lucide-react'
import { LanguageSwitcher } from '@/components/layout/language-switcher'

const Header = () => {
  const [isScrolled, setIsScrolled] = useState(false)
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false)
  const locale = useLocale()
  const tNav = useTranslations('navigation.primary')
  const tActions = useTranslations('navigation.actions')
  const tCommon = useTranslations('common.app')

  useEffect(() => {
    const handleScroll = () => {
      if (window.scrollY > 10) {
        setIsScrolled(true)
      } else {
        setIsScrolled(false)
      }
    }

    window.addEventListener('scroll', handleScroll)
    return () => window.removeEventListener('scroll', handleScroll)
  }, [])

  return (
    <header
      className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
        isScrolled
          ? 'bg-white/80 backdrop-blur-md shadow-sm'
          : 'bg-transparent'
      }`}
    >
      <div className="container mx-auto px-6 py-4 flex items-center justify-between">
        <div className="flex items-center">
          <Link href={`/${locale}`} className="text-2xl font-playfair font-bold text-eldercare-deep-blue">
            {tCommon('name')}
          </Link>
        </div>

        {/* Desktop Navigation */}
        <nav className="hidden md:flex items-center space-x-8">
          <Link href="#programs" className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors">
            {tNav('programs')}
          </Link>
          <Link href="#philosophy" className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors">
            {tNav('philosophy')}
          </Link>
          <Link href="#testimonials" className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors">
            {tNav('testimonials')}
          </Link>
          <Link href="#contact" className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors">
            {tNav('contact')}
          </Link>
          <Button variant="cta" asChild>
            <Link href="#booking">{tActions('bookVisit')}</Link>
          </Button>
          <LanguageSwitcher inline />
        </nav>

        {/* Mobile Menu Button */}
        <button
          className="md:hidden text-eldercare-deep-blue"
          onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
          aria-label="Toggle menu"
        >
          {isMobileMenuOpen ? <X size={24} /> : <Menu size={24} />}
        </button>
      </div>

      {/* Mobile Navigation */}
      {isMobileMenuOpen && (
        <div className="md:hidden bg-white border-t">
          <nav className="container mx-auto px-6 py-4 flex flex-col space-y-4">
            <Link
              href="#programs"
              className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors"
              onClick={() => setIsMobileMenuOpen(false)}
            >
              {tNav('programs')}
            </Link>
            <Link
              href="#philosophy"
              className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors"
              onClick={() => setIsMobileMenuOpen(false)}
            >
              {tNav('philosophy')}
            </Link>
            <Link
              href="#testimonials"
              className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors"
              onClick={() => setIsMobileMenuOpen(false)}
            >
              {tNav('testimonials')}
            </Link>
            <Link
              href="#contact"
              className="text-eldercare-slate-gray-1 hover:text-eldercare-deep-blue transition-colors"
              onClick={() => setIsMobileMenuOpen(false)}
            >
              {tNav('contact')}
            </Link>
            <Button variant="cta" asChild className="w-full">
              <Link href="#booking" onClick={() => setIsMobileMenuOpen(false)}>
                {tActions('bookVisit')}
              </Link>
            </Button>
            <div className="pt-2 border-t border-eldercare-slate-gray-3">
              <LanguageSwitcher />
            </div>
          </nav>
        </div>
      )}
    </header>
  )
}

export default Header
