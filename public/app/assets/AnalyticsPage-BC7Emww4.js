import{a as Ot,s as It}from"./index-DLSB4pMM.js";import{B as L,W as bt,q as et,x as F,l,c as d,a as p,E as T,G as N,n as h,f as _,t as P,X as O,Y as R,y as Ft,F as W,A as ft,g as S,D as Nt,w,Z,$ as Vt,a0 as j,a1 as Et,a2 as z,a3 as Kt,a4 as pt,s as M,a5 as Rt,a6 as D,a7 as zt,o as Dt,a8 as qt,r as I,d as U,b as m,e as b,H as c,m as G}from"./index-Doov6PF1.js";import{R as Y,f as Q,b as E,s as Ht}from"./index-BUl83WeO.js";import{a as vt}from"./index-dtVcGfei.js";import{C as nt,a as at,L as ot,B as ht,p as rt,b as st,c as gt,d as Wt,e as jt,f as Mt,i as Ut,g as Yt,_ as Qt,P as Zt}from"./index-BFE6TraV.js";import{I as X}from"./inbox-BoUcawbK.js";import{F as Gt}from"./file-text-07FzYanM.js";import{c as mt}from"./createLucideIcon-C96RaR1M.js";import{u as it}from"./useQuery-DCnlwrKJ.js";import{a as lt}from"./useApi-DLsYL8Jd.js";import{u as Xt}from"./useBudgetExecution-CHtoOvSB.js";import"./index-IDYJsoVU.js";import"./index-cNj1xdNV.js";/**
 * @license @lucide/vue v1.17.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Jt=[["circle",{cx:"12",cy:"12",r:"10",key:"1mglay"}],["path",{d:"m9 12 2 2 4-4",key:"dzmm74"}]],te=mt("circle-check",Jt);/**
 * @license @lucide/vue v1.17.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const ee=[["circle",{cx:"12",cy:"12",r:"10",key:"1mglay"}],["path",{d:"M12 16v-4",key:"1dtifu"}],["path",{d:"M12 8h.01",key:"e9boi3"}]],ne=mt("info",ee);var ae=`
    .p-togglebutton {
        display: inline-flex;
        cursor: pointer;
        user-select: none;
        overflow: hidden;
        position: relative;
        color: dt('togglebutton.color');
        background: dt('togglebutton.background');
        border: 1px solid dt('togglebutton.border.color');
        padding: dt('togglebutton.padding');
        font-size: 1rem;
        font-family: inherit;
        font-feature-settings: inherit;
        transition:
            background dt('togglebutton.transition.duration'),
            color dt('togglebutton.transition.duration'),
            border-color dt('togglebutton.transition.duration'),
            outline-color dt('togglebutton.transition.duration'),
            box-shadow dt('togglebutton.transition.duration');
        border-radius: dt('togglebutton.border.radius');
        outline-color: transparent;
        font-weight: dt('togglebutton.font.weight');
    }

    .p-togglebutton-content {
        display: inline-flex;
        flex: 1 1 auto;
        align-items: center;
        justify-content: center;
        gap: dt('togglebutton.gap');
        padding: dt('togglebutton.content.padding');
        background: transparent;
        border-radius: dt('togglebutton.content.border.radius');
        transition:
            background dt('togglebutton.transition.duration'),
            color dt('togglebutton.transition.duration'),
            border-color dt('togglebutton.transition.duration'),
            outline-color dt('togglebutton.transition.duration'),
            box-shadow dt('togglebutton.transition.duration');
    }

    .p-togglebutton:not(:disabled):not(.p-togglebutton-checked):hover {
        background: dt('togglebutton.hover.background');
        color: dt('togglebutton.hover.color');
    }

    .p-togglebutton.p-togglebutton-checked {
        background: dt('togglebutton.checked.background');
        border-color: dt('togglebutton.checked.border.color');
        color: dt('togglebutton.checked.color');
    }

    .p-togglebutton-checked .p-togglebutton-content {
        background: dt('togglebutton.content.checked.background');
        box-shadow: dt('togglebutton.content.checked.shadow');
    }

    .p-togglebutton:focus-visible {
        box-shadow: dt('togglebutton.focus.ring.shadow');
        outline: dt('togglebutton.focus.ring.width') dt('togglebutton.focus.ring.style') dt('togglebutton.focus.ring.color');
        outline-offset: dt('togglebutton.focus.ring.offset');
    }

    .p-togglebutton.p-invalid {
        border-color: dt('togglebutton.invalid.border.color');
    }

    .p-togglebutton:disabled {
        opacity: 1;
        cursor: default;
        background: dt('togglebutton.disabled.background');
        border-color: dt('togglebutton.disabled.border.color');
        color: dt('togglebutton.disabled.color');
    }

    .p-togglebutton-label,
    .p-togglebutton-icon {
        position: relative;
        transition: none;
    }

    .p-togglebutton-icon {
        color: dt('togglebutton.icon.color');
    }

    .p-togglebutton:not(:disabled):not(.p-togglebutton-checked):hover .p-togglebutton-icon {
        color: dt('togglebutton.icon.hover.color');
    }

    .p-togglebutton.p-togglebutton-checked .p-togglebutton-icon {
        color: dt('togglebutton.icon.checked.color');
    }

    .p-togglebutton:disabled .p-togglebutton-icon {
        color: dt('togglebutton.icon.disabled.color');
    }

    .p-togglebutton-sm {
        padding: dt('togglebutton.sm.padding');
        font-size: dt('togglebutton.sm.font.size');
    }

    .p-togglebutton-sm .p-togglebutton-content {
        padding: dt('togglebutton.content.sm.padding');
    }

    .p-togglebutton-lg {
        padding: dt('togglebutton.lg.padding');
        font-size: dt('togglebutton.lg.font.size');
    }

    .p-togglebutton-lg .p-togglebutton-content {
        padding: dt('togglebutton.content.lg.padding');
    }

    .p-togglebutton-fluid {
        width: 100%;
    }
`,oe={root:function(e){var n=e.instance,o=e.props;return["p-togglebutton p-component",{"p-togglebutton-checked":n.active,"p-invalid":n.$invalid,"p-togglebutton-fluid":o.fluid,"p-togglebutton-sm p-inputfield-sm":o.size==="small","p-togglebutton-lg p-inputfield-lg":o.size==="large"}]},content:"p-togglebutton-content",icon:"p-togglebutton-icon",label:"p-togglebutton-label"},re=L.extend({name:"togglebutton",style:ae,classes:oe}),se={name:"BaseToggleButton",extends:vt,props:{onIcon:String,offIcon:String,onLabel:{type:String,default:"Yes"},offLabel:{type:String,default:"No"},readonly:{type:Boolean,default:!1},tabindex:{type:Number,default:null},ariaLabelledby:{type:String,default:null},ariaLabel:{type:String,default:null},size:{type:String,default:null},fluid:{type:Boolean,default:null}},style:re,provide:function(){return{$pcToggleButton:this,$parentInstance:this}}};function V(t){"@babel/helpers - typeof";return V=typeof Symbol=="function"&&typeof Symbol.iterator=="symbol"?function(e){return typeof e}:function(e){return e&&typeof Symbol=="function"&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},V(t)}function ie(t,e,n){return(e=le(e))in t?Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[e]=n,t}function le(t){var e=ue(t,"string");return V(e)=="symbol"?e:e+""}function ue(t,e){if(V(t)!="object"||!t)return t;var n=t[Symbol.toPrimitive];if(n!==void 0){var o=n.call(t,e);if(V(o)!="object")return o;throw new TypeError("@@toPrimitive must return a primitive value.")}return(e==="string"?String:Number)(t)}var yt={name:"ToggleButton",extends:se,inheritAttrs:!1,emits:["change"],methods:{getPTOptions:function(e){var n=e==="root"?this.ptmi:this.ptm;return n(e,{context:{active:this.active,disabled:this.disabled}})},onChange:function(e){!this.disabled&&!this.readonly&&(this.writeValue(!this.d_value,e),this.$emit("change",e))},onBlur:function(e){var n,o;(n=(o=this.formField).onBlur)===null||n===void 0||n.call(o,e)}},computed:{active:function(){return this.d_value===!0},hasLabel:function(){return bt(this.onLabel)&&bt(this.offLabel)},label:function(){return this.hasLabel?this.d_value?this.onLabel:this.offLabel:" "},dataP:function(){return Q(ie({checked:this.active,invalid:this.$invalid},this.size,this.size))}},directives:{ripple:Y}},de=["tabindex","disabled","aria-pressed","aria-label","aria-labelledby","data-p-checked","data-p-disabled","data-p"],ce=["data-p"];function be(t,e,n,o,r,a){var u=et("ripple");return F((l(),d("button",h({type:"button",class:t.cx("root"),tabindex:t.tabindex,disabled:t.disabled,"aria-pressed":t.d_value,onClick:e[0]||(e[0]=function(){return a.onChange&&a.onChange.apply(a,arguments)}),onBlur:e[1]||(e[1]=function(){return a.onBlur&&a.onBlur.apply(a,arguments)})},a.getPTOptions("root"),{"aria-label":t.ariaLabel,"aria-labelledby":t.ariaLabelledby,"data-p-checked":a.active,"data-p-disabled":t.disabled,"data-p":a.dataP}),[p("span",h({class:t.cx("content")},a.getPTOptions("content"),{"data-p":a.dataP}),[T(t.$slots,"default",{},function(){return[T(t.$slots,"icon",{value:t.d_value,class:N(t.cx("icon"))},function(){return[t.onIcon||t.offIcon?(l(),d("span",h({key:0,class:[t.cx("icon"),t.d_value?t.onIcon:t.offIcon]},a.getPTOptions("icon")),null,16)):_("",!0)]}),p("span",h({class:t.cx("label")},a.getPTOptions("label")),P(a.label),17)]})],16,ce)],16,de)),[[u]])}yt.render=be;var pe=`
    .p-selectbutton {
        display: inline-flex;
        user-select: none;
        vertical-align: bottom;
        outline-color: transparent;
        border-radius: dt('selectbutton.border.radius');
    }

    .p-selectbutton .p-togglebutton {
        border-radius: 0;
        border-width: 1px 1px 1px 0;
    }

    .p-selectbutton .p-togglebutton:focus-visible {
        position: relative;
        z-index: 1;
    }

    .p-selectbutton .p-togglebutton:first-child {
        border-inline-start-width: 1px;
        border-start-start-radius: dt('selectbutton.border.radius');
        border-end-start-radius: dt('selectbutton.border.radius');
    }

    .p-selectbutton .p-togglebutton:last-child {
        border-start-end-radius: dt('selectbutton.border.radius');
        border-end-end-radius: dt('selectbutton.border.radius');
    }

    .p-selectbutton.p-invalid {
        outline: 1px solid dt('selectbutton.invalid.border.color');
        outline-offset: 0;
    }

    .p-selectbutton-fluid {
        width: 100%;
    }
    
    .p-selectbutton-fluid .p-togglebutton {
        flex: 1 1 0;
    }
`,fe={root:function(e){var n=e.props,o=e.instance;return["p-selectbutton p-component",{"p-invalid":o.$invalid,"p-selectbutton-fluid":n.fluid}]}},ve=L.extend({name:"selectbutton",style:pe,classes:fe}),he={name:"BaseSelectButton",extends:vt,props:{options:Array,optionLabel:null,optionValue:null,optionDisabled:null,multiple:Boolean,allowEmpty:{type:Boolean,default:!0},dataKey:null,ariaLabelledby:{type:String,default:null},size:{type:String,default:null},fluid:{type:Boolean,default:null}},style:ve,provide:function(){return{$pcSelectButton:this,$parentInstance:this}}};function ge(t,e){var n=typeof Symbol<"u"&&t[Symbol.iterator]||t["@@iterator"];if(!n){if(Array.isArray(t)||(n=kt(t))||e){n&&(t=n);var o=0,r=function(){};return{s:r,n:function(){return o>=t.length?{done:!0}:{done:!1,value:t[o++]}},e:function($){throw $},f:r}}throw new TypeError(`Invalid attempt to iterate non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}var a,u=!0,s=!1;return{s:function(){n=n.call(t)},n:function(){var $=n.next();return u=$.done,$},e:function($){s=!0,a=$},f:function(){try{u||n.return==null||n.return()}finally{if(s)throw a}}}}function me(t){return $e(t)||ke(t)||kt(t)||ye()}function ye(){throw new TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function kt(t,e){if(t){if(typeof t=="string")return J(t,e);var n={}.toString.call(t).slice(8,-1);return n==="Object"&&t.constructor&&(n=t.constructor.name),n==="Map"||n==="Set"?Array.from(t):n==="Arguments"||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?J(t,e):void 0}}function ke(t){if(typeof Symbol<"u"&&t[Symbol.iterator]!=null||t["@@iterator"]!=null)return Array.from(t)}function $e(t){if(Array.isArray(t))return J(t)}function J(t,e){(e==null||e>t.length)&&(e=t.length);for(var n=0,o=Array(e);n<e;n++)o[n]=t[n];return o}var $t={name:"SelectButton",extends:he,inheritAttrs:!1,emits:["change"],methods:{getOptionLabel:function(e){return this.optionLabel?R(e,this.optionLabel):e},getOptionValue:function(e){return this.optionValue?R(e,this.optionValue):e},getOptionRenderKey:function(e){return this.dataKey?R(e,this.dataKey):this.getOptionLabel(e)},isOptionDisabled:function(e){return this.optionDisabled?R(e,this.optionDisabled):!1},isOptionReadonly:function(e){if(this.allowEmpty)return!1;var n=this.isSelected(e);return this.multiple?n&&this.d_value.length===1:n},onOptionSelect:function(e,n,o){var r=this;if(!(this.disabled||this.isOptionDisabled(n)||this.isOptionReadonly(n))){var a=this.isSelected(n),u=this.getOptionValue(n),s;if(this.multiple)if(a){if(s=this.d_value.filter(function(f){return!O(f,u,r.equalityKey)}),!this.allowEmpty&&s.length===0)return}else s=this.d_value?[].concat(me(this.d_value),[u]):[u];else{if(a&&!this.allowEmpty)return;s=a?null:u}this.writeValue(s,e),this.$emit("change",{originalEvent:e,value:s})}},isSelected:function(e){var n=!1,o=this.getOptionValue(e);if(this.multiple){if(this.d_value){var r=ge(this.d_value),a;try{for(r.s();!(a=r.n()).done;){var u=a.value;if(O(u,o,this.equalityKey)){n=!0;break}}}catch(s){r.e(s)}finally{r.f()}}}else n=O(this.d_value,o,this.equalityKey);return n}},computed:{equalityKey:function(){return this.optionValue?null:this.dataKey},dataP:function(){return Q({invalid:this.$invalid})}},directives:{ripple:Y},components:{ToggleButton:yt}},xe=["aria-labelledby","data-p"];function we(t,e,n,o,r,a){var u=Ft("ToggleButton");return l(),d("div",h({class:t.cx("root"),role:"group","aria-labelledby":t.ariaLabelledby},t.ptmi("root"),{"data-p":a.dataP}),[(l(!0),d(W,null,ft(t.options,function(s,f){return l(),S(u,{key:a.getOptionRenderKey(s),modelValue:a.isSelected(s),onLabel:a.getOptionLabel(s),offLabel:a.getOptionLabel(s),disabled:t.disabled||a.isOptionDisabled(s),unstyled:t.unstyled,size:t.size,readonly:a.isOptionReadonly(s),onChange:function(x){return a.onOptionSelect(x,s,f)},pt:t.ptm("pcToggleButton")},Nt({_:2},[t.$slots.option?{name:"default",fn:w(function(){return[T(t.$slots,"option",{option:s,index:f},function(){return[p("span",h({ref_for:!0},t.ptm("pcToggleButton").label),P(a.getOptionLabel(s)),17)]})]}),key:"0"}:void 0]),1032,["modelValue","onLabel","offLabel","disabled","unstyled","size","readonly","onChange","pt"])}),128))],16,xe)}$t.render=we;var Te=`
    .p-tabs {
        display: flex;
        flex-direction: column;
    }

    .p-tablist {
        display: flex;
        position: relative;
        overflow: hidden;
        background: dt('tabs.tablist.background');
    }

    .p-tablist-viewport {
        overflow-x: auto;
        overflow-y: hidden;
        scroll-behavior: smooth;
        scrollbar-width: none;
        overscroll-behavior: contain auto;
    }

    .p-tablist-viewport::-webkit-scrollbar {
        display: none;
    }

    .p-tablist-tab-list {
        position: relative;
        display: flex;
        border-style: solid;
        border-color: dt('tabs.tablist.border.color');
        border-width: dt('tabs.tablist.border.width');
    }

    .p-tablist-content {
        flex-grow: 1;
    }

    .p-tablist-nav-button {
        all: unset;
        position: absolute !important;
        flex-shrink: 0;
        inset-block-start: 0;
        z-index: 2;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: dt('tabs.nav.button.background');
        color: dt('tabs.nav.button.color');
        width: dt('tabs.nav.button.width');
        transition:
            color dt('tabs.transition.duration'),
            outline-color dt('tabs.transition.duration'),
            box-shadow dt('tabs.transition.duration');
        box-shadow: dt('tabs.nav.button.shadow');
        outline-color: transparent;
        cursor: pointer;
    }

    .p-tablist-nav-button:focus-visible {
        z-index: 1;
        box-shadow: dt('tabs.nav.button.focus.ring.shadow');
        outline: dt('tabs.nav.button.focus.ring.width') dt('tabs.nav.button.focus.ring.style') dt('tabs.nav.button.focus.ring.color');
        outline-offset: dt('tabs.nav.button.focus.ring.offset');
    }

    .p-tablist-nav-button:hover {
        color: dt('tabs.nav.button.hover.color');
    }

    .p-tablist-prev-button {
        inset-inline-start: 0;
    }

    .p-tablist-next-button {
        inset-inline-end: 0;
    }

    .p-tablist-prev-button:dir(rtl),
    .p-tablist-next-button:dir(rtl) {
        transform: rotate(180deg);
    }

    .p-tab {
        flex-shrink: 0;
        cursor: pointer;
        user-select: none;
        position: relative;
        border-style: solid;
        white-space: nowrap;
        gap: dt('tabs.tab.gap');
        background: dt('tabs.tab.background');
        border-width: dt('tabs.tab.border.width');
        border-color: dt('tabs.tab.border.color');
        color: dt('tabs.tab.color');
        padding: dt('tabs.tab.padding');
        font-weight: dt('tabs.tab.font.weight');
        transition:
            background dt('tabs.transition.duration'),
            border-color dt('tabs.transition.duration'),
            color dt('tabs.transition.duration'),
            outline-color dt('tabs.transition.duration'),
            box-shadow dt('tabs.transition.duration');
        margin: dt('tabs.tab.margin');
        outline-color: transparent;
    }

    .p-tab:not(.p-disabled):focus-visible {
        z-index: 1;
        box-shadow: dt('tabs.tab.focus.ring.shadow');
        outline: dt('tabs.tab.focus.ring.width') dt('tabs.tab.focus.ring.style') dt('tabs.tab.focus.ring.color');
        outline-offset: dt('tabs.tab.focus.ring.offset');
    }

    .p-tab:not(.p-tab-active):not(.p-disabled):hover {
        background: dt('tabs.tab.hover.background');
        border-color: dt('tabs.tab.hover.border.color');
        color: dt('tabs.tab.hover.color');
    }

    .p-tab-active {
        background: dt('tabs.tab.active.background');
        border-color: dt('tabs.tab.active.border.color');
        color: dt('tabs.tab.active.color');
    }

    .p-tabpanels {
        background: dt('tabs.tabpanel.background');
        color: dt('tabs.tabpanel.color');
        padding: dt('tabs.tabpanel.padding');
        outline: 0 none;
    }

    .p-tabpanel:focus-visible {
        box-shadow: dt('tabs.tabpanel.focus.ring.shadow');
        outline: dt('tabs.tabpanel.focus.ring.width') dt('tabs.tabpanel.focus.ring.style') dt('tabs.tabpanel.focus.ring.color');
        outline-offset: dt('tabs.tabpanel.focus.ring.offset');
    }

    .p-tablist-active-bar {
        z-index: 1;
        display: block;
        position: absolute;
        inset-block-end: dt('tabs.active.bar.bottom');
        height: dt('tabs.active.bar.height');
        background: dt('tabs.active.bar.background');
        transition: 250ms cubic-bezier(0.35, 0, 0.25, 1);
    }
`,Be={root:function(e){var n=e.props;return["p-tabs p-component",{"p-tabs-scrollable":n.scrollable}]}},_e=L.extend({name:"tabs",style:Te,classes:Be}),Ce={name:"BaseTabs",extends:E,props:{value:{type:[String,Number],default:void 0},lazy:{type:Boolean,default:!1},scrollable:{type:Boolean,default:!1},showNavigators:{type:Boolean,default:!0},tabindex:{type:Number,default:0},selectOnFocus:{type:Boolean,default:!1}},style:_e,provide:function(){return{$pcTabs:this,$parentInstance:this}}},xt={name:"Tabs",extends:Ce,inheritAttrs:!1,emits:["update:value"],data:function(){return{d_value:this.value}},watch:{value:function(e){this.d_value=e}},methods:{updateValue:function(e){this.d_value!==e&&(this.d_value=e,this.$emit("update:value",e))},isVertical:function(){return this.orientation==="vertical"}}};function Se(t,e,n,o,r,a){return l(),d("div",h({class:t.cx("root")},t.ptmi("root")),[T(t.$slots,"default")],16)}xt.render=Se;var wt={name:"ChevronLeftIcon",extends:Ht};function Le(t){return Ie(t)||Oe(t)||Pe(t)||Ae()}function Ae(){throw new TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function Pe(t,e){if(t){if(typeof t=="string")return tt(t,e);var n={}.toString.call(t).slice(8,-1);return n==="Object"&&t.constructor&&(n=t.constructor.name),n==="Map"||n==="Set"?Array.from(t):n==="Arguments"||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?tt(t,e):void 0}}function Oe(t){if(typeof Symbol<"u"&&t[Symbol.iterator]!=null||t["@@iterator"]!=null)return Array.from(t)}function Ie(t){if(Array.isArray(t))return tt(t)}function tt(t,e){(e==null||e>t.length)&&(e=t.length);for(var n=0,o=Array(e);n<e;n++)o[n]=t[n];return o}function Fe(t,e,n,o,r,a){return l(),d("svg",h({width:"14",height:"14",viewBox:"0 0 14 14",fill:"none",xmlns:"http://www.w3.org/2000/svg"},t.pti()),Le(e[0]||(e[0]=[p("path",{d:"M9.61296 13C9.50997 13.0005 9.40792 12.9804 9.3128 12.9409C9.21767 12.9014 9.13139 12.8433 9.05902 12.7701L3.83313 7.54416C3.68634 7.39718 3.60388 7.19795 3.60388 6.99022C3.60388 6.78249 3.68634 6.58325 3.83313 6.43628L9.05902 1.21039C9.20762 1.07192 9.40416 0.996539 9.60724 1.00012C9.81032 1.00371 10.0041 1.08597 10.1477 1.22959C10.2913 1.37322 10.3736 1.56698 10.3772 1.77005C10.3808 1.97313 10.3054 2.16968 10.1669 2.31827L5.49496 6.99022L10.1669 11.6622C10.3137 11.8091 10.3962 12.0084 10.3962 12.2161C10.3962 12.4238 10.3137 12.6231 10.1669 12.7701C10.0945 12.8433 10.0083 12.9014 9.91313 12.9409C9.81801 12.9804 9.71596 13.0005 9.61296 13Z",fill:"currentColor"},null,-1)])),16)}wt.render=Fe;var Ne={root:"p-tablist",content:"p-tablist-content p-tablist-viewport",tabList:"p-tablist-tab-list",activeBar:"p-tablist-active-bar",prevButton:"p-tablist-prev-button p-tablist-nav-button",nextButton:"p-tablist-next-button p-tablist-nav-button"},Ve=L.extend({name:"tablist",classes:Ne}),Ee={name:"BaseTabList",extends:E,props:{},style:Ve,provide:function(){return{$pcTabList:this,$parentInstance:this}}},Tt={name:"TabList",extends:Ee,inheritAttrs:!1,inject:["$pcTabs"],data:function(){return{isPrevButtonEnabled:!1,isNextButtonEnabled:!0}},resizeObserver:void 0,inkBarObserver:void 0,watch:{showNavigators:function(e){e?this.bindResizeObserver():this.unbindResizeObserver()},activeValue:{flush:"post",handler:function(){this.updateInkBar(),this.bindInkBarObserver()}}},mounted:function(){var e=this;setTimeout(function(){e.updateInkBar(),e.bindInkBarObserver()},150),this.showNavigators&&(this.updateButtonState(),this.bindResizeObserver())},updated:function(){this.showNavigators&&this.updateButtonState()},beforeUnmount:function(){this.unbindResizeObserver(),this.unbindInkBarObserver()},methods:{onScroll:function(e){this.showNavigators&&this.updateButtonState(),e.preventDefault()},onPrevButtonClick:function(){var e=this.$refs.content,n=this.getVisibleButtonWidths(),o=Z(e)-n,r=Math.abs(e.scrollLeft),a=o*.8,u=r-a,s=Math.max(u,0);e.scrollLeft=pt(e)?-1*s:s},onNextButtonClick:function(){var e=this.$refs.content,n=this.getVisibleButtonWidths(),o=Z(e)-n,r=Math.abs(e.scrollLeft),a=o*.8,u=r+a,s=e.scrollWidth-o,f=Math.min(u,s);e.scrollLeft=pt(e)?-1*f:f},bindResizeObserver:function(){var e=this;this.resizeObserver=new ResizeObserver(function(){return e.updateButtonState()}),this.resizeObserver.observe(this.$refs.list)},unbindResizeObserver:function(){var e;(e=this.resizeObserver)===null||e===void 0||e.unobserve(this.$refs.list),this.resizeObserver=void 0},bindInkBarObserver:function(){var e=this;this.unbindInkBarObserver();var n=this.$refs.content,o=j(n,'[data-pc-name="tab"][data-p-active="true"]');o&&(this.inkBarObserver=new ResizeObserver(function(){return e.updateInkBar()}),this.inkBarObserver.observe(o))},unbindInkBarObserver:function(){var e;(e=this.inkBarObserver)===null||e===void 0||e.disconnect(),this.inkBarObserver=void 0},updateInkBar:function(){var e=this.$refs,n=e.content,o=e.inkbar,r=e.tabs;if(o){var a=j(n,'[data-pc-name="tab"][data-p-active="true"]');this.$pcTabs.isVertical()?(o.style.height=Et(a)+"px",o.style.top=z(a).top-z(r).top+"px"):(o.style.width=Kt(a)+"px",o.style.left=z(a).left-z(r).left+"px")}},updateButtonState:function(){var e=this.$refs,n=e.list,o=e.content,r=o.scrollTop,a=o.scrollWidth,u=o.scrollHeight,s=o.offsetWidth,f=o.offsetHeight,$=Math.abs(o.scrollLeft),x=[Z(o),Vt(o)],g=x[0],y=x[1];this.$pcTabs.isVertical()?(this.isPrevButtonEnabled=r!==0,this.isNextButtonEnabled=n.offsetHeight>=f&&parseInt(r)!==u-y):(this.isPrevButtonEnabled=$!==0,this.isNextButtonEnabled=n.offsetWidth>=s&&parseInt($)!==a-g)},getVisibleButtonWidths:function(){var e=this.$refs,n=e.prevButton,o=e.nextButton,r=0;return this.showNavigators&&(r=((n==null?void 0:n.offsetWidth)||0)+((o==null?void 0:o.offsetWidth)||0)),r}},computed:{templates:function(){return this.$pcTabs.$slots},activeValue:function(){return this.$pcTabs.d_value},showNavigators:function(){return this.$pcTabs.showNavigators},prevButtonAriaLabel:function(){return this.$primevue.config.locale.aria?this.$primevue.config.locale.aria.previous:void 0},nextButtonAriaLabel:function(){return this.$primevue.config.locale.aria?this.$primevue.config.locale.aria.next:void 0},dataP:function(){return Q({scrollable:this.$pcTabs.scrollable})}},components:{ChevronLeftIcon:wt,ChevronRightIcon:Ot},directives:{ripple:Y}},Ke=["data-p"],Re=["aria-label","tabindex"],ze=["data-p"],De=["aria-orientation"],qe=["aria-label","tabindex"];function He(t,e,n,o,r,a){var u=et("ripple");return l(),d("div",h({ref:"list",class:t.cx("root"),"data-p":a.dataP},t.ptmi("root")),[a.showNavigators&&r.isPrevButtonEnabled?F((l(),d("button",h({key:0,ref:"prevButton",type:"button",class:t.cx("prevButton"),"aria-label":a.prevButtonAriaLabel,tabindex:a.$pcTabs.tabindex,onClick:e[0]||(e[0]=function(){return a.onPrevButtonClick&&a.onPrevButtonClick.apply(a,arguments)})},t.ptm("prevButton"),{"data-pc-group-section":"navigator"}),[(l(),S(M(a.templates.previcon||"ChevronLeftIcon"),h({"aria-hidden":"true"},t.ptm("prevIcon")),null,16))],16,Re)),[[u]]):_("",!0),p("div",h({ref:"content",class:t.cx("content"),onScroll:e[1]||(e[1]=function(){return a.onScroll&&a.onScroll.apply(a,arguments)}),"data-p":a.dataP},t.ptm("content")),[p("div",h({ref:"tabs",class:t.cx("tabList"),role:"tablist","aria-orientation":a.$pcTabs.orientation||"horizontal"},t.ptm("tabList")),[T(t.$slots,"default"),p("span",h({ref:"inkbar",class:t.cx("activeBar"),role:"presentation","aria-hidden":"true"},t.ptm("activeBar")),null,16)],16,De)],16,ze),a.showNavigators&&r.isNextButtonEnabled?F((l(),d("button",h({key:1,ref:"nextButton",type:"button",class:t.cx("nextButton"),"aria-label":a.nextButtonAriaLabel,tabindex:a.$pcTabs.tabindex,onClick:e[2]||(e[2]=function(){return a.onNextButtonClick&&a.onNextButtonClick.apply(a,arguments)})},t.ptm("nextButton"),{"data-pc-group-section":"navigator"}),[(l(),S(M(a.templates.nexticon||"ChevronRightIcon"),h({"aria-hidden":"true"},t.ptm("nextIcon")),null,16))],16,qe)),[[u]]):_("",!0)],16,Ke)}Tt.render=He;var We={root:function(e){var n=e.instance,o=e.props;return["p-tab",{"p-tab-active":n.active,"p-disabled":o.disabled}]}},je=L.extend({name:"tab",classes:We}),Me={name:"BaseTab",extends:E,props:{value:{type:[String,Number],default:void 0},disabled:{type:Boolean,default:!1},as:{type:[String,Object],default:"BUTTON"},asChild:{type:Boolean,default:!1}},style:je,provide:function(){return{$pcTab:this,$parentInstance:this}}},q={name:"Tab",extends:Me,inheritAttrs:!1,inject:["$pcTabs","$pcTabList"],methods:{onFocus:function(){this.$pcTabs.selectOnFocus&&this.changeActiveValue()},onClick:function(){this.changeActiveValue()},onKeydown:function(e){switch(e.code){case"ArrowRight":this.onArrowRightKey(e);break;case"ArrowLeft":this.onArrowLeftKey(e);break;case"Home":this.onHomeKey(e);break;case"End":this.onEndKey(e);break;case"PageDown":this.onPageDownKey(e);break;case"PageUp":this.onPageUpKey(e);break;case"Enter":case"NumpadEnter":case"Space":this.onEnterKey(e);break}},onArrowRightKey:function(e){var n=this.findNextTab(e.currentTarget);n?this.changeFocusedTab(e,n):this.onHomeKey(e),e.preventDefault()},onArrowLeftKey:function(e){var n=this.findPrevTab(e.currentTarget);n?this.changeFocusedTab(e,n):this.onEndKey(e),e.preventDefault()},onHomeKey:function(e){var n=this.findFirstTab();this.changeFocusedTab(e,n),e.preventDefault()},onEndKey:function(e){var n=this.findLastTab();this.changeFocusedTab(e,n),e.preventDefault()},onPageDownKey:function(e){this.scrollInView(this.findLastTab()),e.preventDefault()},onPageUpKey:function(e){this.scrollInView(this.findFirstTab()),e.preventDefault()},onEnterKey:function(e){this.changeActiveValue()},findNextTab:function(e){var n=arguments.length>1&&arguments[1]!==void 0?arguments[1]:!1,o=n?e:e.nextElementSibling;return o?D(o,"data-p-disabled")||D(o,"data-pc-section")==="activebar"?this.findNextTab(o):j(o,'[data-pc-name="tab"]'):null},findPrevTab:function(e){var n=arguments.length>1&&arguments[1]!==void 0?arguments[1]:!1,o=n?e:e.previousElementSibling;return o?D(o,"data-p-disabled")||D(o,"data-pc-section")==="activebar"?this.findPrevTab(o):j(o,'[data-pc-name="tab"]'):null},findFirstTab:function(){return this.findNextTab(this.$pcTabList.$refs.tabs.firstElementChild,!0)},findLastTab:function(){return this.findPrevTab(this.$pcTabList.$refs.tabs.lastElementChild,!0)},changeActiveValue:function(){this.$pcTabs.updateValue(this.value)},changeFocusedTab:function(e,n){Rt(n),this.scrollInView(n)},scrollInView:function(e){var n;e==null||(n=e.scrollIntoView)===null||n===void 0||n.call(e,{block:"nearest"})}},computed:{active:function(){var e;return O((e=this.$pcTabs)===null||e===void 0?void 0:e.d_value,this.value)},id:function(){var e;return"".concat((e=this.$pcTabs)===null||e===void 0?void 0:e.$id,"_tab_").concat(this.value)},ariaControls:function(){var e;return"".concat((e=this.$pcTabs)===null||e===void 0?void 0:e.$id,"_tabpanel_").concat(this.value)},attrs:function(){return h(this.asAttrs,this.a11yAttrs,this.ptmi("root",this.ptParams))},asAttrs:function(){return this.as==="BUTTON"?{type:"button",disabled:this.disabled}:void 0},a11yAttrs:function(){return{id:this.id,tabindex:this.active?this.$pcTabs.tabindex:-1,role:"tab","aria-selected":this.active,"aria-controls":this.ariaControls,"data-pc-name":"tab","data-p-disabled":this.disabled,"data-p-active":this.active,onFocus:this.onFocus,onKeydown:this.onKeydown}},ptParams:function(){return{context:{active:this.active}}},dataP:function(){return Q({active:this.active})}},directives:{ripple:Y}};function Ue(t,e,n,o,r,a){var u=et("ripple");return t.asChild?T(t.$slots,"default",{key:1,dataP:a.dataP,class:N(t.cx("root")),active:a.active,a11yAttrs:a.a11yAttrs,onClick:a.onClick}):F((l(),S(M(t.as),h({key:0,class:t.cx("root"),"data-p":a.dataP,onClick:a.onClick},a.attrs),{default:w(function(){return[T(t.$slots,"default")]}),_:3},16,["class","data-p","onClick"])),[[u]])}q.render=Ue;var Ye={root:"p-tabpanels"},Qe=L.extend({name:"tabpanels",classes:Ye}),Ze={name:"BaseTabPanels",extends:E,props:{},style:Qe,provide:function(){return{$pcTabPanels:this,$parentInstance:this}}},Bt={name:"TabPanels",extends:Ze,inheritAttrs:!1};function Ge(t,e,n,o,r,a){return l(),d("div",h({class:t.cx("root"),role:"presentation"},t.ptmi("root")),[T(t.$slots,"default")],16)}Bt.render=Ge;var Xe={root:function(e){var n=e.instance;return["p-tabpanel",{"p-tabpanel-active":n.active}]}},Je=L.extend({name:"tabpanel",classes:Xe}),tn={name:"BaseTabPanel",extends:E,props:{value:{type:[String,Number],default:void 0},as:{type:[String,Object],default:"DIV"},asChild:{type:Boolean,default:!1},header:null,headerStyle:null,headerClass:null,headerProps:null,headerActionProps:null,contentStyle:null,contentClass:null,contentProps:null,disabled:Boolean},style:Je,provide:function(){return{$pcTabPanel:this,$parentInstance:this}}},H={name:"TabPanel",extends:tn,inheritAttrs:!1,inject:["$pcTabs"],computed:{active:function(){var e;return O((e=this.$pcTabs)===null||e===void 0?void 0:e.d_value,this.value)},id:function(){var e;return"".concat((e=this.$pcTabs)===null||e===void 0?void 0:e.$id,"_tabpanel_").concat(this.value)},ariaLabelledby:function(){var e;return"".concat((e=this.$pcTabs)===null||e===void 0?void 0:e.$id,"_tab_").concat(this.value)},attrs:function(){return h(this.a11yAttrs,this.ptmi("root",this.ptParams))},a11yAttrs:function(){var e;return{id:this.id,tabindex:(e=this.$pcTabs)===null||e===void 0?void 0:e.tabindex,role:"tabpanel","aria-labelledby":this.ariaLabelledby,"data-pc-name":"tabpanel","data-p-active":this.active}},ptParams:function(){return{context:{active:this.active}}}}};function en(t,e,n,o,r,a){var u,s;return a.$pcTabs?(l(),d(W,{key:1},[t.asChild?T(t.$slots,"default",{key:1,class:N(t.cx("root")),active:a.active,a11yAttrs:a.a11yAttrs}):(l(),d(W,{key:0},[!((u=a.$pcTabs)!==null&&u!==void 0&&u.lazy)||a.active?F((l(),S(M(t.as),h({key:0,class:t.cx("root")},a.attrs),{default:w(function(){return[T(t.$slots,"default")]}),_:3},16,["class"])),[[zt,(s=a.$pcTabs)!==null&&s!==void 0&&s.lazy?!0:a.active]]):_("",!0)],64))],64)):T(t.$slots,"default",{key:0})}H.render=en;function ut(){const t=I(!1);if(typeof window>"u"||typeof window.matchMedia!="function")return t;const e=window.matchMedia("(prefers-reduced-motion: reduce)"),n=()=>{t.value=e.matches};return Dt(()=>{n(),e.addEventListener("change",n)}),qt(()=>{e.removeEventListener("change",n)}),t}const nn={class:"h-72"},an=U({__name:"ComparisonChart",props:{labels:{},budget:{},disbursed:{}},setup(t){nt.register(at,ot,ht,rt,st);const e=t,n=new Intl.NumberFormat("th-TH",{minimumFractionDigits:2,maximumFractionDigits:2}),o=new Intl.NumberFormat("th-TH",{notation:"compact",maximumFractionDigits:1}),r=ut(),a=c(()=>({labels:e.labels,datasets:[{label:"งบ/จัดสรร",data:e.budget,backgroundColor:"#0ea5e9",hoverBackgroundColor:"#38bdf8",borderRadius:6,maxBarThickness:38},{label:"เบิกจ่าย",data:e.disbursed,backgroundColor:"#10b981",hoverBackgroundColor:"#34d399",borderRadius:6,maxBarThickness:38}]})),u=c(()=>({responsive:!0,maintainAspectRatio:!1,animation:r.value?!1:void 0,plugins:{legend:{display:!0,labels:{color:"#cbd5e1",usePointStyle:!0,boxWidth:8}},tooltip:{backgroundColor:"#1e293b",borderColor:"#334155",borderWidth:1,titleColor:"#f1f5f9",bodyColor:"#94a3b8",padding:10,callbacks:{label:s=>` ${s.dataset.label}: ${n.format(Number(s.parsed.y))} บาท`}}},scales:{x:{grid:{color:"#334155"},border:{color:"#334155"},ticks:{color:"#94a3b8"}},y:{beginAtZero:!0,grid:{color:"#334155"},border:{color:"#334155"},ticks:{color:"#94a3b8",callback:s=>o.format(Number(s))}}}}));return(s,f)=>(l(),d("div",nn,[m(b(gt),{data:a.value,options:u.value},null,8,["data","options"])]))}}),on={class:"space-y-3"},rn={class:"flex justify-end"},sn={class:"inline-flex rounded-lg border border-dark-border bg-dark-bg p-0.5 text-xs"},ln={class:"h-72"},un=U({__name:"ForecastChart",props:{labels:{},forecastMonthly:{},actualMonthly:{},forecastCumulative:{},actualCumulative:{}},setup(t){nt.register(at,ot,Wt,jt,Mt,Ut,rt,st);const e=t,n=I("monthly"),o=new Intl.NumberFormat("th-TH",{minimumFractionDigits:2,maximumFractionDigits:2}),r=new Intl.NumberFormat("th-TH",{notation:"compact",maximumFractionDigits:1}),a=ut(),u=c(()=>n.value==="monthly"?e.forecastMonthly:e.forecastCumulative),s=c(()=>n.value==="monthly"?e.actualMonthly:e.actualCumulative),f=c(()=>({labels:e.labels,datasets:[{label:"พยากรณ์ (Forecast)",data:u.value,borderColor:"#f59e0b",backgroundColor:"rgba(245, 158, 11, 0.08)",borderDash:[6,4],pointBackgroundColor:"#f59e0b",pointRadius:3,tension:.3,fill:!1},{label:"เบิกจ่ายจริง (Actual)",data:s.value,borderColor:"#10b981",backgroundColor:"rgba(16, 185, 129, 0.12)",pointBackgroundColor:"#10b981",pointRadius:3,tension:.3,fill:!0}]})),$=c(()=>({responsive:!0,maintainAspectRatio:!1,animation:a.value?!1:void 0,interaction:{mode:"index",intersect:!1},plugins:{legend:{display:!0,labels:{color:"#cbd5e1",usePointStyle:!0,boxWidth:8}},tooltip:{backgroundColor:"#1e293b",borderColor:"#334155",borderWidth:1,titleColor:"#f1f5f9",bodyColor:"#94a3b8",padding:10,callbacks:{label:x=>` ${x.dataset.label}: ${o.format(Number(x.parsed.y))} บาท`}}},scales:{x:{grid:{color:"#334155"},border:{color:"#334155"},ticks:{color:"#94a3b8"}},y:{beginAtZero:!0,grid:{color:"#334155"},border:{color:"#334155"},ticks:{color:"#94a3b8",callback:x=>r.format(Number(x))}}}}));return(x,g)=>(l(),d("div",on,[p("div",rn,[p("div",sn,[p("button",{type:"button",class:N(["rounded-md px-3 py-1.5 font-medium transition",n.value==="monthly"?"bg-primary-600 text-white":"text-dark-muted hover:text-white"]),onClick:g[0]||(g[0]=y=>n.value="monthly")}," รายเดือน ",2),p("button",{type:"button",class:N(["rounded-md px-3 py-1.5 font-medium transition",n.value==="cumulative"?"bg-primary-600 text-white":"text-dark-muted hover:text-white"]),onClick:g[1]||(g[1]=y=>n.value="cumulative")}," สะสม ",2)])]),p("div",ln,[m(b(Yt),{data:f.value,options:$.value},null,8,["data","options"])])]))}}),dn={class:"space-y-6"},cn={class:"grid grid-cols-1 gap-4 sm:grid-cols-3"},bn={class:"rounded-xl border border-dark-border bg-dark-card p-5 shadow-sm"},pn={key:0,class:"flex h-72 flex-col items-center justify-center gap-2 text-dark-muted"},fn={key:1,class:"h-80"},vn=U({__name:"RequestApprovalChart",props:{report:{}},setup(t){nt.register(at,ot,ht,rt,st);const e=t,n=new Intl.NumberFormat("th-TH",{style:"currency",currency:"THB"}),o=new Intl.NumberFormat("th-TH",{minimumFractionDigits:2,maximumFractionDigits:2}),r=new Intl.NumberFormat("th-TH",{notation:"compact",maximumFractionDigits:1}),a=new Intl.NumberFormat("th-TH",{style:"percent",maximumFractionDigits:1}),u=ut(),s=c(()=>[{label:"ยอดขอ",value:n.format(e.report.requested),accent:"sky",icon:Gt},{label:"ยอดอนุมัติ",value:n.format(e.report.approved),accent:"emerald",icon:te},{label:"อัตราอนุมัติ",value:a.format(e.report.approval_rate),accent:"violet",icon:Zt}]),f=c(()=>e.report.by_org.length>0),$=c(()=>({labels:e.report.by_org.map(g=>g.org_name),datasets:[{label:"ยอดขอ",data:e.report.by_org.map(g=>g.requested),backgroundColor:"#0ea5e9",hoverBackgroundColor:"#38bdf8",borderRadius:6,maxBarThickness:22},{label:"ยอดอนุมัติ",data:e.report.by_org.map(g=>g.approved),backgroundColor:"#10b981",hoverBackgroundColor:"#34d399",borderRadius:6,maxBarThickness:22}]})),x=c(()=>({indexAxis:"y",responsive:!0,maintainAspectRatio:!1,animation:u.value?!1:void 0,plugins:{legend:{display:!0,labels:{color:"#cbd5e1",usePointStyle:!0,boxWidth:8}},tooltip:{backgroundColor:"#1e293b",borderColor:"#334155",borderWidth:1,titleColor:"#f1f5f9",bodyColor:"#94a3b8",padding:10,callbacks:{label:g=>` ${g.dataset.label}: ${o.format(Number(g.parsed.x))} บาท`}}},scales:{x:{beginAtZero:!0,grid:{color:"#334155"},border:{color:"#334155"},ticks:{color:"#94a3b8",callback:g=>r.format(Number(g))}},y:{grid:{display:!1},border:{color:"#334155"},ticks:{color:"#cbd5e1"}}}}));return(g,y)=>(l(),d("div",dn,[p("div",cn,[(l(!0),d(W,null,ft(s.value,B=>(l(),S(Qt,h({key:B.label},{ref_for:!0},B),null,16))),128))]),p("section",bn,[y[1]||(y[1]=p("h2",{class:"mb-4 text-base font-semibold text-white"},"คำขอ vs อนุมัติ ตามหน่วยงาน",-1)),f.value?(l(),d("div",fn,[m(b(gt),{data:$.value,options:x.value},null,8,["data","options"])])):(l(),d("div",pn,[m(b(X),{class:"h-10 w-10"}),y[0]||(y[0]=p("p",{class:"text-sm"},"ยังไม่มีคำขอแยกตามหน่วยงานในปีงบนี้",-1))]))])]))}});async function hn(t,e){const n=new URLSearchParams({fiscal_year:String(t),dimension:e});return lt(`/analytics/comparison?${n.toString()}`)}async function gn(t){const e=new URLSearchParams({fiscal_year:String(t)});return lt(`/analytics/forecast?${e.toString()}`)}async function mn(t){const e=new URLSearchParams({fiscal_year:String(t)});return lt(`/analytics/request-vs-approved?${e.toString()}`)}const dt=["analytics"];function yn(t,e,n){return it({queryKey:c(()=>[...dt,"comparison",t.value,e.value]),queryFn:async()=>{const o=await hn(t.value,e.value);if(!o.success||!o.data)throw new Error(o.error??"โหลดข้อมูลเปรียบเทียบไม่สำเร็จ");return o.data},enabled:c(()=>n.value&&!!t.value)})}function kn(t,e){return it({queryKey:c(()=>[...dt,"forecast",t.value]),queryFn:async()=>{const n=await gn(t.value);if(!n.success||!n.data)throw new Error(n.error??"โหลดข้อมูล Forecast ไม่สำเร็จ");return n.data},enabled:c(()=>e.value&&!!t.value)})}function $n(t,e){return it({queryKey:c(()=>[...dt,"request-vs-approved",t.value]),queryFn:async()=>{const n=await mn(t.value);if(!n.success||!n.data)throw new Error(n.error??"โหลดข้อมูลคำขอ vs อนุมัติไม่สำเร็จ");return n.data},enabled:c(()=>e.value&&!!t.value)})}const xn={class:"space-y-6"},wn={class:"flex flex-wrap items-end justify-between gap-4"},Tn={class:"flex flex-col items-end gap-1"},Bn={key:0,class:"text-xs text-dark-muted"},_n={key:0,class:"flex items-center gap-2 rounded-lg border border-sky-800 bg-sky-950/40 px-4 py-2.5 text-sm text-sky-300"},Cn={class:"space-y-4"},Sn={key:0,class:"rounded-xl border border-rose-800 bg-rose-950/40 p-4 text-sm text-rose-300"},Ln={key:1,class:"h-72 animate-pulse rounded-xl border border-dark-border bg-dark-card"},An={key:2,class:"flex flex-col items-center justify-center gap-2 rounded-xl border border-dark-border bg-dark-card py-16 text-dark-muted"},Pn={key:3,class:"rounded-xl border border-dark-border bg-dark-card p-5 shadow-sm"},On={key:0,class:"rounded-xl border border-rose-800 bg-rose-950/40 p-4 text-sm text-rose-300"},In={key:1,class:"h-72 animate-pulse rounded-xl border border-dark-border bg-dark-card"},Fn={key:2,class:"flex flex-col items-center justify-center gap-2 rounded-xl border border-dark-border bg-dark-card py-16 text-dark-muted"},Nn={key:3,class:"rounded-xl border border-dark-border bg-dark-card p-5 shadow-sm"},Vn={key:0,class:"rounded-xl border border-rose-800 bg-rose-950/40 p-4 text-sm text-rose-300"},En={key:1,class:"h-72 animate-pulse rounded-xl border border-dark-border bg-dark-card"},Gn=U({__name:"AnalyticsPage",setup(t){const e=(()=>{const v=new Date,i=v.getFullYear()+543;return v.getMonth()+1>=10?i+1:i})(),n=I(e),o=I("quarter"),r=I("comparison"),a=Xt(),u=c(()=>{const v=new Set([e]);for(const i of a.data.value??[])v.add(i.fiscal_year);return[...v].sort((i,k)=>k-i).map(i=>({label:`ปีงบ ${i}`,value:i}))}),s=[{label:"รายปี",value:"year"},{label:"รายไตรมาส",value:"quarter"},{label:"รายเดือน",value:"month"}],f=c(()=>r.value==="comparison"&&o.value==="year"),$=c(()=>r.value==="comparison"),x=c(()=>r.value==="forecast"),g=c(()=>r.value==="request"),y=yn(n,o,$),B=kn(n,x),A=$n(n,g),_t=c(()=>{var v;return(((v=y.data.value)==null?void 0:v.rows)??[]).map(i=>i.fiscal_year!=null?`ปีงบ ${i.fiscal_year}`:i.label??"")}),Ct=c(()=>{var v;return(((v=y.data.value)==null?void 0:v.rows)??[]).map(i=>i.budget)}),St=c(()=>{var v;return(((v=y.data.value)==null?void 0:v.rows)??[]).map(i=>i.disbursed)}),Lt=c(()=>{var v;return(((v=y.data.value)==null?void 0:v.rows)??[]).length>0}),C=c(()=>B.data.value),At=c(()=>{var v;return(((v=C.value)==null?void 0:v.labels)??[]).length>0}),ct=c(()=>A.data.value),Pt=c(()=>{var i,k,K;return[(i=y.data.value)==null?void 0:i.scope,(k=B.data.value)==null?void 0:k.scope,(K=A.data.value)==null?void 0:K.scope].includes("subtree")});return(v,i)=>(l(),d("div",xn,[p("header",wn,[i[3]||(i[3]=p("div",null,[p("h1",{class:"text-2xl font-bold text-white"},"รายงานวิเคราะห์"),p("p",{class:"mt-1 text-sm text-dark-muted"}," เปรียบเทียบงบจัดสรรกับเบิกจ่าย พยากรณ์เทียบจริง และคำขอเทียบอนุมัติ ")],-1)),p("div",Tn,[m(b(It),{modelValue:n.value,"onUpdate:modelValue":i[0]||(i[0]=k=>n.value=k),options:u.value,"option-label":"label","option-value":"value",class:"w-40",disabled:f.value},null,8,["modelValue","options","disabled"]),f.value?(l(),d("p",Bn," ตัวเลือกปีไม่มีผลในมุมมองรายปี ")):_("",!0)])]),Pt.value?(l(),d("div",_n,[m(b(ne),{class:"h-4 w-4 shrink-0"}),i[4]||(i[4]=p("span",null,"แสดงเฉพาะข้อมูลตามสิทธิ์หน่วยงานของคุณ",-1))])):_("",!0),m(b(xt),{value:r.value,"onUpdate:value":i[2]||(i[2]=k=>r.value=k)},{default:w(()=>[m(b(Tt),null,{default:w(()=>[m(b(q),{value:"comparison"},{default:w(()=>[...i[5]||(i[5]=[G("เปรียบเทียบ",-1)])]),_:1}),m(b(q),{value:"forecast"},{default:w(()=>[...i[6]||(i[6]=[G("Forecast vs จริง",-1)])]),_:1}),m(b(q),{value:"request"},{default:w(()=>[...i[7]||(i[7]=[G("คำขอ vs อนุมัติ",-1)])]),_:1})]),_:1}),m(b(Bt),null,{default:w(()=>[m(b(H),{value:"comparison"},{default:w(()=>{var k;return[p("div",Cn,[m(b($t),{modelValue:o.value,"onUpdate:modelValue":i[1]||(i[1]=K=>o.value=K),options:s,"option-label":"label","option-value":"value","allow-empty":!1,"aria-label":"เลือกมุมมองการเปรียบเทียบ"},null,8,["modelValue"]),b(y).isError.value?(l(),d("div",Sn," โหลดข้อมูลเปรียบเทียบไม่สำเร็จ — "+P((k=b(y).error.value)==null?void 0:k.message),1)):b(y).isLoading.value?(l(),d("div",Ln)):Lt.value?(l(),d("section",Pn,[m(an,{labels:_t.value,budget:Ct.value,disbursed:St.value},null,8,["labels","budget","disbursed"])])):(l(),d("div",An,[m(b(X),{class:"h-10 w-10"}),i[8]||(i[8]=p("p",{class:"text-sm"},"ยังไม่มีข้อมูลสำหรับการเปรียบเทียบ",-1))]))])]}),_:1}),m(b(H),{value:"forecast"},{default:w(()=>{var k;return[b(B).isError.value?(l(),d("div",On," โหลดข้อมูล Forecast ไม่สำเร็จ — "+P((k=b(B).error.value)==null?void 0:k.message),1)):b(B).isLoading.value?(l(),d("div",In)):At.value?C.value?(l(),d("section",Nn,[m(un,{labels:C.value.labels,"forecast-monthly":C.value.forecast_monthly,"actual-monthly":C.value.actual_monthly,"forecast-cumulative":C.value.forecast_cumulative,"actual-cumulative":C.value.actual_cumulative},null,8,["labels","forecast-monthly","actual-monthly","forecast-cumulative","actual-cumulative"])])):_("",!0):(l(),d("div",Fn,[m(b(X),{class:"h-10 w-10"}),i[9]||(i[9]=p("p",{class:"text-sm"},"ยังไม่มีข้อมูลพยากรณ์ในปีงบนี้",-1))]))]}),_:1}),m(b(H),{value:"request"},{default:w(()=>{var k;return[b(A).isError.value?(l(),d("div",Vn," โหลดข้อมูลคำขอ vs อนุมัติไม่สำเร็จ — "+P((k=b(A).error.value)==null?void 0:k.message),1)):b(A).isLoading.value?(l(),d("div",En)):ct.value?(l(),S(vn,{key:2,report:ct.value},null,8,["report"])):_("",!0)]}),_:1})]),_:1})]),_:1},8,["value"])]))}});export{Gn as default};
