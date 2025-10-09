import Header from '@/components/layout/header'
import Footer from '@/components/layout/footer'
import Hero from '@/components/sections/hero'
import ProgramHighlights from '@/components/sections/program-highlights'
import { getTranslations } from 'next-intl/server'
import { Locale } from '@/lib/i18n/config'

export default async function LocaleHomePage({ params }: { params: { locale: string } }) {
  const locale = params.locale as Locale

  const [tCommon, tNavigation, tHome, tFooter] = await Promise.all([
    getTranslations({ locale, namespace: 'common' }),
    getTranslations({ locale, namespace: 'navigation' }),
    getTranslations({ locale, namespace: 'home' }),
    getTranslations({ locale, namespace: 'footer' })
  ])

  const appName = tCommon('app.name')
  const headerNavigation = {
    programs: tNavigation('primary.programs'),
    philosophy: tNavigation('primary.philosophy'),
    testimonials: tNavigation('primary.testimonials'),
    contact: tNavigation('primary.contact')
  }
  const headerActions = {
    bookVisit: tNavigation('actions.bookVisit'),
    languageSwitch: tNavigation('actions.languageSwitch')
  }

  const heroCopy = {
    title: tCommon('hero.title'),
    subtitle: tCommon('hero.subtitle'),
    cta: {
      primary: tCommon('hero.cta.primary'),
      video: tCommon('hero.cta.video')
    }
  }

  const programsCopy = {
    heading: tHome('programs.heading'),
    subheading: tHome('programs.subheading'),
    items: {
      dayPrograms: {
        title: tHome('programs.items.dayPrograms.title'),
        description: tHome('programs.items.dayPrograms.description')
      },
      wellness: {
        title: tHome('programs.items.wellness.title'),
        description: tHome('programs.items.wellness.description')
      },
      familySupport: {
        title: tHome('programs.items.familySupport.title'),
        description: tHome('programs.items.familySupport.description')
      }
    }
  }

  const currentYear = new Date().getFullYear()
  const footerSections = {
    contactHeading: tFooter('sections.contact.heading'),
    newsletterHeading: tFooter('sections.newsletter.heading'),
    newsletterDescription: tFooter('sections.newsletter.description'),
    newsletterPlaceholder: tFooter('sections.newsletter.placeholder'),
    newsletterCta: tFooter('sections.newsletter.cta'),
    certificationsHeading: tFooter('sections.certifications.heading'),
    certifications: {
      moh: tFooter('sections.certifications.labels.moh'),
      iso: tFooter('sections.certifications.labels.iso'),
      eldercare: tFooter('sections.certifications.labels.eldercare'),
      sg: tFooter('sections.certifications.labels.sg')
    }
  }

  const footerLegal = {
    rights: tFooter('legal.rights', { year: currentYear }),
    privacy: tFooter('legal.privacy'),
    terms: tFooter('legal.terms')
  }

  return (
    <main className="min-h-screen">
      <Header
        locale={locale}
        appName={appName}
        navigation={headerNavigation}
        actions={headerActions}
      />
      <Hero
        title={heroCopy.title}
        subtitle={heroCopy.subtitle}
        cta={heroCopy.cta}
      />
      <ProgramHighlights
        heading={programsCopy.heading}
        subheading={programsCopy.subheading}
        items={programsCopy.items}
      />
      <Footer
        locale={locale}
        sections={footerSections}
        legal={footerLegal}
      />
    </main>
  )
}
