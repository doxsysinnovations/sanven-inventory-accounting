import{r as l,m as se,j as e,L as le}from"./app-DOUPXsiX.js";import{A as ae}from"./app-layout-PY3kmcwW.js";import{I as p}from"./input-Bbm0gJlk.js";import{S as b,a as y,b as C,c as w,d as r}from"./select-Cgyte8c0.js";import{T as te,a as ie,b as D,c,d as ne,e as t}from"./table-BLRovgye.js";import{B as o}from"./app-logo-icon-CsPC_L6o.js";import{U as re}from"./layout-BZQUm4lG.js";import{H as ce}from"./heading-small-BPxtPpy9.js";import{D as E,a as oe,b as F,c as k,d as O,e as de,f as me}from"./dialog-G8VnJk0B.js";import{L as he,y as U}from"./ReactToastify-CBCxvM_K.js";import{S as xe}from"./status-switch-Du_LaTWg.js";import"./index-BU479ty5.js";import"./index-CsK4GaqG.js";import"./index-C7y8VOSW.js";import"./check-BqeGN2GX.js";const ue=[{title:"Manage Users",href:"/users"}];function Ie({users:H,roles:A}){const[f,P]=l.useState(""),[L,je]=l.useState("all"),[v,_]=l.useState("all"),[h,V]=l.useState("all"),[B,x]=l.useState(!1),[M,u]=l.useState(!1),[z,T]=l.useState(null),[j,g]=l.useState(null),[I,d]=l.useState(null),{data:S,setData:m,post:q,put:G,processing:J,errors:n,reset:N,delete:K}=se({name:"",email:"",role:[],picture:null}),Q=s=>{s.preventDefault(),j?G(route("users.update",j.id),{onSuccess:()=>{x(!1),N(),g(null),d(null),U.success("User updated successfully")}}):q(route("users.store"),{onSuccess:()=>{x(!1),N(),d(null),U.success("User created successfully")}})},W=s=>{g(s),m({name:s.name,email:s.email,role:s.roles,picture:null}),d(s.picture_url),x(!0)},X=s=>{T(s),u(!0)},Y=()=>{K(route("users.destroy",z.id),{onSuccess:()=>{u(!1),T(null),U.success("User deleted successfully")}})},Z=s=>{const i=s.target.files[0];if(i){m("picture",i);const a=new FileReader;a.onloadend=()=>{d(a.result)},a.readAsDataURL(i)}},R=H.filter(s=>{const i=s.name.toLowerCase().includes(f.toLowerCase())||s.email.toLowerCase().includes(f.toLowerCase()),a=L==="all"||s.status.toLowerCase()===L,$=v==="all"||s.role===v,ee=h==="all"||h==="active"&&s.is_active||h==="inactive"&&!s.is_active;return i&&a&&$&&ee});return e.jsxs(ae,{breadcrumbs:ue,children:[e.jsx(le,{title:"Manage Users"}),e.jsxs(re,{children:[e.jsx(he,{}),e.jsxs("div",{className:"space-y-6",children:[e.jsx(ce,{title:"Users List",description:"Manage system users here"}),e.jsxs("div",{className:"p-6 space-y-6",children:[e.jsxs("div",{className:"flex flex-wrap items-center justify-between gap-4",children:[e.jsx(p,{type:"text",placeholder:"Search users...",value:f,onChange:s=>P(s.target.value),className:"w-1/3"}),e.jsxs("div",{className:"flex gap-4",children:[e.jsxs(b,{value:v,onValueChange:s=>_(s),children:[e.jsx(y,{className:"w-[180px]",children:e.jsx(C,{placeholder:"Filter by Role"})}),e.jsxs(w,{children:[e.jsx(r,{value:"all",children:"All Roles"}),A.map(s=>e.jsx(r,{value:s.name,children:s.name},s.id))]})]}),e.jsxs(b,{value:h,onValueChange:s=>V(s),children:[e.jsx(y,{className:"w-[180px]",children:e.jsx(C,{placeholder:"Filter by Status"})}),e.jsxs(w,{children:[e.jsx(r,{value:"all",children:"All Statuses"}),e.jsx(r,{value:"active",children:"Active"}),e.jsx(r,{value:"inactive",children:"Inactive"})]})]})]}),e.jsxs(E,{open:B,onOpenChange:x,children:[e.jsx(oe,{asChild:!0,children:e.jsx(o,{onClick:()=>{g(null),d(null),N()},children:"Add User"})}),e.jsxs(F,{className:"max-w-md",children:[e.jsx(k,{children:e.jsx(O,{children:j?"Edit User":"Add New User"})}),e.jsxs("form",{onSubmit:Q,className:"space-y-4",children:[e.jsxs("div",{className:"space-y-2",children:[e.jsx("label",{className:"block text-sm font-medium",children:"Profile Picture"}),e.jsxs("div",{className:"flex items-center space-x-4",children:[I&&e.jsx("img",{src:I,alt:"Preview",className:"w-20 h-20 rounded-full object-cover"}),e.jsx(p,{type:"file",accept:"image/*",onChange:Z})]})]}),e.jsx(p,{placeholder:"Full name",value:S.name,onChange:s=>m("name",s.target.value)}),n.name&&e.jsx("div",{className:"text-red-500 text-sm",children:n.name}),e.jsx(p,{type:"email",placeholder:"Email address",value:S.email,onChange:s=>m("email",s.target.value)}),n.email&&e.jsx("div",{className:"text-red-500 text-sm",children:n.email}),e.jsxs(b,{value:S.role,onValueChange:s=>m("role",s),multiple:!0,children:[e.jsx(y,{children:e.jsx(C,{placeholder:"Select roles"})}),e.jsx(w,{children:A.map(s=>e.jsx(r,{value:s.name,children:s.name},s.id))})]}),n.role&&e.jsx("div",{className:"text-red-500 text-sm",children:n.role}),e.jsx(o,{type:"submit",disabled:J,children:j?"Update":"Create"})]})]})]})]}),e.jsx(E,{open:M,onOpenChange:u,children:e.jsxs(F,{children:[e.jsxs(k,{children:[e.jsx(O,{children:"Delete User"}),e.jsx(de,{children:"Are you sure you want to delete this user? This action cannot be undone."})]}),e.jsxs(me,{children:[e.jsx(o,{variant:"outline",onClick:()=>u(!1),children:"Cancel"}),e.jsx(o,{variant:"destructive",onClick:Y,children:"Delete"})]})]})}),e.jsx("div",{className:"border rounded-xl overflow-hidden shadow",children:e.jsxs(te,{children:[e.jsx(ie,{children:e.jsxs(D,{className:"bg-gray-100",children:[e.jsx(c,{className:"font-semibold",children:"Picture"}),e.jsx(c,{className:"font-semibold",children:"Name"}),e.jsx(c,{className:"font-semibold",children:"Email"}),e.jsx(c,{className:"font-semibold",children:"Role"}),e.jsx(c,{className:"font-semibold",children:"Status"}),e.jsx(c,{className:"font-semibold text-center w-[10%]",children:"Action"})]})}),e.jsx(ne,{children:R.length>0?R.map(s=>e.jsxs(D,{children:[e.jsx(t,{children:e.jsx("img",{src:s.picture_url||"/default-avatar.png",alt:s.name,className:"w-10 h-10 rounded-full object-cover"})}),e.jsx(t,{children:s.name}),e.jsx(t,{children:s.email}),e.jsx(t,{children:e.jsx("span",{className:"px-2 py-1 rounded-full text-xs",children:s.roles.map((i,a)=>e.jsxs("span",{className:"mr-1",children:[i.name,a<s.roles.length-1&&", "]},a))})}),e.jsx(t,{children:e.jsx(t,{children:e.jsx(xe,{model:"User",recordId:s.id,initialStatus:s.is_active})})}),e.jsx(t,{className:"text-right",children:e.jsxs("div",{className:"flex gap-2 justify-end",children:[e.jsx(o,{variant:"outline",size:"sm",onClick:()=>W(s),children:"Edit"}),e.jsx(o,{variant:"destructive",size:"sm",onClick:()=>X(s),children:"Delete"})]})})]},s.id)):e.jsx(D,{children:e.jsx(t,{colSpan:"6",className:"text-center py-4",children:"No users found."})})})]})})]})]})]})]})}export{Ie as default};
