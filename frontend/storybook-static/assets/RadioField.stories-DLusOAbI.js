import{j as y}from"./jsx-runtime-CmtfZKef.js";import{b as s}from"./radio-field-_jvVaVTC.js";import"./index-Dm8qopDP.js";import"./_commonjsHelpers-BosuxZz1.js";import"./index-B1McFu0M.js";import"./index-nna2CGTn.js";import"./index-LbsB4HyV.js";import"./index-B__5SPCB.js";import"./utils-CytzSlOG.js";import"./form-field-pR_88ZQs.js";import"./label-C8l1P-i-.js";const o={en:{label:"Preferred care plan",description:"Choose the holistic programme that best supports your family.",helper:"Plans can be adjusted with 7-day notice.",options:[{value:"day",label:"Day care",description:"Weekday day-time programme with meals and therapies."},{value:"respite",label:"Respite",description:"Short-term overnight stays to support caregivers."},{value:"memory",label:"Memory support",description:"Specialised cognitive engagement and supervision."}]},zh:{label:"首选护理方案",description:"选择最适合您家庭的综合服务方案。",helper:"方案可在七天通知内调整。",options:[{value:"day",label:"日间护理",description:"工作日提供膳食与治疗的白天托管。"},{value:"respite",label:"喘息护理",description:"短期夜宿服务，支持照护者。"},{value:"memory",label:"记忆辅导",description:"专门的认知训练与安全监督。"}]},ms:{label:"Pelan penjagaan pilihan",description:"Pilih program holistik yang paling membantu keluarga anda.",helper:"Pelan boleh diubah dengan notis 7 hari.",options:[{value:"day",label:"Penjagaan siang",description:"Program siang hari dengan hidangan dan terapi."},{value:"respite",label:"Rehat jelang",description:"Penginapan sementara malam untuk sokong penjaga."},{value:"memory",label:"Sokongan memori",description:"Rangsangan kognitif dan pengawasan khusus."}]},ta:{label:"விருப்பமான பராமரிப்பு திட்டம்",description:"உங்கள் குடும்பத்திற்கு ஏற்ற விரிவான திட்டத்தைத் தேர்வுசெய்க.",helper:"ஏழு நாள் முன் அறிவிப்பில் திட்டங்களை மாற்றலாம்.",options:[{value:"day",label:"பகல் பராமரிப்பு",description:"உணவும் சிகிச்சைகளும் சேர்த்த பகல் நேர திட்டம்."},{value:"respite",label:"இடைக்கால தங்கல்",description:"பராமரிப்பாளர்களுக்கு ஆதரவாக குறுகிய கால இரவு தங்கல்."},{value:"memory",label:"நினைவாற்றல் ஆதரவு",description:"சிறப்பு நினைவாற்றல் பயிற்சியும் கண்காணிப்பும்."}]}},b=r=>o[r??"en"]??o.en;var p;const T={title:"Molecules/RadioField",component:s,parameters:{layout:"padded"},args:{required:!0,label:o.en.label,description:o.en.description,helperText:o.en.helper,options:o.en.options,radioGroupProps:{defaultValue:(p=o.en.options[0])==null?void 0:p.value}},tags:["autodocs"]},a={args:{required:!0},render:(r,{globals:i})=>{var t;const e=b(i.locale);return y.jsx(s,{...r,label:e.label,description:e.description,helperText:e.helper,options:e.options,radioGroupProps:{...r.radioGroupProps,defaultValue:(t=e.options[0])==null?void 0:t.value}})}},n={args:{required:!0,errorMessage:"Select one plan to continue"},render:(r,{globals:i})=>{const e=b(i.locale);return y.jsx(s,{...r,label:e.label,description:e.description,helperText:e.helper,options:e.options,radioGroupProps:{...r.radioGroupProps,"aria-invalid":!0}})}};var l,d,c;a.parameters={...a.parameters,docs:{...(l=a.parameters)==null?void 0:l.docs,source:{originalSource:`{
  args: {
    required: true
  },
  render: (args, {
    globals
  }) => {
    const copy = resolveCopy(globals.locale as string);
    return <RadioField {...args} label={copy.label} description={copy.description} helperText={copy.helper} options={copy.options} radioGroupProps={{
      ...args.radioGroupProps,
      defaultValue: copy.options[0]?.value
    }} />;
  }
}`,...(c=(d=a.parameters)==null?void 0:d.docs)==null?void 0:c.source}}};var u,m,g;n.parameters={...n.parameters,docs:{...(u=n.parameters)==null?void 0:u.docs,source:{originalSource:`{
  args: {
    required: true,
    errorMessage: 'Select one plan to continue'
  },
  render: (args, {
    globals
  }) => {
    const copy = resolveCopy(globals.locale as string);
    return <RadioField {...args} label={copy.label} description={copy.description} helperText={copy.helper} options={copy.options} radioGroupProps={{
      ...args.radioGroupProps,
      'aria-invalid': true
    }} />;
  }
}`,...(g=(m=n.parameters)==null?void 0:m.docs)==null?void 0:g.source}}};const C=["Playground","WithError"];export{a as Playground,n as WithError,C as __namedExportsOrder,T as default};
