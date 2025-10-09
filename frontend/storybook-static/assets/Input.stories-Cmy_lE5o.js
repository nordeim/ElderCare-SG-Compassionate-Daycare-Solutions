import{r as e}from"./index-Dm8qopDP.js";import{c as i}from"./utils-CytzSlOG.js";import{L as _}from"./label-CckRZuQa.js";import{c as E}from"./createLucideIcon-DES5Jjit.js";import"./_commonjsHelpers-BosuxZz1.js";/**
 * @license lucide-react v0.545.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const k=[["path",{d:"m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7",key:"132q7q"}],["rect",{x:"2",y:"4",width:"20",height:"16",rx:"2",key:"izxlao"}]],w=E("mail",k),s=e.forwardRef(({className:r,type:g="text",startAdornment:l,endAdornment:n,isInvalid:c=!1,...h},N)=>{const I=i("flex h-10 w-full rounded-[var(--radius-md)] border border-[var(--color-border-default)] bg-[var(--color-surface-default)] px-3 py-2 text-[var(--font-size-base)] text-[var(--color-text-primary)] placeholder:text-[var(--color-text-secondary)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-400)] focus-visible:ring-offset-0 transition-colors disabled:cursor-not-allowed disabled:opacity-60",{"border-[var(--color-danger-500)] focus-visible:ring-[var(--color-danger-500)]":c},r);return e.createElement("div",{className:"relative flex items-center"},l&&e.createElement("span",{className:"pointer-events-none absolute inset-y-0 left-3 flex items-center text-[var(--color-text-secondary)]"},l),e.createElement("input",{type:g,className:i(I,{"pl-9":!!l}),ref:N,"aria-invalid":c||void 0,...h}),n&&e.createElement("span",{className:"absolute inset-y-0 right-3 flex items-center text-[var(--color-text-secondary)]"},n))});s.displayName="Input";try{s.displayName="Input",s.__docgenInfo={description:"",displayName:"Input",props:{startAdornment:{defaultValue:null,description:"",name:"startAdornment",required:!1,type:{name:"ReactNode"}},endAdornment:{defaultValue:null,description:"",name:"endAdornment",required:!1,type:{name:"ReactNode"}},isInvalid:{defaultValue:{value:"false"},description:"",name:"isInvalid",required:!1,type:{name:"boolean"}}}}}catch{}const V={title:"Atoms/Input",component:s,decorators:[r=>React.createElement("div",{className:"flex max-w-sm flex-col gap-2"},React.createElement(_,{htmlFor:"storybook-input",helperText:"We will never share your email."},"Email address"),React.createElement(r,null))]},a={args:{id:"storybook-input",placeholder:"you@example.com"}},o={args:{id:"storybook-input-adorned",placeholder:"you@example.com",startAdornment:React.createElement(w,{className:"h-4 w-4"})}},t={args:{id:"storybook-input-invalid",placeholder:"you@example.com",isInvalid:!0}};var d,m,p;a.parameters={...a.parameters,docs:{...(d=a.parameters)==null?void 0:d.docs,source:{originalSource:`{
  args: {
    id: 'storybook-input',
    placeholder: 'you@example.com'
  }
}`,...(p=(m=a.parameters)==null?void 0:m.docs)==null?void 0:p.source}}};var u,y,f;o.parameters={...o.parameters,docs:{...(u=o.parameters)==null?void 0:u.docs,source:{originalSource:`{
  args: {
    id: 'storybook-input-adorned',
    placeholder: 'you@example.com',
    startAdornment: <Mail className="h-4 w-4" />
  }
}`,...(f=(y=o.parameters)==null?void 0:y.docs)==null?void 0:f.source}}};var v,x,b;t.parameters={...t.parameters,docs:{...(v=t.parameters)==null?void 0:v.docs,source:{originalSource:`{
  args: {
    id: 'storybook-input-invalid',
    placeholder: 'you@example.com',
    isInvalid: true
  }
}`,...(b=(x=t.parameters)==null?void 0:x.docs)==null?void 0:b.source}}};const W=["Playground","WithAdornment","Invalid"];export{t as Invalid,a as Playground,o as WithAdornment,W as __namedExportsOrder,V as default};
