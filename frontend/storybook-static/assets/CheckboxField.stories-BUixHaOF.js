import{j as m}from"./jsx-runtime-CmtfZKef.js";import{a as t}from"./checkbox-field-BFmh-v3o.js";import"./index-Dm8qopDP.js";import"./_commonjsHelpers-BosuxZz1.js";import"./index-LbsB4HyV.js";import"./form-field-CUUVDR4N.js";import"./index-oz-OHGNu.js";import"./index-nna2CGTn.js";import"./label-C8l1P-i-.js";import"./utils-CytzSlOG.js";import"./createLucideIcon-DES5Jjit.js";const n={en:{label:"Fall prevention support",description:"Daily supervised mobility exercises and home safety reviews.",helper:"Recommended for residents with previous fall incidents."},zh:{label:"防跌倒支持",description:"每日监督的活动训练与居家安全评估。",helper:"曾经跌倒的长者建议加入。"},ms:{label:"Sokongan pencegahan jatuh",description:"Senaman mobiliti diawasi setiap hari dan semakan keselamatan rumah.",helper:"Disyorkan bagi warga emas yang pernah jatuh."},ta:{label:"விழுந்தலை தடுக்கும் உதவி",description:"தினசரி கண்காணிப்பில் இயக்க பயிற்சிகள் மற்றும் இல்ல பாதுகாப்பு மதிப்பீடுகள்.",helper:"முன்பு விழுந்த மூத்தவர்களுக்கு பரிந்துரைக்கப்படுகிறது."}},h=r=>n[r??"en"]??n.en,S={title:"Molecules/CheckboxField",component:t,parameters:{layout:"padded"},args:{required:!1},tags:["autodocs"]},o={render:(r,{globals:s})=>{const e=h(s.locale);return m.jsx(t,{...r,label:e.label,description:e.description,helperText:e.helper,checkboxProps:{defaultChecked:!0}})}},a={args:{required:!0,errorMessage:"Selection required"},render:(r,{globals:s})=>{const e=h(s.locale);return m.jsx(t,{...r,label:e.label,description:e.description,helperText:e.helper,checkboxProps:{"aria-invalid":!0}})}};var i,l,p;o.parameters={...o.parameters,docs:{...(i=o.parameters)==null?void 0:i.docs,source:{originalSource:`{
  render: (args, {
    globals
  }) => {
    const copy = resolveCopy(globals.locale as string);
    return <CheckboxField {...args} label={copy.label} description={copy.description} helperText={copy.helper} checkboxProps={{
      defaultChecked: true
    }} />;
  }
}`,...(p=(l=o.parameters)==null?void 0:l.docs)==null?void 0:p.source}}};var c,d,u;a.parameters={...a.parameters,docs:{...(c=a.parameters)==null?void 0:c.docs,source:{originalSource:`{
  args: {
    required: true,
    errorMessage: 'Selection required'
  },
  render: (args, {
    globals
  }) => {
    const copy = resolveCopy(globals.locale as string);
    return <CheckboxField {...args} label={copy.label} description={copy.description} helperText={copy.helper} checkboxProps={{
      'aria-invalid': true
    }} />;
  }
}`,...(u=(d=a.parameters)==null?void 0:d.docs)==null?void 0:u.source}}};const F=["Playground","RequiredWithError"];export{o as Playground,a as RequiredWithError,F as __namedExportsOrder,S as default};
