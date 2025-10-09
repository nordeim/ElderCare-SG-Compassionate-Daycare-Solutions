import{j as a}from"./jsx-runtime-CmtfZKef.js";import{B as x}from"./button-Mrj10rhP.js";import{r as t}from"./index-Dm8qopDP.js";import{c as d}from"./utils-CytzSlOG.js";import"./index-LbsB4HyV.js";import"./_commonjsHelpers-BosuxZz1.js";const o=t.forwardRef(({className:r,...s},e)=>a.jsx("div",{ref:e,className:d("rounded-lg border bg-card text-card-foreground shadow-sm",r),...s}));o.displayName="Card";const c=t.forwardRef(({className:r,...s},e)=>a.jsx("div",{ref:e,className:d("flex flex-col space-y-1.5 p-6",r),...s}));c.displayName="CardHeader";const i=t.forwardRef(({className:r,...s},e)=>a.jsx("h3",{ref:e,className:d("text-2xl font-semibold leading-none tracking-tight",r),...s}));i.displayName="CardTitle";const l=t.forwardRef(({className:r,...s},e)=>a.jsx("p",{ref:e,className:d("text-sm text-muted-foreground",r),...s}));l.displayName="CardDescription";const n=t.forwardRef(({className:r,...s},e)=>a.jsx("div",{ref:e,className:d("p-6 pt-0",r),...s}));n.displayName="CardContent";const p=t.forwardRef(({className:r,...s},e)=>a.jsx("div",{ref:e,className:d("flex items-center p-6 pt-0",r),...s}));p.displayName="CardFooter";try{o.displayName="Card",o.__docgenInfo={description:"",displayName:"Card",props:{}}}catch{}try{c.displayName="CardHeader",c.__docgenInfo={description:"",displayName:"CardHeader",props:{}}}catch{}try{p.displayName="CardFooter",p.__docgenInfo={description:"",displayName:"CardFooter",props:{}}}catch{}try{i.displayName="CardTitle",i.__docgenInfo={description:"",displayName:"CardTitle",props:{}}}catch{}try{l.displayName="CardDescription",l.__docgenInfo={description:"",displayName:"CardDescription",props:{}}}catch{}try{n.displayName="CardContent",n.__docgenInfo={description:"",displayName:"CardContent",props:{}}}catch{}const g={en:{title:"Day Care Membership",description:"Personalised activities and health monitoring for seniors.",body:"Our multidisciplinary team coordinates mobility, nutrition, and cognitive engagement to keep loved ones thriving every weekday.",primary:"Book a centre tour",secondary:"Download brochure"},zh:{title:"日间护理计划",description:"为长者量身定制的活动与健康监测。",body:"跨专业团队协调行动能力、营养与认知训练，让家人每天都保持活力与笑容。",primary:"预约参观",secondary:"下载手册"},ms:{title:"Program Penjagaan Harian",description:"Aktiviti peribadi dan pemantauan kesihatan untuk warga emas.",body:"Pasukan multidisiplin kami menyelaraskan mobiliti, pemakanan dan rangsangan kognitif supaya orang tersayang kekal aktif setiap hari.",primary:"Tempah lawatan pusat",secondary:"Muat turun risalah"},ta:{title:"பகல் பராமரிப்பு திட்டம்",description:"மூத்தவர்களுக்கு தனிப்பட்ட செயற்பாடுகள் மற்றும் ஆரோக்கிய கண்காணிப்பு.",body:"நாங்கள் இயக்கம், ஊட்டச்சத்து, நினைவாற்றல் பயிற்சி ஆகியவற்றை ஒருங்கிணைக்கும் அணியை கொண்டு குடும்பத்தார் தினமும் உற்சாகமாக இருப்பதை உறுதி செய்கிறோம்.",primary:"மையத்தை பார்வையிட முன்பதிவு",secondary:"விளக்கப் புத்தகத்தை பதிவிறக்கவும்"}},D={title:"Molecules/Card",component:o,parameters:{layout:"padded",backgrounds:{default:"surface",values:[{name:"surface",value:"var(--color-surface-default)"},{name:"inverse",value:"var(--color-surface-inverse)"}]}},args:{className:"max-w-sm"},tags:["autodocs"]},N=r=>g[r??"en"]??g.en,m={name:"Overview",render:(r,{globals:s})=>{const e=N(s.locale);return a.jsxs(o,{...r,"aria-label":e.title,children:[a.jsxs(c,{children:[a.jsx(i,{children:e.title}),a.jsx(l,{children:e.description})]}),a.jsx(n,{children:a.jsx("p",{className:"text-[var(--color-text-secondary)] leading-relaxed",children:e.body})}),a.jsxs(p,{className:"flex flex-col gap-3 sm:flex-row",children:[a.jsx(x,{className:"w-full sm:flex-1",children:e.primary}),a.jsx(x,{variant:"outline",className:"w-full sm:flex-1",children:e.secondary})]})]})}},y={name:"Highlighted state",args:{className:"max-w-sm border-[var(--color-primary-200)] shadow-lg"},render:(r,{globals:s})=>{const e=N(s.locale);return a.jsxs(o,{...r,"aria-label":e.title,children:[a.jsxs(c,{children:[a.jsx(i,{className:"text-[var(--color-primary-600)]",children:e.title}),a.jsx(l,{children:e.description})]}),a.jsxs(n,{className:"space-y-3",children:[a.jsx("p",{className:"leading-relaxed text-[var(--color-text-secondary)]",children:e.body}),a.jsxs("div",{className:"rounded-md bg-[var(--color-surface-subtle)] p-3 text-sm",children:[a.jsx("strong",{className:"block text-[var(--color-text-primary)]",children:e.primary}),a.jsx("span",{className:"text-[var(--color-text-secondary)]",children:e.secondary})]})]}),a.jsx(p,{children:a.jsx(x,{variant:"secondary",className:"w-full",children:e.primary})})]})}};var u,C,f;m.parameters={...m.parameters,docs:{...(u=m.parameters)==null?void 0:u.docs,source:{originalSource:`{
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
}`,...(f=(C=m.parameters)==null?void 0:C.docs)==null?void 0:f.source}}};var _,h,v;y.parameters={...y.parameters,docs:{...(_=y.parameters)==null?void 0:_.docs,source:{originalSource:`{
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
}`,...(v=(h=y.parameters)==null?void 0:h.docs)==null?void 0:v.source}}};const T=["Overview","Highlighted"];export{y as Highlighted,m as Overview,T as __namedExportsOrder,D as default};
