import type { Meta, StoryObj } from '@storybook/react'

import { CheckboxField } from '@/components/forms/checkbox-field'

const copyByLocale = {
  en: {
    label: 'Fall prevention support',
    description: 'Daily supervised mobility exercises and home safety reviews.',
    helper: 'Recommended for residents with previous fall incidents.'
  },
  zh: {
    label: '防跌倒支持',
    description: '每日监督的活动训练与居家安全评估。',
    helper: '曾经跌倒的长者建议加入。'
  },
  ms: {
    label: 'Sokongan pencegahan jatuh',
    description: 'Senaman mobiliti diawasi setiap hari dan semakan keselamatan rumah.',
    helper: 'Disyorkan bagi warga emas yang pernah jatuh.'
  },
  ta: {
    label: 'விழுந்தலை தடுக்கும் உதவி',
    description: 'தினசரி கண்காணிப்பில் இயக்க பயிற்சிகள் மற்றும் இல்ல பாதுகாப்பு மதிப்பீடுகள்.',
    helper: 'முன்பு விழுந்த மூத்தவர்களுக்கு பரிந்துரைக்கப்படுகிறது.'
  }
} satisfies Record<string, { label: string; description: string; helper: string }>

type Locale = keyof typeof copyByLocale

const resolveCopy = (locale: string | undefined): (typeof copyByLocale)[Locale] =>
  copyByLocale[(locale as Locale) ?? 'en'] ?? copyByLocale.en

const meta = {
  title: 'Molecules/CheckboxField',
  component: CheckboxField,
  parameters: {
    layout: 'padded'
  },
  args: {
    required: false
  },
  tags: ['autodocs']
} satisfies Meta<typeof CheckboxField>

export default meta

type Story = StoryObj<typeof meta>

export const Playground: Story = {
  render: (args, { globals }) => {
    const copy = resolveCopy(globals.locale as string)

    return (
      <CheckboxField
        {...args}
        label={copy.label}
        description={copy.description}
        helperText={copy.helper}
        checkboxProps={{ defaultChecked: true }}
      />
    )
  }
}

export const RequiredWithError: Story = {
  args: {
    required: true,
    errorMessage: 'Selection required'
  },
  render: (args, { globals }) => {
    const copy = resolveCopy(globals.locale as string)

    return (
      <CheckboxField
        {...args}
        label={copy.label}
        description={copy.description}
        helperText={copy.helper}
        checkboxProps={{ 'aria-invalid': true }}
      />
    )
  }
}
