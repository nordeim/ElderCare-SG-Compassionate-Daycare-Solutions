import{j as e}from"./jsx-runtime-CmtfZKef.js";import{r as _}from"./index-Dm8qopDP.js";import{c}from"./utils-CytzSlOG.js";import{L as j}from"./label-C8l1P-i-.js";import{c as k}from"./createLucideIcon-DES5Jjit.js";import"./_commonjsHelpers-BosuxZz1.js";/**
 * @license lucide-react v0.545.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const w=[["path",{d:"m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7",key:"132q7q"}],["rect",{x:"2",y:"4",width:"20",height:"16",rx:"2",key:"izxlao"}]],A=k("mail",w),t=_.forwardRef(({className:r,type:b="text",startAdornment:n,endAdornment:l,isInvalid:i=!1,...g},N)=>{const I=c("flex h-10 w-full rounded-[var(--radius-md)] border border-[var(--color-border-default)] bg-[var(--color-surface-default)] px-3 py-2 text-[var(--font-size-base)] text-[var(--color-text-primary)] placeholder:text-[var(--color-text-secondary)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-400)] focus-visible:ring-offset-0 transition-colors disabled:cursor-not-allowed disabled:opacity-60",{"border-[var(--color-danger-500)] focus-visible:ring-[var(--color-danger-500)]":i},r);return e.jsxs("div",{className:"relative flex items-center",children:[n&&e.jsx("span",{className:"pointer-events-none absolute inset-y-0 left-3 flex items-center text-[var(--color-text-secondary)]",children:n}),e.jsx("input",{type:b,className:c(I,{"pl-9":!!n}),ref:N,"aria-invalid":i||void 0,...g}),l&&e.jsx("span",{className:"absolute inset-y-0 right-3 flex items-center text-[var(--color-text-secondary)]",children:l})]})});t.displayName="Input";try{t.displayName="Input",t.__docgenInfo={description:"",displayName:"Input",props:{startAdornment:{defaultValue:null,description:"",name:"startAdornment",required:!1,type:{name:"ReactNode"}},endAdornment:{defaultValue:null,description:"",name:"endAdornment",required:!1,type:{name:"ReactNode"}},isInvalid:{defaultValue:{value:"false"},description:"",name:"isInvalid",required:!1,type:{name:"boolean"}}}}}catch{}const W={title:"Atoms/Input",component:t,decorators:[r=>e.jsxs("div",{className:"flex max-w-sm flex-col gap-2",children:[e.jsx(j,{htmlFor:"storybook-input",helperText:"We will never share your email.",children:"Email address"}),e.jsx(r,{})]})]},o={args:{id:"storybook-input",placeholder:"you@example.com"}},a={args:{id:"storybook-input-adorned",placeholder:"you@example.com",startAdornment:e.jsx(A,{className:"h-4 w-4"})}},s={args:{id:"storybook-input-invalid",placeholder:"you@example.com",isInvalid:!0}};var d,p,m;o.parameters={...o.parameters,docs:{...(d=o.parameters)==null?void 0:d.docs,source:{originalSource:`{
  args: {
    id: 'storybook-input',
    placeholder: 'you@example.com'
  }
}`,...(m=(p=o.parameters)==null?void 0:p.docs)==null?void 0:m.source}}};var u,x,f;a.parameters={...a.parameters,docs:{...(u=a.parameters)==null?void 0:u.docs,source:{originalSource:`{
  args: {
    id: 'storybook-input-adorned',
    placeholder: 'you@example.com',
    startAdornment: <Mail className="h-4 w-4" />
  }
}`,...(f=(x=a.parameters)==null?void 0:x.docs)==null?void 0:f.source}}};var y,v,h;s.parameters={...s.parameters,docs:{...(y=s.parameters)==null?void 0:y.docs,source:{originalSource:`{
  args: {
    id: 'storybook-input-invalid',
    placeholder: 'you@example.com',
    isInvalid: true
  }
}`,...(h=(v=s.parameters)==null?void 0:v.docs)==null?void 0:h.source}}};const z=["Playground","WithAdornment","Invalid"];export{s as Invalid,o as Playground,a as WithAdornment,z as __namedExportsOrder,W as default};
