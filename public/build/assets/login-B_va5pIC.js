import{r as d,j as e,m as A,L as B}from"./app-DOUPXsiX.js";import{L as w,I}from"./label-CsoCwPiC.js";import{T as N}from"./text-link-CAyoiYdB.js";import{u as z,c as M,B as O}from"./app-logo-icon-CsPC_L6o.js";import{u as T,c as H,a as P,P as K,b as X}from"./index-BU479ty5.js";import{u as $}from"./index-C7y8VOSW.js";import{P as R}from"./index-CsK4GaqG.js";import{C as G}from"./check-BqeGN2GX.js";import{I as L}from"./input-Bbm0gJlk.js";import{A as J,L as Q}from"./auth-layout-Cy2hT8GH.js";var y="Checkbox",[U,ue]=H(y),[V,W]=U(y),S=d.forwardRef((t,n)=>{const{__scopeCheckbox:r,name:o,checked:p,defaultChecked:s,required:l,disabled:i,value:x="on",onCheckedChange:c,form:h,...v}=t,[m,k]=d.useState(null),C=z(n,a=>k(a)),g=d.useRef(!1),E=m?h||!!m.closest("form"):!0,[f=!1,j]=T({prop:p,defaultProp:s,onChange:c}),q=d.useRef(f);return d.useEffect(()=>{const a=m==null?void 0:m.form;if(a){const b=()=>j(q.current);return a.addEventListener("reset",b),()=>a.removeEventListener("reset",b)}},[m,j]),e.jsxs(V,{scope:r,state:f,disabled:i,children:[e.jsx(R.button,{type:"button",role:"checkbox","aria-checked":u(f)?"mixed":f,"aria-required":l,"data-state":_(f),"data-disabled":i?"":void 0,disabled:i,value:x,...v,ref:C,onKeyDown:P(t.onKeyDown,a=>{a.key==="Enter"&&a.preventDefault()}),onClick:P(t.onClick,a=>{j(b=>u(b)?!0:!b),E&&(g.current=a.isPropagationStopped(),g.current||a.stopPropagation())})}),E&&e.jsx(Y,{control:m,bubbles:!g.current,name:o,value:x,checked:f,required:l,disabled:i,form:h,style:{transform:"translateX(-100%)"},defaultChecked:u(s)?!1:s})]})});S.displayName=y;var D="CheckboxIndicator",F=d.forwardRef((t,n)=>{const{__scopeCheckbox:r,forceMount:o,...p}=t,s=W(D,r);return e.jsx(K,{present:o||u(s.state)||s.state===!0,children:e.jsx(R.span,{"data-state":_(s.state),"data-disabled":s.disabled?"":void 0,...p,ref:n,style:{pointerEvents:"none",...t.style}})})});F.displayName=D;var Y=t=>{const{control:n,checked:r,bubbles:o=!0,defaultChecked:p,...s}=t,l=d.useRef(null),i=$(r),x=X(n);d.useEffect(()=>{const h=l.current,v=window.HTMLInputElement.prototype,k=Object.getOwnPropertyDescriptor(v,"checked").set;if(i!==r&&k){const C=new Event("click",{bubbles:o});h.indeterminate=u(r),k.call(h,u(r)?!1:r),h.dispatchEvent(C)}},[i,r,o]);const c=d.useRef(u(r)?!1:r);return e.jsx("input",{type:"checkbox","aria-hidden":!0,defaultChecked:p??c.current,...s,tabIndex:-1,ref:l,style:{...t.style,...x,position:"absolute",pointerEvents:"none",opacity:0,margin:0}})};function u(t){return t==="indeterminate"}function _(t){return u(t)?"indeterminate":t?"checked":"unchecked"}var Z=S,ee=F;function te({className:t,...n}){return e.jsx(Z,{"data-slot":"checkbox",className:M("peer border-input data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground data-[state=checked]:border-primary focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive size-4 shrink-0 rounded-[4px] border shadow-xs transition-shadow outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50",t),...n,children:e.jsx(ee,{"data-slot":"checkbox-indicator",className:"flex items-center justify-center text-current transition-none",children:e.jsx(G,{className:"size-3.5"})})})}function pe({status:t,canResetPassword:n}){const{data:r,setData:o,post:p,processing:s,errors:l,reset:i}=A({email:"",password:"",remember:!1}),x=c=>{c.preventDefault(),p(route("login"),{onFinish:()=>i("password")})};return e.jsxs(J,{title:"Log in to your account",description:"Enter your email and password below to log in",children:[e.jsx(B,{title:"Log in"}),e.jsxs("form",{className:"flex flex-col gap-6",onSubmit:x,children:[e.jsxs("div",{className:"grid gap-6",children:[e.jsxs("div",{className:"grid gap-2",children:[e.jsx(w,{htmlFor:"email",children:"Email address"}),e.jsx(L,{id:"email",type:"email",required:!0,autoFocus:!0,tabIndex:1,autoComplete:"email",value:r.email,onChange:c=>o("email",c.target.value),placeholder:"email@example.com"}),e.jsx(I,{message:l.email})]}),e.jsxs("div",{className:"grid gap-2",children:[e.jsxs("div",{className:"flex items-center",children:[e.jsx(w,{htmlFor:"password",children:"Password"}),n&&e.jsx(N,{href:route("password.request"),className:"ml-auto text-sm",tabIndex:5,children:"Forgot password?"})]}),e.jsx(L,{id:"password",type:"password",required:!0,tabIndex:2,autoComplete:"current-password",value:r.password,onChange:c=>o("password",c.target.value),placeholder:"Password"}),e.jsx(I,{message:l.password})]}),e.jsxs("div",{className:"flex items-center space-x-3",children:[e.jsx(te,{id:"remember",name:"remember",checked:r.remember,onClick:()=>o("remember",!r.remember),tabIndex:3}),e.jsx(w,{htmlFor:"remember",children:"Remember me"})]}),e.jsxs(O,{type:"submit",className:"mt-4 w-full",tabIndex:4,disabled:s,children:[s&&e.jsx(Q,{className:"h-4 w-4 animate-spin"}),"Log in"]})]}),e.jsxs("div",{className:"text-muted-foreground text-center text-sm",children:["Don't have an account?"," ",e.jsx(N,{href:route("register"),tabIndex:5,children:"Sign up"})]})]}),t&&e.jsx("div",{className:"mb-4 text-center text-sm font-medium text-green-600",children:t})]})}export{pe as default};
