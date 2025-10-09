import type { Meta, StoryObj } from '@storybook/react'

import { RadioField } from '@/components/forms/radio-field'

const copyByLocale = {
  en: {
    label: 'Preferred care plan',
    description: 'Choose the holistic programme that best supports your family.',
    helper: 'Plans can be adjusted with 7-day notice.',
    options: [
      { value: 'day', label: 'Day care', description: 'Weekday day-time programme with meals and therapies.' },
      { value: 'respite', label: 'Respite', description: 'Short-term overnight stays to support caregivers.' },
      { value: 'memory', label: 'Memory support', description: 'Specialised cognitive engagement and supervision.' }
    ]
  },
  zh: {
    label: '首选护理方案',
    description: '选择最适合您家庭的综合服务方案。',
    helper: '方案可在七天通知内调整。',
    options: [
      { value: 'day', label: '日间护理', description: '工作日提供膳食与治疗的白天托管。' },
      { value: 'respite', label: '喘息护理', description: '短期夜宿服务，支持照护者。' },
      { value: 'memory', label: '记忆辅导', description: '专门的认知训练与安全监督。' }
    ]
  },
  ms: {
    label: 'Pelan penjagaan pilihan',
    description: 'Pilih program holistik yang paling membantu keluarga anda.',
    helper: 'Pelan boleh diubah dengan notis 7 hari.',
    options: [
      { value: 'day', label: 'Penjagaan siang', description: 'Program siang hari dengan hidangan dan terapi.' },
      { value: 'respite', label: 'Rehat jelang', description: 'Penginapan sementara malam untuk sokong penjaga.' },
      { value: 'memory', label: 'Sokongan memori', description: 'Rangsangan kognitif dan pengawasan khusus.' }
    ]
  },
  ta: {
    label: 'விருப்பமான பராமரிப்பு திட்டம்',
    description: 'உங்கள் குடும்பத்திற்கு ஏற்ற விரிவான திட்டத்தைத் தேர்வுசெய்க.',
    helper: 'ஏழு நாள் முன் அறிவிப்பில் திட்டங்களை மாற்றலாம்.',
    options: [
      { value: 'day', label: 'பகல் பராமரிப்பு', description: 'உணவும் சிகிச்சைகளும் சேர்த்த பகல் நேர திட்டம்.' },
      { value: 'respite', label: 'இடைக்கால தங்கல்', description: 'பராமரிப்பாளர்களுக்கு ஆதரவாக குறுகிய கால இரவு தங்கல்.' },
      { value: 'memory', label: 'நினைவாற்றல் ஆதரவு', description: 'சிறப்பு நினைவாற்றல் பயிற்சியும் கண்காணிப்பும்.' }
    ]
  }
} satisfies Record<
  string,
  {
    label: string
    description: string
    helper: string
    options: { value: string; label: string; description: string }[]
  }
>

type Locale = keyof typeof copyByLocale

const resolveCopy = (locale: string | undefined) =>
  copyByLocale[(locale as Locale) ?? 'en'] ?? copyByLocale.en

const meta = {
  title: 'Molecules/RadioField',
  component: RadioField,
  parameters: {
    layout: 'padded'
  },
  args: {
    required: true,
    label: copyByLocale.en.label,
    description: copyByLocale.en.description,
    helperText: copyByLocale.en.helper,
    options: copyByLocale.en.options,
    radioGroupProps: { defaultValue: copyByLocale.en.options[0]?.value }
  },
  tags: ['autodocs']
} satisfies Meta<typeof RadioField>

export default meta

type Story = StoryObj<typeof meta>

export const Playground: Story = {
  args: {
    required: true
  },
  render: (args, { globals }) => {
    const copy = resolveCopy(globals.locale as string)

    return (
      <RadioField
        {...args}
        label={copy.label}
        description={copy.description}
        helperText={copy.helper}
        options={copy.options}
        radioGroupProps={{ ...args.radioGroupProps, defaultValue: copy.options[0]?.value }}
      />
    )
  }
}

export const WithError: Story = {
  args: {
    required: true,
    errorMessage: 'Select one plan to continue'
  },
  render: (args, { globals }) => {
    const copy = resolveCopy(globals.locale as string)

    return (
      <RadioField
        {...args}
        label={copy.label}
        description={copy.description}
        helperText={copy.helper}
        options={copy.options}
        radioGroupProps={{ ...args.radioGroupProps, 'aria-invalid': true }}
      />
    )
  }
}
