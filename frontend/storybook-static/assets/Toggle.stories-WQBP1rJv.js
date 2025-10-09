import{j as a}from"./jsx-runtime-CmtfZKef.js";import{r as h}from"./index-Dm8qopDP.js";import{a as _,P as j,c as I}from"./index-B1McFu0M.js";import{c as k}from"./utils-CytzSlOG.js";import{c as v}from"./createLucideIcon-DES5Jjit.js";import"./_commonjsHelpers-BosuxZz1.js";import"./index-nna2CGTn.js";import"./index-LbsB4HyV.js";/**
 * @license lucide-react v0.545.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const P=[["path",{d:"M11 4.702a.705.705 0 0 0-1.203-.498L6.413 7.587A1.4 1.4 0 0 1 5.416 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.416a1.4 1.4 0 0 1 .997.413l3.383 3.384A.705.705 0 0 0 11 19.298z",key:"uqj9uw"}],["path",{d:"M16 9a5 5 0 0 1 0 6",key:"1q6k2b"}],["path",{d:"M19.364 18.364a9 9 0 0 0 0-12.728",key:"ijwkga"}]],C=v("volume-2",P);/**
 * @license lucide-react v0.545.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const T=[["path",{d:"M11 4.702a.705.705 0 0 0-1.203-.498L6.413 7.587A1.4 1.4 0 0 1 5.416 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.416a1.4 1.4 0 0 1 .997.413l3.383 3.384A.705.705 0 0 0 11 19.298z",key:"uqj9uw"}],["line",{x1:"22",x2:"16",y1:"9",y2:"15",key:"1ewh16"}],["line",{x1:"16",x2:"22",y1:"9",y2:"15",key:"5ykzw1"}]],V=v("volume-x",T);var x="Toggle",b=h.forwardRef((e,n)=>{const{pressed:i,defaultPressed:l,onPressedChange:r,...c}=e,[d,w]=_({prop:i,onChange:r,defaultProp:l??!1,caller:x});return a.jsx(j.button,{type:"button","aria-pressed":d,"data-state":d?"on":"off","data-disabled":e.disabled?"":void 0,...c,ref:n,onClick:I(e.onClick,()=>{e.disabled||w(!d)})})});b.displayName=x;var N=b;const t=h.forwardRef(({className:e,pressedIcon:n,unpressedIcon:i,disabled:l,...r},c)=>a.jsxs(N,{ref:c,className:k("inline-flex h-9 min-w-[2.5rem] items-center justify-center gap-2 rounded-[var(--radius-md)] border border-[var(--color-border-default)] bg-[var(--color-surface-default)] px-3 py-2 text-[var(--color-text-primary)] transition-colors data-[state=on]:bg-[var(--color-primary-500)] data-[state=on]:text-[var(--color-text-inverse)] data-[state=on]:border-[var(--color-primary-500)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-primary-400)] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60",e),disabled:l,...r,children:[a.jsx("span",{className:"flex h-4 w-4 items-center justify-center",children:r["aria-pressed"]||r.pressed?n:i}),a.jsx("span",{className:"sr-only",children:"Toggle"})]}));t.displayName=N.displayName;try{t.displayName="Toggle",t.__docgenInfo={description:"",displayName:"Toggle",props:{pressedIcon:{defaultValue:null,description:"",name:"pressedIcon",required:!1,type:{name:"ReactNode"}},unpressedIcon:{defaultValue:null,description:"",name:"unpressedIcon",required:!1,type:{name:"ReactNode"}},asChild:{defaultValue:null,description:"",name:"asChild",required:!1,type:{name:"boolean"}}}}}catch{}const S={title:"Atoms/Toggle",component:t,args:{"aria-label":"Mute notifications"}},s={args:{children:"Notifications"}},o={args:{pressedIcon:a.jsx(V,{className:"h-4 w-4"}),unpressedIcon:a.jsx(C,{className:"h-4 w-4"})}};var p,m,u;s.parameters={...s.parameters,docs:{...(p=s.parameters)==null?void 0:p.docs,source:{originalSource:`{
  args: {
    children: 'Notifications'
  }
}`,...(u=(m=s.parameters)==null?void 0:m.docs)==null?void 0:u.source}}};var f,g,y;o.parameters={...o.parameters,docs:{...(f=o.parameters)==null?void 0:f.docs,source:{originalSource:`{
  args: {
    pressedIcon: <VolumeX className="h-4 w-4" />,
    unpressedIcon: <Volume2 className="h-4 w-4" />
  }
}`,...(y=(g=o.parameters)==null?void 0:g.docs)==null?void 0:y.source}}};const W=["Playground","WithIcons"];export{s as Playground,o as WithIcons,W as __namedExportsOrder,S as default};
