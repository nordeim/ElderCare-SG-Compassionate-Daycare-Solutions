import{j as l}from"./jsx-runtime-CmtfZKef.js";import{R as u}from"./index-Dm8qopDP.js";import{F as c}from"./form-field-pR_88ZQs.js";import{I as g}from"./input-DgKb6-oJ.js";import"./_commonjsHelpers-BosuxZz1.js";import"./label-C8l1P-i-.js";import"./utils-CytzSlOG.js";const b=["en","zh","ms","ta"],t={en:{label:"Resident contact number",description:"We will only call for care coordination updates.",helper:"Include country code for overseas caregivers.",placeholder:"+65 9876 5432",error:"Please provide a valid phone number."},zh:{label:"住户联系电话",description:"仅用于护理协调的必要通知。",helper:"若家属在海外，请加上国家代码。",placeholder:"+65 9876 5432",error:"请输入有效电话号码。"},ms:{label:"Nombor telefon penghuni",description:"Kami akan menghubungi hanya untuk kemas kini koordinasi penjagaan.",helper:"Sertakan kod negara untuk penjaga di luar negara.",placeholder:"+65 9876 5432",error:"Sila berikan nombor telefon yang sah."},ta:{label:"வாழ்வோர் தொடர்பு எண்",description:"பாதுகாப்பு ஒருங்கிணைப்பு அறிவிப்புகளுக்கு மட்டுமே அழைப்போம்.",helper:"வெளிநாட்டில் உள்ள பாதுகாவலர்களுக்கு நாட்டுக் குறியீட்டை சேர்க்கவும்.",placeholder:"+65 9876 5432",error:"சரியான தொலைபேசி எண்ணை உள்ளிடவும்."}},F=e=>typeof e=="string"&&b.includes(e),y=e=>F(e)?t[e]:t.en,q={title:"Molecules/FormField",component:c,parameters:{layout:"padded"},tags:["autodocs"],args:{required:!1,children:l.jsx(g,{})}},o={render:(e,{globals:n})=>{const r=y(n.locale);return l.jsx(c,{...e,label:r.label,description:r.description,helperText:r.helper,children:u.cloneElement(e.children,{placeholder:r.placeholder})})}},a={args:{required:!0,children:l.jsx(g,{"aria-invalid":!0,placeholder:""})},render:(e,{globals:n})=>{const r=y(n.locale);return l.jsx(c,{...e,label:r.label,description:r.description,helperText:r.helper,errorMessage:r.error,children:u.cloneElement(e.children,{placeholder:r.placeholder,"aria-invalid":!0})})}};var s,i,p;o.parameters={...o.parameters,docs:{...(s=o.parameters)==null?void 0:s.docs,source:{originalSource:`{
  render: (args, {
    globals
  }) => {
    const copy = resolveCopy(globals.locale as string);
    return <FormField {...args} label={copy.label} description={copy.description} helperText={copy.helper}>
        {React.cloneElement(args.children as React.ReactElement, {
        placeholder: copy.placeholder
      })}
      </FormField>;
  }
}`,...(p=(i=o.parameters)==null?void 0:i.docs)==null?void 0:p.source}}};var d,m,h;a.parameters={...a.parameters,docs:{...(d=a.parameters)==null?void 0:d.docs,source:{originalSource:`{
  args: {
    required: true,
    children: <Input aria-invalid placeholder="" />
  },
  render: (args, {
    globals
  }) => {
    const copy = resolveCopy(globals.locale as string);
    return <FormField {...args} label={copy.label} description={copy.description} helperText={copy.helper} errorMessage={copy.error}>
        {React.cloneElement(args.children as React.ReactElement, {
        placeholder: copy.placeholder,
        'aria-invalid': true
      })}
      </FormField>;
  }
}`,...(h=(m=a.parameters)==null?void 0:m.docs)==null?void 0:h.source}}};const I=["Playground","RequiredWithError"];export{o as Playground,a as RequiredWithError,I as __namedExportsOrder,q as default};
