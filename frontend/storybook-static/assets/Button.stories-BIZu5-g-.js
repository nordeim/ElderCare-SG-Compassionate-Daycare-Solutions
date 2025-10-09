import{j as e}from"./jsx-runtime-CmtfZKef.js";import{B as o}from"./button-CfCZeQbC.js";import{c as k}from"./createLucideIcon-DES5Jjit.js";import"./index-Dm8qopDP.js";import"./_commonjsHelpers-BosuxZz1.js";import"./index-LbsB4HyV.js";import"./utils-CytzSlOG.js";/**
 * @license lucide-react v0.545.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const w=[["path",{d:"M5 12h14",key:"1ays0h"}],["path",{d:"m12 5 7 7-7 7",key:"xquz4c"}]],I=k("arrow-right",w);/**
 * @license lucide-react v0.545.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const L=[["path",{d:"M21 12a9 9 0 1 1-6.219-8.56",key:"13zald"}]],N=k("loader-circle",L),d={en:{primary:"Book a centre tour",secondary:"Explore services",loading:"Processing request"},zh:{primary:"预约参观中心",secondary:"探索服务",loading:"处理中"},ms:{primary:"Tempah lawatan pusat",secondary:"Teroka perkhidmatan",loading:"Sedang diproses"},ta:{primary:"மையத்தை பார்வையிட முன்பதிவு",secondary:"சேவைகளை ஆராயுங்கள்",loading:"செயலாக்கப்படுகிறது"}},i=a=>d[a??"en"]??d.en,P={title:"Atoms/Button",component:o,argTypes:{onClick:{action:"click"}},parameters:{controls:{exclude:["leftIcon","rightIcon"]}}},n={args:{children:d.en.primary},render:(a,{globals:s})=>{const r=i(s.locale);return e.jsx(o,{...a,children:r.primary})}},c={args:{variant:"secondary",rightIcon:e.jsx(I,{className:"h-4 w-4"})},render:(a,{globals:s})=>{const r=i(s.locale);return e.jsx(o,{...a,children:r.secondary})}},t={args:{isLoading:!0,leftIcon:e.jsx(N,{className:"h-4 w-4 animate-spin"})},render:(a,{globals:s})=>{const r=i(s.locale);return e.jsx(o,{...a,children:r.loading})}},l={name:"Theme sampler",parameters:{backgrounds:{default:"surface",values:[{name:"surface",value:"var(--color-surface-default)"},{name:"inverse",value:"var(--color-surface-inverse)"}]}},render:(a,{globals:s})=>{const r=i(s.locale);return e.jsxs("div",{className:"flex flex-col gap-4 sm:flex-row",children:[e.jsx(o,{children:r.primary}),e.jsx(o,{variant:"secondary",children:r.secondary}),e.jsx(o,{variant:"outline",children:r.secondary}),e.jsx(o,{variant:"ghost",children:r.secondary})]})}};var p,m,u;n.parameters={...n.parameters,docs:{...(p=n.parameters)==null?void 0:p.docs,source:{originalSource:`{
  args: {
    children: copyByLocale.en.primary
  } as Story['args'],
  render: (args, {
    globals
  }) => {
    const copy = resolveCopy(globals.locale as string);
    return <Button {...args}>{copy.primary}</Button>;
  }
}`,...(u=(m=n.parameters)==null?void 0:m.docs)==null?void 0:u.source}}};var g,y,h;c.parameters={...c.parameters,docs:{...(g=c.parameters)==null?void 0:g.docs,source:{originalSource:`{
  args: {
    variant: 'secondary',
    rightIcon: <ArrowRight className="h-4 w-4" />
  },
  render: (args, {
    globals
  }) => {
    const copy = resolveCopy(globals.locale as string);
    return <Button {...args}>{copy.secondary}</Button>;
  }
}`,...(h=(y=c.parameters)==null?void 0:y.docs)==null?void 0:h.source}}};var v,f,x;t.parameters={...t.parameters,docs:{...(v=t.parameters)==null?void 0:v.docs,source:{originalSource:`{
  args: {
    isLoading: true,
    leftIcon: <Loader2 className="h-4 w-4 animate-spin" />
  },
  render: (args, {
    globals
  }) => {
    const copy = resolveCopy(globals.locale as string);
    return <Button {...args}>{copy.loading}</Button>;
  }
}`,...(x=(f=t.parameters)==null?void 0:f.docs)==null?void 0:x.source}}};var B,j,b;l.parameters={...l.parameters,docs:{...(B=l.parameters)==null?void 0:B.docs,source:{originalSource:`{
  name: 'Theme sampler',
  parameters: {
    backgrounds: {
      default: 'surface',
      values: [{
        name: 'surface',
        value: 'var(--color-surface-default)'
      }, {
        name: 'inverse',
        value: 'var(--color-surface-inverse)'
      }]
    }
  },
  render: (_args, {
    globals
  }) => {
    const copy = resolveCopy(globals.locale as string);
    return <div className="flex flex-col gap-4 sm:flex-row">
        <Button>{copy.primary}</Button>
        <Button variant="secondary">{copy.secondary}</Button>
        <Button variant="outline">{copy.secondary}</Button>
        <Button variant="ghost">{copy.secondary}</Button>
      </div>;
  }
}`,...(b=(j=l.parameters)==null?void 0:j.docs)==null?void 0:b.source}}};const R=["Playground","WithIcons","Loading","ThemeSampler"];export{t as Loading,n as Playground,l as ThemeSampler,c as WithIcons,R as __namedExportsOrder,P as default};
