import{j as a}from"./jsx-runtime-CmtfZKef.js";import{B as l}from"./button-CfCZeQbC.js";import{C as i,a as x,b as g,c as v,d as C,e as h}from"./card-mGLnOj3V.js";import"./index-Dm8qopDP.js";import"./_commonjsHelpers-BosuxZz1.js";import"./index-LbsB4HyV.js";import"./utils-CytzSlOG.js";const n={en:{title:"Day Care Membership",description:"Personalised activities and health monitoring for seniors.",body:"Our multidisciplinary team coordinates mobility, nutrition, and cognitive engagement to keep loved ones thriving every weekday.",primary:"Book a centre tour",secondary:"Download brochure"},zh:{title:"日间护理计划",description:"为长者量身定制的活动与健康监测。",body:"跨专业团队协调行动能力、营养与认知训练，让家人每天都保持活力与笑容。",primary:"预约参观",secondary:"下载手册"},ms:{title:"Program Penjagaan Harian",description:"Aktiviti peribadi dan pemantauan kesihatan untuk warga emas.",body:"Pasukan multidisiplin kami menyelaraskan mobiliti, pemakanan dan rangsangan kognitif supaya orang tersayang kekal aktif setiap hari.",primary:"Tempah lawatan pusat",secondary:"Muat turun risalah"},ta:{title:"பகல் பராமரிப்பு திட்டம்",description:"மூத்தவர்களுக்கு தனிப்பட்ட செயற்பாடுகள் மற்றும் ஆரோக்கிய கண்காணிப்பு.",body:"நாங்கள் இயக்கம், ஊட்டச்சத்து, நினைவாற்றல் பயிற்சி ஆகியவற்றை ஒருங்கிணைக்கும் அணியை கொண்டு குடும்பத்தார் தினமும் உற்சாகமாக இருப்பதை உறுதி செய்கிறோம்.",primary:"மையத்தை பார்வையிட முன்பதிவு",secondary:"விளக்கப் புத்தகத்தை பதிவிறக்கவும்"}},D={title:"Molecules/Card",component:i,parameters:{layout:"padded",backgrounds:{default:"surface",values:[{name:"surface",value:"var(--color-surface-default)"},{name:"inverse",value:"var(--color-surface-inverse)"}]}},args:{className:"max-w-sm"},tags:["autodocs"]},b=r=>n[r??"en"]??n.en,s={name:"Overview",render:(r,{globals:t})=>{const e=b(t.locale);return a.jsxs(i,{...r,"aria-label":e.title,children:[a.jsxs(x,{children:[a.jsx(g,{children:e.title}),a.jsx(v,{children:e.description})]}),a.jsx(C,{children:a.jsx("p",{className:"text-[var(--color-text-secondary)] leading-relaxed",children:e.body})}),a.jsxs(h,{className:"flex flex-col gap-3 sm:flex-row",children:[a.jsx(l,{className:"w-full sm:flex-1",children:e.primary}),a.jsx(l,{variant:"outline",className:"w-full sm:flex-1",children:e.secondary})]})]})}},o={name:"Highlighted state",args:{className:"max-w-sm border-[var(--color-primary-200)] shadow-lg"},render:(r,{globals:t})=>{const e=b(t.locale);return a.jsxs(i,{...r,"aria-label":e.title,children:[a.jsxs(x,{children:[a.jsx(g,{className:"text-[var(--color-primary-600)]",children:e.title}),a.jsx(v,{children:e.description})]}),a.jsxs(C,{className:"space-y-3",children:[a.jsx("p",{className:"leading-relaxed text-[var(--color-text-secondary)]",children:e.body}),a.jsxs("div",{className:"rounded-md bg-[var(--color-surface-subtle)] p-3 text-sm",children:[a.jsx("strong",{className:"block text-[var(--color-text-primary)]",children:e.primary}),a.jsx("span",{className:"text-[var(--color-text-secondary)]",children:e.secondary})]})]}),a.jsx(h,{children:a.jsx(l,{variant:"secondary",className:"w-full",children:e.primary})})]})}};var c,d,m;s.parameters={...s.parameters,docs:{...(c=s.parameters)==null?void 0:c.docs,source:{originalSource:`{
  name: 'Overview',
  render: (args, {
    globals
  }) => {
    const copy = resolveCopy(globals.locale as string);
    return <Card {...args} aria-label={copy.title}>
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
      </Card>;
  }
}`,...(m=(d=s.parameters)==null?void 0:d.docs)==null?void 0:m.source}}};var p,y,u;o.parameters={...o.parameters,docs:{...(p=o.parameters)==null?void 0:p.docs,source:{originalSource:`{
  name: 'Highlighted state',
  args: {
    className: 'max-w-sm border-[var(--color-primary-200)] shadow-lg'
  },
  render: (args, {
    globals
  }) => {
    const copy = resolveCopy(globals.locale as string);
    return <Card {...args} aria-label={copy.title}>
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
      </Card>;
  }
}`,...(u=(y=o.parameters)==null?void 0:y.docs)==null?void 0:u.source}}};const O=["Overview","Highlighted"];export{o as Highlighted,s as Overview,O as __namedExportsOrder,D as default};
