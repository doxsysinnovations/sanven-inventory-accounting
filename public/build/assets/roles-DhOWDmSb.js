import{r as i,m as J,j as e,L as K}from"./app-DOUPXsiX.js";import{A as Q}from"./app-layout-PY3kmcwW.js";import{I as D}from"./input-Bbm0gJlk.js";import{T as V,a as W,b as u,c as p,d as X,e as m}from"./table-BLRovgye.js";import{B as r}from"./app-logo-icon-CsPC_L6o.js";import{U as Y}from"./layout-BZQUm4lG.js";import{H as Z}from"./heading-small-BPxtPpy9.js";import{D as C,a as _,b as S,c as R,d as w,e as $,f as ee}from"./dialog-G8VnJk0B.js";import{L as se,y as j}from"./ReactToastify-CBCxvM_K.js";import"./index-BU479ty5.js";import"./index-CsK4GaqG.js";const le=[{title:"Manage Roles",href:"/roles"}];function pe({roles:A,permissions:f}){const[g,T]=i.useState(""),[b,ae]=i.useState("all"),[k,c]=i.useState(!1),[L,o]=i.useState(!1),[P,v]=i.useState(null),[d,h]=i.useState(null),{data:n,setData:t,post:O,put:E,processing:H,errors:N,reset:x,delete:I}=J({name:"",permissions:[],giveAllPermissions:!1}),z=s=>{const a=n.permissions.includes(s)?n.permissions.filter(l=>l!==s):[...n.permissions,s];t("permissions",a)},B=s=>{const a=s.target.checked;t("giveAllPermissions",a),a?t("permissions",f.map(l=>l.id)):t("permissions",[])},U=s=>{s.preventDefault(),d?E(route("roles.update",d.id),{onSuccess:()=>{c(!1),x(),h(null),j.success("Role updated successfully")}}):O(route("roles.store"),{onSuccess:()=>{c(!1),x(),j.success("Role created successfully")}})},G=s=>{h(s),t("name",s.name),t("permissions",s.permissions.map(a=>a.id)),c(!0)},M=s=>{v(s),o(!0)},F=()=>{I(route("roles.destroy",P.id),{onSuccess:()=>{o(!1),v(null),j.success("Role deleted successfully")}})},q=f.reduce((s,a)=>{const[l]=a.name.split(".");return s[l]||(s[l]=[]),s[l].push(a),s},{}),y=A.filter(s=>{const a=s.name.toLowerCase().includes(g.toLowerCase()),l=b==="all"||s.status.toLowerCase()===b;return a&&l});return e.jsxs(Q,{breadcrumbs:le,children:[e.jsx(K,{title:"Manage Roles"}),e.jsxs(Y,{children:[e.jsx(se,{}),e.jsxs("div",{className:"space-y-6",children:[e.jsx(Z,{title:"Roles Lists",description:"See all roles here"}),e.jsxs("div",{className:"p-6 space-y-6",children:[e.jsxs("div",{className:"flex flex-wrap items-center justify-between gap-4",children:[e.jsx(D,{type:"text",placeholder:"Search roles...",value:g,onChange:s=>T(s.target.value),className:"w-1/3"}),e.jsxs(C,{open:k,onOpenChange:c,children:[e.jsx(_,{asChild:!0,children:e.jsx(r,{onClick:()=>{h(null),x()},children:"Add Role"})}),e.jsxs(S,{className:"max-w-4xl",children:[e.jsx(R,{children:e.jsx(w,{children:d?"Edit Role":"Add New Role"})}),e.jsxs("form",{onSubmit:U,className:"space-y-4",children:[e.jsx(D,{placeholder:"Role name",value:n.name,onChange:s=>t("name",s.target.value)}),N.name&&e.jsx("div",{className:"text-red-500 text-sm",children:N.name}),e.jsx("div",{children:e.jsxs("label",{className:"flex items-center space-x-2",children:[e.jsx("input",{type:"checkbox",checked:n.giveAllPermissions,onChange:B,className:"rounded border-gray-300"}),e.jsx("span",{children:"Give All Permissions"})]})}),e.jsx("div",{className:"grid grid-cols-3 gap-6",children:Object.entries(q).map(([s,a])=>e.jsxs("div",{className:"space-y-2",children:[e.jsx("h3",{className:"font-semibold capitalize",children:s}),a.map(l=>e.jsx("div",{className:"ml-2",children:e.jsxs("label",{className:"flex items-center space-x-2",children:[e.jsx("input",{type:"checkbox",checked:n.permissions.includes(l.id),onChange:()=>z(l.id),className:"rounded border-gray-300"}),e.jsx("span",{children:l.name.split(".")[1]})]})},l.id))]},s))}),e.jsx(r,{type:"submit",disabled:H,children:d?"Update":"Create"})]})]})]})]}),e.jsx(C,{open:L,onOpenChange:o,children:e.jsxs(S,{children:[e.jsxs(R,{children:[e.jsx(w,{children:"Delete Role"}),e.jsx($,{children:"Are you sure you want to delete this role? This action cannot be undone."})]}),e.jsxs(ee,{children:[e.jsx(r,{variant:"outline",onClick:()=>o(!1),children:"Cancel"}),e.jsx(r,{variant:"destructive",onClick:F,children:"Delete"})]})]})}),e.jsx("div",{className:"border rounded-xl overflow-hidden shadow",children:e.jsxs(V,{children:[e.jsx(W,{children:e.jsxs(u,{className:"bg-gray-100",children:[e.jsx(p,{className:"font-semibold",children:"ID"}),e.jsx(p,{className:"font-semibold",children:"Name"}),e.jsx(p,{className:"font-semibold text-center w-[10%]",children:"Action"})]})}),e.jsx(X,{children:y.length>0?y.map(s=>e.jsxs(u,{children:[e.jsx(m,{children:s.id}),e.jsx(m,{children:s.name}),e.jsx(m,{className:"text-right",children:e.jsxs("div",{className:"flex gap-2 justify-end",children:[e.jsx(r,{variant:"outline",size:"sm",onClick:()=>G(s),children:"Edit"}),e.jsx(r,{variant:"destructive",size:"sm",onClick:()=>M(s),children:"Delete"})]})})]},s.id)):e.jsx(u,{children:e.jsx(m,{colSpan:"4",className:"text-center py-4",children:"No roles found."})})})]})})]})]})]})]})}export{pe as default};
