import{j as e}from"./jsx-runtime-CmtfZKef.js";import"./index-Dm8qopDP.js";import"./_commonjsHelpers-BosuxZz1.js";const o=a=>Object.fromEntries(["50","100","200","300","400","500","600","700","800","900"].map(r=>[r,`var(--color-${a}-${r})`])),s={primary:o("primary"),secondary:o("secondary"),accent:o("accent"),neutral:o("neutral")},v={"surface-default":"var(--color-surface-default)","surface-subtle":"var(--color-surface-subtle)","surface-inverse":"var(--color-surface-inverse)","text-primary":"var(--color-text-primary)","text-secondary":"var(--color-text-secondary)","text-inverse":"var(--color-text-inverse)","border-default":"var(--color-border-default)","border-strong":"var(--color-border-strong)",success:"var(--color-success-500)",warning:"var(--color-warning-500)",danger:"var(--color-danger-500)",info:"var(--color-info-500)"},f={"font-family-base":"var(--font-family-base)","font-family-display":"var(--font-family-display)","font-size-xs":"var(--font-size-xs)","font-size-sm":"var(--font-size-sm)","font-size-base":"var(--font-size-base)","font-size-lg":"var(--font-size-lg)","font-size-xl":"var(--font-size-xl)","font-size-2xl":"var(--font-size-2xl)","font-size-3xl":"var(--font-size-3xl)","font-size-4xl":"var(--font-size-4xl)","font-size-5xl":"var(--font-size-5xl)","line-height-tight":"var(--line-height-tight)","line-height-snug":"var(--line-height-snug)","line-height-normal":"var(--line-height-normal)","line-height-relaxed":"var(--line-height-relaxed)"},y={"space-0":"var(--space-0)","space-1":"var(--space-1)","space-2":"var(--space-2)","space-3":"var(--space-3)","space-4":"var(--space-4)","space-5":"var(--space-5)","space-6":"var(--space-6)","space-7":"var(--space-7)","space-8":"var(--space-8)","space-9":"var(--space-9)","space-10":"var(--space-10)","space-12":"var(--space-12)","space-16":"var(--space-16)","space-20":"var(--space-20)","space-24":"var(--space-24)","space-28":"var(--space-28)","space-32":"var(--space-32)"},i=({title:a,children:r})=>e.jsxs("section",{style:{marginBottom:"2rem"},children:[e.jsx("h3",{style:{fontSize:"1.25rem",fontWeight:600,marginBottom:"0.75rem"},children:a}),r]}),t=({palette:a})=>e.jsx("div",{style:{display:"grid",gap:"0.75rem",gridTemplateColumns:"repeat(auto-fit, minmax(120px, 1fr))"},children:Object.entries(a).map(([r,d])=>e.jsxs("div",{style:{borderRadius:"var(--radius-md)",overflow:"hidden",border:"1px solid var(--color-border-default)",boxShadow:"var(--shadow-sm)"},children:[e.jsx("div",{style:{backgroundColor:d,height:"64px"}}),e.jsxs("dl",{style:{padding:"0.75rem",backgroundColor:"var(--color-surface-default)"},children:[e.jsx("dt",{style:{fontWeight:600,fontSize:"0.875rem"},children:r}),e.jsx("dd",{style:{fontFamily:"var(--font-family-base)",fontSize:"0.75rem",opacity:.75},children:d})]})]},r))}),g=()=>e.jsxs("table",{style:{width:"100%",borderCollapse:"separate",borderSpacing:0},children:[e.jsx("thead",{children:e.jsxs("tr",{children:[e.jsx("th",{style:n,children:"Token"}),e.jsx("th",{style:n,children:"Preview"}),e.jsx("th",{style:n,children:"Value"})]})}),e.jsx("tbody",{children:Object.entries(f).map(([a,r])=>e.jsxs("tr",{children:[e.jsx("td",{style:c,children:a}),e.jsx("td",{style:c,children:e.jsx("span",{style:{fontFamily:r.includes("display")?"var(--font-family-display)":"var(--font-family-base)",fontSize:r},children:"The quick brown fox"})}),e.jsx("td",{style:c,children:r})]},a))})]}),x=()=>e.jsx("div",{style:{display:"grid",gap:"0.75rem",gridTemplateColumns:"repeat(auto-fit, minmax(160px, 1fr))"},children:Object.entries(y).map(([a,r])=>e.jsxs("div",{style:b,children:[e.jsx("span",{style:{fontWeight:600},children:a}),e.jsx("span",{style:{fontFamily:"var(--font-family-base)",fontSize:"0.75rem",opacity:.75},children:r}),e.jsx("span",{"aria-hidden":"true",style:{display:"block",marginTop:"0.5rem",width:r,height:"0.5rem",borderRadius:"var(--radius-pill)",backgroundColor:"var(--color-primary-500)"}})]},a))}),n={textAlign:"left",padding:"0.75rem",backgroundColor:"var(--color-surface-subtle)",fontFamily:"var(--font-family-base)",fontSize:"0.875rem",borderBottom:"1px solid var(--color-border-strong)"},c={padding:"0.75rem",borderBottom:"1px solid var(--color-border-default)",verticalAlign:"top",fontFamily:"var(--font-family-base)",fontSize:"0.875rem"},b={padding:"1rem",border:"1px solid var(--color-border-default)",borderRadius:"var(--radius-md)",backgroundColor:"var(--color-surface-default)",boxShadow:"var(--shadow-xs)"},k={title:"Foundation/Tokens",parameters:{layout:"fullscreen",docs:{description:{page:"Design tokens drive spacing, color, typography, motion and elevation across the component library. Use the controls below to reference the semantic and scale tokens when building new components."}}}},l={render:()=>e.jsxs("article",{style:{padding:"2rem",background:"var(--color-surface-subtle)",minHeight:"100vh"},children:[e.jsx("h2",{style:{fontSize:"2rem",fontWeight:600,marginBottom:"1rem"},children:"Design Tokens Overview"}),e.jsx("p",{style:{maxWidth:"720px",marginBottom:"2rem",fontSize:"1rem",lineHeight:1.6},children:"The palettes and scales defined here are exported to Tailwind (`tailwind.config.ts`) and available as CSS variables in `design-tokens.css`. Switch themes via the Storybook toolbar to validate dark mode and high-contrast behavior."}),e.jsxs(i,{title:"Color Scales",children:[e.jsx(t,{palette:s.primary}),e.jsx("div",{style:{height:"1.5rem"}}),e.jsx(t,{palette:s.secondary}),e.jsx("div",{style:{height:"1.5rem"}}),e.jsx(t,{palette:s.accent}),e.jsx("div",{style:{height:"1.5rem"}}),e.jsx(t,{palette:s.neutral})]}),e.jsx(i,{title:"Semantic Colors",children:e.jsx(t,{palette:v})}),e.jsx(i,{title:"Typography",children:e.jsx(g,{})}),e.jsx(i,{title:"Spacing Scale",children:e.jsx(x,{})})]})};var p,h,m;l.parameters={...l.parameters,docs:{...(p=l.parameters)==null?void 0:p.docs,source:{originalSource:`{
  render: () => <article style={{
    padding: '2rem',
    background: 'var(--color-surface-subtle)',
    minHeight: '100vh'
  }}>
      <h2 style={{
      fontSize: '2rem',
      fontWeight: 600,
      marginBottom: '1rem'
    }}>Design Tokens Overview</h2>
      <p style={{
      maxWidth: '720px',
      marginBottom: '2rem',
      fontSize: '1rem',
      lineHeight: 1.6
    }}>
        The palettes and scales defined here are exported to Tailwind (\`tailwind.config.ts\`) and available as CSS variables in
        \`design-tokens.css\`. Switch themes via the Storybook toolbar to validate dark mode and high-contrast behavior.
      </p>

      <TokenSection title="Color Scales">
        <ColorSwatches palette={colorScales.primary} />
        <div style={{
        height: '1.5rem'
      }} />
        <ColorSwatches palette={colorScales.secondary} />
        <div style={{
        height: '1.5rem'
      }} />
        <ColorSwatches palette={colorScales.accent} />
        <div style={{
        height: '1.5rem'
      }} />
        <ColorSwatches palette={colorScales.neutral} />
      </TokenSection>

      <TokenSection title="Semantic Colors">
        <ColorSwatches palette={semanticColors} />
      </TokenSection>

      <TokenSection title="Typography">
        <TypographyTable />
      </TokenSection>

      <TokenSection title="Spacing Scale">
        <SpaceScale />
      </TokenSection>
    </article>
}`,...(m=(h=l.parameters)==null?void 0:h.docs)==null?void 0:m.source}}};const w=["Overview"];export{l as Overview,w as __namedExportsOrder,k as default};
