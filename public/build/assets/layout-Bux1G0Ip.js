import{j as s,$ as t}from"./app-DOUPXsiX.js";import{B as r,c}from"./app-logo-icon-CsPC_L6o.js";import{S as i}from"./heading-small-BPxtPpy9.js";const n=[{title:"Product Lists",url:"/products",icon:null},{title:"Brands",url:"/brands",icon:null},{title:"Categories",url:"/categories",icon:null},{title:"Units",url:"/units",icon:null}];function d({children:e}){const a=window.location.pathname;return s.jsx("div",{className:"px-4 py-6 ",children:s.jsxs("div",{className:"flex flex-col space-y-8 lg:flex-row lg:space-y-0 lg:space-x-12",children:[s.jsx("aside",{className:"w-full max-w-xl lg:w-48",children:s.jsx("nav",{className:"flex flex-col space-y-1 space-x-0",children:n.map(l=>s.jsx(r,{size:"sm",variant:"ghost",asChild:!0,className:c("w-full justify-start",{"bg-muted":a===l.url}),children:s.jsx(t,{href:l.url,prefetch:!0,children:l.title})},l.url))})}),s.jsx(i,{className:"my-6 md:hidden"}),s.jsx("div",{className:"flex-1 w-full md:max-w-8xl",children:s.jsx("section",{className:"w-full space-y-12",children:e})})]})})}export{d as P};
