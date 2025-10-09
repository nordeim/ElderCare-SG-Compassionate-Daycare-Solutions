import type { Meta, StoryObj } from '@storybook/react'
import React from 'react'

import { FormField } from '@/components/forms/form-field'
import { Input } from '@/components/ui/input'

const copyByLocale = {
  en: {
    label: 'Resident contact number',
    description: 'We will only call for care coordination updates.',
    helper: 'Include country code for overseas caregivers.',
    placeholder: '+65 9876 5432',
    error: 'Please provide a valid phone number.'
  },
  zh: {
    label: '住户联系电话',
    description: '仅用于护理协调的必要通知。',
    helper: '若家属在海外，请加上国家代码。',
    placeholder: '+65 9876 5432',
    error: '请输入有效电话号码。'
  },
  ms: {
    label: 'Nombor telefon penghuni',
    description: 'Kami akan menghubungi hanya untuk kemas kini koordinasi penjagaan.',
    helper: 'Sertakan kod negara untuk penjaga di luar negara.',
    placeholder: '+65 9876 5432',
    error: 'Sila berikan nombor telefon yang sah.'
  },
  ta: {
    label: 'வாழ்வோர் தொடர்பு எண்',
    description: 'பாதுகாப்பு ஒருங்கிணைப்பு அறிவிப்புகளுக்கு மட்டுமே அழைப்போம்.',
    helper: 'வெளிநாட்டில் உள்ள பாதுகாவலர்களுக்கு நாட்டுக் குறியீட்டை சேர்க்கவும்.',
    placeholder: '+65 9876 5432',
    error: 'சரியான தொலைபேசி எண்ணை உள்ளிடவும்.'
  }
} satisfies Record<string, { label: string; description: string; helper: string; placeholder: string; error: string }>

const resolveCopy = (locale: string | undefined) => copyByLocale[locale ?? 'en'] ?? copyByLocale.en

const meta = {
  title: 'Molecules/FormField',
  component: FormField,
  parameters: {
    layout: 'padded'
  },
  tags: ['autodocs'],
  args: {
    required: false
  }
} satisfies Meta<typeof FormField>

export default meta

type Story = StoryObj<typeof meta>

export const Playground: Story = {
  render: (args, { globals }) => {
    const copy = resolveCopy(globals.locale as string)

    return (
      <FormField
        {...args}
        label={copy.label}
        description={copy.description}
        helperText={copy.helper}
      >
        <Input placeholder={copy.placeholder} />
      </FormField>
    )
  }
}

export const RequiredWithError: Story = {
  args: {
    required: true
  },
  render: (args, { globals }) => {
    const copy = resolveCopy(globals.locale as string)

    return (
      <FormField
        {...args}
        label={copy.label}
        description={copy.description}
        helperText={copy.helper}
        errorMessage={copy.error}
      >
        <Input placeholder={copy.placeholder} aria-invalid />
      </FormField>
    )
  }
}
