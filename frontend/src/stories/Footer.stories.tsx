import type { Meta, StoryObj } from '@storybook/react'
import React from 'react'

import Footer from '@/components/layout/footer'
import type { Locale } from '@/lib/i18n/config'

const locales = ['en', 'zh', 'ms', 'ta'] as const

type LocaleKey = (typeof locales)[number]

type FooterCopy = {
  sections: {
    contactHeading: string
    newsletterHeading: string
    newsletterDescription: string
    newsletterPlaceholder: string
    newsletterCta: string
    certificationsHeading: string
    certifications: {
      moh: string
      iso: string
      eldercare: string
      sg: string
    }
  }
  legal: {
    rights: string
    privacy: string
    terms: string
  }
}

const copyByLocale: Record<LocaleKey, FooterCopy> = {
  en: {
    sections: {
      contactHeading: 'Contact us',
      newsletterHeading: 'Stay in touch',
      newsletterDescription: 'Monthly updates on programmes, caregiver workshops, and health tips.',
      newsletterPlaceholder: 'Your email address',
      newsletterCta: 'Subscribe',
      certificationsHeading: 'Licences & partnerships',
      certifications: {
        moh: 'MOH accredited provider',
        iso: 'ISO 9001 Care Quality',
        eldercare: 'Singapore Eldercare Alliance',
        sg: 'Support SG Programme'
      }
    },
    legal: {
      rights: '© 2025 ElderCare SG. All rights reserved.',
      privacy: 'Privacy policy',
      terms: 'Terms of use'
    }
  },
  zh: {
    sections: {
      contactHeading: '联系我们',
      newsletterHeading: '订阅资讯',
      newsletterDescription: '每月掌握课程、照护者讲座与健康贴士。',
      newsletterPlaceholder: '您的电子邮件',
      newsletterCta: '立即订阅',
      certificationsHeading: '认证与合作',
      certifications: {
        moh: '新加坡卫生部认证',
        iso: 'ISO 9001 质量体系',
        eldercare: '新加坡长者照护联盟',
        sg: '关怀新加坡计划'
      }
    },
    legal: {
      rights: '© 2025 ElderCare SG 版权所有。',
      privacy: '隐私政策',
      terms: '使用条款'
    }
  },
  ms: {
    sections: {
      contactHeading: 'Hubungi kami',
      newsletterHeading: 'Ikuti perkembangan',
      newsletterDescription: 'Kemas kini bulanan mengenai program, bengkel penjaga dan tip kesihatan.',
      newsletterPlaceholder: 'Alamat emel anda',
      newsletterCta: 'Langgan',
      certificationsHeading: 'Lesen & kerjasama',
      certifications: {
        moh: 'Penyedia bertauliah MOH',
        iso: 'ISO 9001 Kualiti Penjagaan',
        eldercare: 'Perikatan Penjagaan Warga Emas SG',
        sg: 'Program Sokongan SG'
      }
    },
    legal: {
      rights: '© 2025 ElderCare SG. Hak cipta terpelihara.',
      privacy: 'Dasar privasi',
      terms: 'Syarat penggunaan'
    }
  },
  ta: {
    sections: {
      contactHeading: 'எங்களை தொடர்பு கொள்ளவும்',
      newsletterHeading: 'செய்திகளைப் பெறுங்கள்',
      newsletterDescription: 'நிகழ்ச்சிகள், பராமரிப்பாளர் பயிலரங்குகள் மற்றும் உடல்நல குறிப்புகள் பற்றிய மாதாந்திர புதுப்பிப்புகள்.',
      newsletterPlaceholder: 'உங்கள் மின்னஞ்சல் முகவரி',
      newsletterCta: 'சந்தாகொள்ளவும்',
      certificationsHeading: 'அங்கீகாரங்கள் & கூட்டாண்மைகள்',
      certifications: {
        moh: 'MOH அங்கீகாரம் பெற்ற நிறுவனம்',
        iso: 'ISO 9001 தர மேலாண்மை',
        eldercare: 'சிங்கப்பூர் முதியோர் பராமரிப்பு கூட்டணி',
        sg: 'ஸ்போர்ட் SG திட்டம்'
      }
    },
    legal: {
      rights: '© 2025 ElderCare SG. அனைத்து உரிமைகளும் பாதுகாக்கப்பட்டவை.',
      privacy: 'தனியுரிமைக் கொள்கை',
      terms: 'பயன்பாட்டு விதிமுறைகள்'
    }
  }
}

const isLocale = (value: unknown): value is LocaleKey => typeof value === 'string' && locales.includes(value as LocaleKey)

const resolveCopy = (locale: string | undefined) => (isLocale(locale) ? copyByLocale[locale] : copyByLocale.en)

const meta = {
  title: 'Sections/Footer',
  component: Footer,
  parameters: {
    layout: 'fullscreen'
  },
  tags: ['autodocs'],
  args: {
    locale: 'en' as Locale,
    sections: copyByLocale.en.sections,
    legal: copyByLocale.en.legal
  }
} satisfies Meta<typeof Footer>

export default meta

type Story = StoryObj<typeof meta>

export const Playground: Story = {
  render: (args, { globals }) => {
    const copy = resolveCopy(globals.locale as string)
    const locale = (isLocale(globals.locale as string) ? (globals.locale as Locale) : args.locale) as Locale

    return <Footer {...args} locale={locale} sections={copy.sections} legal={copy.legal} />
  }
}
