import{j as l}from"./jsx-runtime-CmtfZKef.js";import{F as s}from"./form-field-pR_88ZQs.js";import{I as h}from"./input-DgKb6-oJ.js";import"./index-Dm8qopDP.js";import"./_commonjsHelpers-BosuxZz1.js";import"./label-C8l1P-i-.js";import"./utils-CytzSlOG.js";const p={en:{label:"Resident contact number",description:"We will only call for care coordination updates.",helper:"Include country code for overseas caregivers.",placeholder:"+65 9876 5432",error:"Please provide a valid phone number."},zh:{label:"住户联系电话",description:"仅用于护理协调的必要通知。",helper:"若家属在海外，请加上国家代码。",placeholder:"+65 9876 5432",error:"请输入有效电话号码。"},ms:{label:"Nombor telefon penghuni",description:"Kami akan menghubungi hanya untuk kemas kini koordinasi penjagaan.",helper:"Sertakan kod negara untuk penjaga di luar negara.",placeholder:"+65 9876 5432",error:"Sila berikan nombor telefon yang sah."},ta:{label:"வாழ்வோர் தொடர்பு எண்",description:"பாதுகாப்பு ஒருங்கிணைப்பு அறிவிப்புகளுக்கு மட்டுமே அழைப்போம்.",helper:"வெளிநாட்டில் உள்ள பாதுகாவலர்களுக்கு நாட்டுக் குறியீட்டை சேர்க்கவும்.",placeholder:"+65 9876 5432",error:"சரியான தொலைபேசி எண்ணை உள்ளிடவும்."}},g=r=>p[r??"en"]??p.en,j={title:"Molecules/FormField",component:s,parameters:{layout:"padded"},tags:["autodocs"],args:{required:!1}},o={render:(r,{globals:n})=>{const e=g(n.locale);return l.jsx(s,{...r,label:e.label,description:e.description,helperText:e.helper,children:l.jsx(h,{placeholder:e.placeholder})})}},a={args:{required:!0},render:(r,{globals:n})=>{const e=g(n.locale);return l.jsx(s,{...r,label:e.label,description:e.description,helperText:e.helper,errorMessage:e.error,children:l.jsx(h,{placeholder:e.placeholder,"aria-invalid":!0})})}};var t,c,i;o.parameters={...o.parameters,docs:{...(t=o.parameters)==null?void 0:t.docs,source:{originalSource:`{
  render: (args, {
    globals
  }) => {
    const copy = resolveCopy(globals.locale as string);
    return <FormField {...args} label={copy.label} description={copy.description} helperText={copy.helper}>
        <Input placeholder={copy.placeholder} />
      </FormField>;
  }
}`,...(i=(c=o.parameters)==null?void 0:c.docs)==null?void 0:i.source}}};var d,u,m;a.parameters={...a.parameters,docs:{...(d=a.parameters)==null?void 0:d.docs,source:{originalSource:`{
  args: {
    required: true
  },
  render: (args, {
    globals
  }) => {
    const copy = resolveCopy(globals.locale as string);
    return <FormField {...args} label={copy.label} description={copy.description} helperText={copy.helper} errorMessage={copy.error}>
        <Input placeholder={copy.placeholder} aria-invalid />
      </FormField>;
  }
}`,...(m=(u=a.parameters)==null?void 0:u.docs)==null?void 0:m.source}}};const q=["Playground","RequiredWithError"];export{o as Playground,a as RequiredWithError,q as __namedExportsOrder,j as default};
