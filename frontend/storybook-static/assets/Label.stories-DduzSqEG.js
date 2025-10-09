import{j as i}from"./jsx-runtime-CmtfZKef.js";import{L as s}from"./label-C8l1P-i-.js";import"./index-Dm8qopDP.js";import"./_commonjsHelpers-BosuxZz1.js";import"./utils-CytzSlOG.js";const t={en:{default:"Primary caregiver contact",helper:"We will reach out between 9am–6pm SGT."},zh:{default:"主要照护者联络方式",helper:"我们会在新加坡时间上午9点至下午6点之间联系。"},ms:{default:"Hubungan penjaga utama",helper:"Kami akan menghubungi antara 9 pagi–6 petang SGT."},ta:{default:"முதன்மை பராமரிப்பாளர் தொடர்பு",helper:"நாங்கள் காலை 9 மணி முதல் மாலை 6 மணி வரை (SGT) தொடர்பு கொள்வோம்."}},g=e=>t[e??"en"]??t.en,L={title:"Atoms/Label",component:s,parameters:{layout:"padded"},args:{size:"md"},tags:["autodocs"]},a={render:(e,{globals:n})=>{const r=g(n.locale);return i.jsx(s,{...e,helperText:r.helper,requiredMarker:!0,children:r.default})}},o={args:{size:"sm"},render:(e,{globals:n})=>{const r=g(n.locale);return i.jsx(s,{...e,helperText:r.helper,children:r.default})}};var l,p,c;a.parameters={...a.parameters,docs:{...(l=a.parameters)==null?void 0:l.docs,source:{originalSource:`{
  render: (args, {
    globals
  }) => {
    const copy = resolveCopy(globals.locale as string);
    return <Label {...args} helperText={copy.helper} requiredMarker>
        {copy.default}
      </Label>;
  }
}`,...(c=(p=a.parameters)==null?void 0:p.docs)==null?void 0:c.source}}};var m,u,d;o.parameters={...o.parameters,docs:{...(m=o.parameters)==null?void 0:m.docs,source:{originalSource:`{
  args: {
    size: 'sm'
  },
  render: (args, {
    globals
  }) => {
    const copy = resolveCopy(globals.locale as string);
    return <Label {...args} helperText={copy.helper}>
        {copy.default}
      </Label>;
  }
}`,...(d=(u=o.parameters)==null?void 0:u.docs)==null?void 0:d.source}}};const S=["Playground","Small"];export{a as Playground,o as Small,S as __namedExportsOrder,L as default};
