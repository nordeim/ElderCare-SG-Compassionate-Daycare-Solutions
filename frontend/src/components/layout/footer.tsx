'use client'

import Link from 'next/link'
import { Button } from '@/components/ui/button'
import { Mail, Phone, MapPin } from 'lucide-react'

type FooterCertifications = {
  moh: string
  iso: string
  eldercare: string
  sg: string
}

type FooterSections = {
  contactHeading: string
  newsletterHeading: string
  newsletterDescription: string
  newsletterPlaceholder: string
  newsletterCta: string
  certificationsHeading: string
  certifications: FooterCertifications
}

type FooterLegal = {
  rights: string
  privacy: string
  terms: string
}

type FooterProps = {
  locale: string
  sections: FooterSections
  legal: FooterLegal
}

const Footer = ({ locale, sections, legal }: FooterProps) => {
  return (
    <footer className="bg-eldercare-deep-blue text-white">
      <div className="container mx-auto px-6 py-12">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {/* Contact Information */}
          <div>
            <h3 className="text-xl font-playfair font-semibold mb-4">
              {sections.contactHeading}
            </h3>
            <div className="space-y-3">
              <div className="flex items-center space-x-3">
                <Phone size={18} />
                <span>+65 6123 4567</span>
              </div>
              <div className="flex items-center space-x-3">
                <Mail size={18} />
                <span>info@eldercare-sg.example.com</span>
              </div>
              <div className="flex items-center space-x-3">
                <MapPin size={18} />
                <span>123 Care Lane, Singapore 123456</span>
              </div>
            </div>
          </div>

          {/* Newsletter */}
          <div>
            <h3 className="text-xl font-playfair font-semibold mb-4">
              {sections.newsletterHeading}
            </h3>
            <p className="mb-4 text-eldercare-off-white">
              {sections.newsletterDescription}
            </p>
            <form className="flex flex-col space-y-2">
              <input
                type="email"
                placeholder={sections.newsletterPlaceholder}
                className="px-4 py-2 rounded-md text-eldercare-deep-blue"
                required
              />
              <Button variant="secondary" type="submit">
                {sections.newsletterCta}
              </Button>
            </form>
          </div>

          {/* Certifications */}
          <div>
            <h3 className="text-xl font-playfair font-semibold mb-4">
              {sections.certificationsHeading}
            </h3>
            <div className="grid grid-cols-2 gap-4">
              {(['moh', 'iso', 'eldercare', 'sg'] as const).map((key) => (
                <div key={key} className="bg-white/10 rounded-md p-3 flex items-center justify-center">
                  <span className="text-sm">{sections.certifications[key]}</span>
                </div>
              ))}
            </div>
          </div>
        </div>

        <div className="border-t border-white/20 mt-8 pt-8 text-center text-eldercare-off-white">
          <p>{legal.rights}</p>
          <div className="mt-4 space-x-4">
            <Link href={`/${locale}/privacy`} className="hover:text-eldercare-gold transition-colors">
              {legal.privacy}
            </Link>
            <Link href={`/${locale}/terms`} className="hover:text-eldercare-gold transition-colors">
              {legal.terms}
            </Link>
          </div>
        </div>
      </div>
    </footer>
  )
}

export default Footer
