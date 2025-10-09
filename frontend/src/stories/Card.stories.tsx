import type { Meta, StoryObj } from '@storybook/react'
import { Button } from '@/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle
} from '@/components/ui/card'

const copyByLocale = {
  en: {
    title: 'Day Care Membership',
    description: 'Personalised activities and health monitoring for seniors.',
    body: 'Our multidisciplinary team coordinates mobility, nutrition, and cognitive engagement to keep loved ones thriving every weekday.',
    primary: 'Book a centre tour',
    secondary: 'Download brochure'
  },
  zh: {
    title: '日间护理计划',
    description: '为长者量身定制的活动与健康监测。',
    body: '跨专业团队协调行动能力、营养与认知训练，让家人每天都保持活力与笑容。',
    primary: '预约参观',
    secondary: '下载手册'
  },
  ms: {
    title: 'Program Penjagaan Harian',
    description: 'Aktiviti peribadi dan pemantauan kesihatan untuk warga emas.',
    body: 'Pasukan multidisiplin kami menyelaraskan mobiliti, pemakanan dan rangsangan kognitif supaya orang tersayang kekal aktif setiap hari.',
    primary: 'Tempah lawatan pusat',
    secondary: 'Muat turun risalah'
  },
  ta: {
    title: 'பகல் பராமரிப்பு திட்டம்',
    description: 'மூத்தவர்களுக்கு தனிப்பட்ட செயற்பாடுகள் மற்றும் ஆரோக்கிய கண்காணிப்பு.',
    body: 'நாங்கள் இயக்கம், ஊட்டச்சத்து, நினைவாற்றல் பயிற்சி ஆகியவற்றை ஒருங்கிணைக்கும் அணியை கொண்டு குடும்பத்தார் தினமும் உற்சாகமாக இருப்பதை உறுதி செய்கிறோம்.',
    primary: 'மையத்தை பார்வையிட முன்பதிவு',
    secondary: 'விளக்கப் புத்தகத்தை பதிவிறக்கவும்'
  }
} satisfies Record<string, { title: string; description: string; body: string; primary: string; secondary: string }>

type Locale = keyof typeof copyByLocale

const meta = {
  title: 'Molecules/Card',
  component: Card,
  parameters: {
    layout: 'padded',
    backgrounds: {
      default: 'surface',
      values: [
        { name: 'surface', value: 'var(--color-surface-default)' },
        { name: 'inverse', value: 'var(--color-surface-inverse)' }
      ]
    }
  },
  args: {
    className: 'max-w-sm'
  },
  tags: ['autodocs']
} satisfies Meta<typeof Card>

type Story = StoryObj<typeof meta>

const resolveCopy = (locale: string | undefined): (typeof copyByLocale)[Locale] =>
  copyByLocale[(locale as Locale) ?? 'en'] ?? copyByLocale.en

export const Overview: Story = {
  name: 'Overview',
  render: (args, { globals }) => {
    const copy = resolveCopy(globals.locale as string)

    return (
      <Card {...args} aria-label={copy.title}>
        <CardHeader>
          <CardTitle>{copy.title}</CardTitle>
          <CardDescription>{copy.description}</CardDescription>
        </CardHeader>
        <CardContent>
          <p className="text-[var(--color-text-secondary)] leading-relaxed">{copy.body}</p>
        </CardContent>
        <CardFooter className="flex flex-col gap-3 sm:flex-row">
          <Button className="w-full sm:flex-1">{copy.primary}</Button>
          <Button variant="outline" className="w-full sm:flex-1">
            {copy.secondary}
          </Button>
        </CardFooter>
      </Card>
    )
  }
}

export const Highlighted: Story = {
  name: 'Highlighted state',
  args: {
    className: 'max-w-sm border-[var(--color-primary-200)] shadow-lg'
  },
  render: (args, { globals }) => {
    const copy = resolveCopy(globals.locale as string)

    return (
      <Card {...args} aria-label={copy.title}>
        <CardHeader>
          <CardTitle className="text-[var(--color-primary-600)]">{copy.title}</CardTitle>
          <CardDescription>{copy.description}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-3">
          <p className="leading-relaxed text-[var(--color-text-secondary)]">{copy.body}</p>
          <div className="rounded-md bg-[var(--color-surface-subtle)] p-3 text-sm">
            <strong className="block text-[var(--color-text-primary)]">{copy.primary}</strong>
            <span className="text-[var(--color-text-secondary)]">
              {copy.secondary}
            </span>
          </div>
        </CardContent>
        <CardFooter>
          <Button variant="secondary" className="w-full">
            {copy.primary}
          </Button>
        </CardFooter>
      </Card>
    )
  }
}

export default meta
