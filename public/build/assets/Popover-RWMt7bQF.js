import{c as o}from"./createLucideIcon-48nG323g.js";import{c as l,b as e,u as i,n,o as c,t as p}from"./app-BS747Cvj.js";import{_ as d}from"./_plugin-vue_export-helper-DlAUqK2U.js";/**
 * @license lucide-vue-next v0.488.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const b=o("circle-alert",[["circle",{cx:"12",cy:"12",r:"10",key:"1mglay"}],["line",{x1:"12",x2:"12",y1:"8",y2:"12",key:"1pkeuh"}],["line",{x1:"12",x2:"12.01",y1:"16",y2:"16",key:"4dfq90"}]]);/**
 * @license lucide-vue-next v0.488.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const C=o("circle-check",[["circle",{cx:"12",cy:"12",r:"10",key:"1mglay"}],["path",{d:"m9 12 2 2 4-4",key:"dzmm74"}]]);/**
 * @license lucide-vue-next v0.488.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const _=o("refresh-ccw",[["path",{d:"M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8",key:"14sxne"}],["path",{d:"M3 3v5h5",key:"1xhq8a"}],["path",{d:"M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16",key:"1hlbsb"}],["path",{d:"M16 16h5v5",key:"ccwih5"}]]),h={props:{content:{type:String,default:"This is a hover popover."},trigger:{type:String,default:"hover",validator:t=>["click","hover"].includes(t)},position:{type:String,default:"bottom",validator:t=>["top","bottom","left","right"].includes(t)},wrapperClasses:{type:String}},computed:{triggerClass(){return`[--trigger:${this.trigger}]`},positionClass(){return`[--placement:${this.position}]`}},mounted(){setTimeout(()=>{window.HSTooltip&&window.HSTooltip.autoInit()},500)}},g={class:"hs-tooltip-toggle"},y={role:"tooltip",class:"hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible hidden opacity-0 transition-opacity absolute invisible z-30 w-auto max-w-xs bg-white border border-gray-100 text-start rounded-xl shadow-md after:absolute after:top-0 after:-start-4 after:w-4 after:h-full dark:bg-neutral-800 dark:border-neutral-700"},m={class:"max-w-80 text-xs leading-5 px-4 py-2 whitespace-normal"};function u(t,r,s,w,f,a){return c(),l("div",{class:n(["hs-tooltip inline-block",a.triggerClass,a.positionClass,s.wrapperClasses])},[e("div",g,[i(t.$slots,"trigger",{},()=>[r[0]||(r[0]=e("svg",{class:"w-4 h-4 text-gray-500 hover:text-gray-400",xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"})],-1))]),e("div",y,[i(t.$slots,"content",{},()=>[e("p",m,p(s.content),1)])])])],2)}const S=d(h,[["render",u]]);export{C,S as P,_ as R,b as a};
