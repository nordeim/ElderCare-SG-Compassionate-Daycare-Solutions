import type { Meta, StoryObj } from '@storybook/react'
import React from 'react'

import ProgramHighlights from '@/components/sections/program-highlights'

const locales = ['en', 'zh', 'ms', 'ta'] as const

type Locale = (typeof locales)[number]

type ProgramCopy = {
  heading: string
  subheading: string
  items: {
    dayPrograms: {
      title: string
      description: string
    }
    wellness: {
      title: string
      description: string
    }
    familySupport: {
      title: string
      description: string
    }
  }
}

const copyByLocale: Record<Locale, ProgramCopy> = {
  en: {
    heading: 'Care programmes for every need',
    subheading: 'From weekday community care to allied-health wellness and family support, we tailor services to your goals.',
    items: {
      dayPrograms: {
        title: 'Weekday day programmes',
        description: 'Small-group therapy, social lunches, and caregiver feedback sessions.'
      },
      wellness: {
        title: 'Allied health wellness',
        description: 'Physio, occupational therapy, and nutrition planning supervised by clinicians.'
      },
      familySupport: {
        title: 'Family support & respite',
        description: 'Weekend respite bookings, transport coordination, and care coaching.'
      }
    }
  },
  zh: {
    heading: '满足不同需求的照护方案',
    subheading: '从平日托管、专业健康护理到家庭支援，我们依据目标量身规划服务。',
    items: {
      dayPrograms: {
        title: '平日托管课程',
        description: '小班治疗、社交午餐与照护者回馈时间。'
      },
      wellness: {
        title: '专业健康护理',
        description: '物理治疗、职能治疗与营养规划，由临床团队指导。'
      },
      familySupport: {
        title: '家庭支援与喘息服务',
        description: '周末喘息预约、交通接送协调与照护指导。'
      }
    }
  },
  ms: {
    heading: 'Program penjagaan untuk setiap keperluan',
    subheading: 'Daripada penjagaan komuniti hari bekerja hingga kesihatan sekutu dan sokongan keluarga, kami menyesuaikan perkhidmatan mengikut matlamat anda.',
    items: {
      dayPrograms: {
        title: 'Program harian hari bekerja',
        description: 'Terapi kumpulan kecil, makan tengah hari sosial dan sesi maklum balas penjaga.'
      },
      wellness: {
        title: 'Kesihatan profesional bersekutu',
        description: 'Fisioterapi, terapi carakerja dan perancangan pemakanan diawasi pakar.'
      },
      familySupport: {
        title: 'Sokongan keluarga & rehat',
        description: 'Tempahan rehat hujung minggu, koordinasi pengangkutan dan bimbingan penjagaan.'
      }
    }
  },
  ta: {
    heading: 'ஒவ்வொரு தேவைக்கும் பொருந்திய பராமரிப்பு திட்டங்கள்',
    subheading: 'வாரநாள் சமூக பராமரிப்பிலிருந்து மருத்துவ நலம் மற்றும் குடும்ப ஆதரவு வரை, உங்கள் இலக்குகளுக்கு ஏற்ப சேவைகள் திட்டமிடப்படுகின்றன.',
    items: {
      dayPrograms: {
        title: 'வாரநாள் பகல் திட்டங்கள்',
        description: 'சிறுகுழு சிகிச்சை, சமூக உணவு, பாதுகாவலர் கருத்துப் பகிர்வு அமர்வுகள்.'
      },
      wellness: {
        title: 'ஒட்டுமொத்த ஆரோக்கிய பராமரிப்பு',
        description: 'உடற்தெராபி, தொழில்சிகிச்சை, மற்றும் ஊட்டச்சத்து திட்டமிடல் மருத்துவ நிபுணர்கள் மேற்பார்வையில்.'
      },
      familySupport: {
        title: 'குடும்ப ஆதரவு & ஓய்வு',
        description: 'வார இறுதி ஓய்வு முன்பதிவு, போக்குவரத்து ஒழுங்குபடுத்தல், மற்றும் பராமரிப்பு பயிற்சி.'
      }
    }
  }
}

const isLocale = (value: unknown): value is Locale => typeof value === 'string' && locales.includes(value as Locale)

const resolveCopy = (locale: string | undefined) => (isLocale(locale) ? copyByLocale[locale] : copyByLocale.en)

const meta = {
  title: 'Sections/ProgramHighlights',
  component: ProgramHighlights,
  parameters: {
    layout: 'fullscreen'
  },
  tags: ['autodocs'],
  args: {
    heading: copyByLocale.en.heading,
    subheading: copyByLocale.en.subheading,
    items: copyByLocale.en.items
  }
} satisfies Meta<typeof ProgramHighlights>

export default meta

type Story = StoryObj<typeof meta>

export const Playground: Story = {
  render: (args, { globals }) => {
    const copy = resolveCopy(globals.locale as string)

    return <ProgramHighlights {...args} heading={copy.heading} subheading={copy.subheading} items={copy.items} />
  }
}
