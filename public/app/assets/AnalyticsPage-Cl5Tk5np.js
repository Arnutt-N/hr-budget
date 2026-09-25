import{a as It,s as Ft}from"./index-DmF3qbCV.js";import{B as P,a2 as gt,y as at,C as N,n as u,c,b as r,J as C,s as h,K as V,h as A,t as B,a3 as F,a4 as R,D as Nt,F as I,G as j,j as S,I as Vt,i as T,a5 as G,a6 as Kt,a7 as U,a8 as zt,a9 as D,aa as Rt,ab as mt,z as M,ac as Dt,ad as E,ae as Et,d as Q,e as m,g as b,O as p,r as q,Q as ot,k as J}from"./index-DyIBQ9Fj.js";import{R as Y,f as Z,b as z,s as qt}from"./index-DMeWpJEN.js";import{a as yt}from"./index-CXb64q6l.js";import{_ as X}from"./QueryErrorState.vue_vue_type_script_setup_true_lang-BU3Jixd6.js";import{C as rt,a as st,L as it,B as kt,p as lt,b as ut,u as dt,c as $t,d as Ht,e as Wt,f as jt,i as Ut,g as Mt,_ as Qt,P as Yt}from"./usePrefersReducedMotion-C4GAHAo2.js";import{I as tt}from"./inbox-B0-eEja6.js";import{F as Zt}from"./file-text-CGrO94SW.js";import{c as wt}from"./createLucideIcon-CS-ifzKk.js";import{u as ct}from"./useQuery-DGRD5ryj.js";import{u as Gt}from"./useBudgetExecution-CB3M0XAh.js";import"./index-PhUvF1eA.js";import"./index-FXvGaelt.js";import"./index-B8luFFDE.js";/**
 * @license @lucide/vue v1.17.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Jt=[["circle",{cx:"12",cy:"12",r:"10",key:"1mglay"}],["path",{d:"m9 12 2 2 4-4",key:"dzmm74"}]],Xt=wt("circle-check",Jt);/**
 * @license @lucide/vue v1.17.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const te=[["circle",{cx:"12",cy:"12",r:"10",key:"1mglay"}],["path",{d:"M12 16v-4",key:"1dtifu"}],["path",{d:"M12 8h.01",key:"e9boi3"}]],ee=wt("info",te);var ne=`
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
`,ae={root:function(e){var n=e.instance,o=e.props;return["p-togglebutton p-component",{"p-togglebutton-checked":n.active,"p-invalid":n.$invalid,"p-togglebutton-fluid":o.fluid,"p-togglebutton-sm p-inputfield-sm":o.size==="small","p-togglebutton-lg p-inputfield-lg":o.size==="large"}]},content:"p-togglebutton-content",icon:"p-togglebutton-icon",label:"p-togglebutton-label"},oe=P.extend({name:"togglebutton",style:ne,classes:ae}),re={name:"BaseToggleButton",extends:yt,props:{onIcon:String,offIcon:String,onLabel:{type:String,default:"Yes"},offLabel:{type:String,default:"No"},readonly:{type:Boolean,default:!1},tabindex:{type:Number,default:null},ariaLabelledby:{type:String,default:null},ariaLabel:{type:String,default:null},size:{type:String,default:null},fluid:{type:Boolean,default:null}},style:oe,provide:function(){return{$pcToggleButton:this,$parentInstance:this}}};function K(t){"@babel/helpers - typeof";return K=typeof Symbol=="function"&&typeof Symbol.iterator=="symbol"?function(e){return typeof e}:function(e){return e&&typeof Symbol=="function"&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},K(t)}function se(t,e,n){return(e=ie(e))in t?Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[e]=n,t}function ie(t){var e=le(t,"string");return K(e)=="symbol"?e:e+""}function le(t,e){if(K(t)!="object"||!t)return t;var n=t[Symbol.toPrimitive];if(n!==void 0){var o=n.call(t,e);if(K(o)!="object")return o;throw new TypeError("@@toPrimitive must return a primitive value.")}return(e==="string"?String:Number)(t)}var xt={name:"ToggleButton",extends:re,inheritAttrs:!1,emits:["change"],methods:{getPTOptions:function(e){var n=e==="root"?this.ptmi:this.ptm;return n(e,{context:{active:this.active,disabled:this.disabled}})},onChange:function(e){!this.disabled&&!this.readonly&&(this.writeValue(!this.d_value,e),this.$emit("change",e))},onBlur:function(e){var n,o;(n=(o=this.formField).onBlur)===null||n===void 0||n.call(o,e)}},computed:{active:function(){return this.d_value===!0},hasLabel:function(){return gt(this.onLabel)&&gt(this.offLabel)},label:function(){return this.hasLabel?this.d_value?this.onLabel:this.offLabel:" "},dataP:function(){return Z(se({checked:this.active,invalid:this.$invalid},this.size,this.size))}},directives:{ripple:Y}},ue=["tabindex","disabled","aria-pressed","aria-label","aria-labelledby","data-p-checked","data-p-disabled","data-p"],de=["data-p"];function ce(t,e,n,o,i,a){var d=at("ripple");return N((u(),c("button",h({type:"button",class:t.cx("root"),tabindex:t.tabindex,disabled:t.disabled,"aria-pressed":t.d_value,onClick:e[0]||(e[0]=function(){return a.onChange&&a.onChange.apply(a,arguments)}),onBlur:e[1]||(e[1]=function(){return a.onBlur&&a.onBlur.apply(a,arguments)})},a.getPTOptions("root"),{"aria-label":t.ariaLabel,"aria-labelledby":t.ariaLabelledby,"data-p-checked":a.active,"data-p-disabled":t.disabled,"data-p":a.dataP}),[r("span",h({class:t.cx("content")},a.getPTOptions("content"),{"data-p":a.dataP}),[C(t.$slots,"default",{},function(){return[C(t.$slots,"icon",{value:t.d_value,class:V(t.cx("icon"))},function(){return[t.onIcon||t.offIcon?(u(),c("span",h({key:0,class:[t.cx("icon"),t.d_value?t.onIcon:t.offIcon]},a.getPTOptions("icon")),null,16)):A("",!0)]}),r("span",h({class:t.cx("label")},a.getPTOptions("label")),B(a.label),17)]})],16,de)],16,ue)),[[d]])}xt.render=ce;var be=`
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
`,pe={root:function(e){var n=e.props,o=e.instance;return["p-selectbutton p-component",{"p-invalid":o.$invalid,"p-selectbutton-fluid":n.fluid}]}},fe=P.extend({name:"selectbutton",style:be,classes:pe}),ve={name:"BaseSelectButton",extends:yt,props:{options:Array,optionLabel:null,optionValue:null,optionDisabled:null,multiple:Boolean,allowEmpty:{type:Boolean,default:!0},dataKey:null,ariaLabelledby:{type:String,default:null},size:{type:String,default:null},fluid:{type:Boolean,default:null}},style:fe,provide:function(){return{$pcSelectButton:this,$parentInstance:this}}};function he(t,e){var n=typeof Symbol<"u"&&t[Symbol.iterator]||t["@@iterator"];if(!n){if(Array.isArray(t)||(n=Tt(t))||e){n&&(t=n);var o=0,i=function(){};return{s:i,n:function(){return o>=t.length?{done:!0}:{done:!1,value:t[o++]}},e:function(y){throw y},f:i}}throw new TypeError(`Invalid attempt to iterate non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}var a,d=!0,l=!1;return{s:function(){n=n.call(t)},n:function(){var y=n.next();return d=y.done,y},e:function(y){l=!0,a=y},f:function(){try{d||n.return==null||n.return()}finally{if(l)throw a}}}}function ge(t){return ke(t)||ye(t)||Tt(t)||me()}function me(){throw new TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function Tt(t,e){if(t){if(typeof t=="string")return et(t,e);var n={}.toString.call(t).slice(8,-1);return n==="Object"&&t.constructor&&(n=t.constructor.name),n==="Map"||n==="Set"?Array.from(t):n==="Arguments"||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?et(t,e):void 0}}function ye(t){if(typeof Symbol<"u"&&t[Symbol.iterator]!=null||t["@@iterator"]!=null)return Array.from(t)}function ke(t){if(Array.isArray(t))return et(t)}function et(t,e){(e==null||e>t.length)&&(e=t.length);for(var n=0,o=Array(e);n<e;n++)o[n]=t[n];return o}var Bt={name:"SelectButton",extends:ve,inheritAttrs:!1,emits:["change"],methods:{getOptionLabel:function(e){return this.optionLabel?R(e,this.optionLabel):e},getOptionValue:function(e){return this.optionValue?R(e,this.optionValue):e},getOptionRenderKey:function(e){return this.dataKey?R(e,this.dataKey):this.getOptionLabel(e)},isOptionDisabled:function(e){return this.optionDisabled?R(e,this.optionDisabled):!1},isOptionReadonly:function(e){if(this.allowEmpty)return!1;var n=this.isSelected(e);return this.multiple?n&&this.d_value.length===1:n},onOptionSelect:function(e,n,o){var i=this;if(!(this.disabled||this.isOptionDisabled(n)||this.isOptionReadonly(n))){var a=this.isSelected(n),d=this.getOptionValue(n),l;if(this.multiple)if(a){if(l=this.d_value.filter(function(f){return!F(f,d,i.equalityKey)}),!this.allowEmpty&&l.length===0)return}else l=this.d_value?[].concat(ge(this.d_value),[d]):[d];else{if(a&&!this.allowEmpty)return;l=a?null:d}this.writeValue(l,e),this.$emit("change",{originalEvent:e,value:l})}},isSelected:function(e){var n=!1,o=this.getOptionValue(e);if(this.multiple){if(this.d_value){var i=he(this.d_value),a;try{for(i.s();!(a=i.n()).done;){var d=a.value;if(F(d,o,this.equalityKey)){n=!0;break}}}catch(l){i.e(l)}finally{i.f()}}}else n=F(this.d_value,o,this.equalityKey);return n}},computed:{equalityKey:function(){return this.optionValue?null:this.dataKey},dataP:function(){return Z({invalid:this.$invalid})}},directives:{ripple:Y},components:{ToggleButton:xt}},$e=["aria-labelledby","data-p"];function we(t,e,n,o,i,a){var d=Nt("ToggleButton");return u(),c("div",h({class:t.cx("root"),role:"group","aria-labelledby":t.ariaLabelledby},t.ptmi("root"),{"data-p":a.dataP}),[(u(!0),c(I,null,j(t.options,function(l,f){return u(),S(d,{key:a.getOptionRenderKey(l),modelValue:a.isSelected(l),onLabel:a.getOptionLabel(l),offLabel:a.getOptionLabel(l),disabled:t.disabled||a.isOptionDisabled(l),unstyled:t.unstyled,size:t.size,readonly:a.isOptionReadonly(l),onChange:function(w){return a.onOptionSelect(w,l,f)},pt:t.ptm("pcToggleButton")},Vt({_:2},[t.$slots.option?{name:"default",fn:T(function(){return[C(t.$slots,"option",{option:l,index:f},function(){return[r("span",h({ref_for:!0},t.ptm("pcToggleButton").label),B(a.getOptionLabel(l)),17)]})]}),key:"0"}:void 0]),1032,["modelValue","onLabel","offLabel","disabled","unstyled","size","readonly","onChange","pt"])}),128))],16,$e)}Bt.render=we;var xe=`
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
`,Te={root:function(e){var n=e.props;return["p-tabs p-component",{"p-tabs-scrollable":n.scrollable}]}},Be=P.extend({name:"tabs",style:xe,classes:Te}),Ce={name:"BaseTabs",extends:z,props:{value:{type:[String,Number],default:void 0},lazy:{type:Boolean,default:!1},scrollable:{type:Boolean,default:!1},showNavigators:{type:Boolean,default:!0},tabindex:{type:Number,default:0},selectOnFocus:{type:Boolean,default:!1}},style:Be,provide:function(){return{$pcTabs:this,$parentInstance:this}}},Ct={name:"Tabs",extends:Ce,inheritAttrs:!1,emits:["update:value"],data:function(){return{d_value:this.value}},watch:{value:function(e){this.d_value=e}},methods:{updateValue:function(e){this.d_value!==e&&(this.d_value=e,this.$emit("update:value",e))},isVertical:function(){return this.orientation==="vertical"}}};function _e(t,e,n,o,i,a){return u(),c("div",h({class:t.cx("root")},t.ptmi("root")),[C(t.$slots,"default")],16)}Ct.render=_e;var _t={name:"ChevronLeftIcon",extends:qt};function Se(t){return Oe(t)||Pe(t)||Ae(t)||Le()}function Le(){throw new TypeError(`Invalid attempt to spread non-iterable instance.
In order to be iterable, non-array objects must have a [Symbol.iterator]() method.`)}function Ae(t,e){if(t){if(typeof t=="string")return nt(t,e);var n={}.toString.call(t).slice(8,-1);return n==="Object"&&t.constructor&&(n=t.constructor.name),n==="Map"||n==="Set"?Array.from(t):n==="Arguments"||/^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)?nt(t,e):void 0}}function Pe(t){if(typeof Symbol<"u"&&t[Symbol.iterator]!=null||t["@@iterator"]!=null)return Array.from(t)}function Oe(t){if(Array.isArray(t))return nt(t)}function nt(t,e){(e==null||e>t.length)&&(e=t.length);for(var n=0,o=Array(e);n<e;n++)o[n]=t[n];return o}function Ie(t,e,n,o,i,a){return u(),c("svg",h({width:"14",height:"14",viewBox:"0 0 14 14",fill:"none",xmlns:"http://www.w3.org/2000/svg"},t.pti()),Se(e[0]||(e[0]=[r("path",{d:"M9.61296 13C9.50997 13.0005 9.40792 12.9804 9.3128 12.9409C9.21767 12.9014 9.13139 12.8433 9.05902 12.7701L3.83313 7.54416C3.68634 7.39718 3.60388 7.19795 3.60388 6.99022C3.60388 6.78249 3.68634 6.58325 3.83313 6.43628L9.05902 1.21039C9.20762 1.07192 9.40416 0.996539 9.60724 1.00012C9.81032 1.00371 10.0041 1.08597 10.1477 1.22959C10.2913 1.37322 10.3736 1.56698 10.3772 1.77005C10.3808 1.97313 10.3054 2.16968 10.1669 2.31827L5.49496 6.99022L10.1669 11.6622C10.3137 11.8091 10.3962 12.0084 10.3962 12.2161C10.3962 12.4238 10.3137 12.6231 10.1669 12.7701C10.0945 12.8433 10.0083 12.9014 9.91313 12.9409C9.81801 12.9804 9.71596 13.0005 9.61296 13Z",fill:"currentColor"},null,-1)])),16)}_t.render=Ie;var Fe={root:"p-tablist",content:"p-tablist-content p-tablist-viewport",tabList:"p-tablist-tab-list",activeBar:"p-tablist-active-bar",prevButton:"p-tablist-prev-button p-tablist-nav-button",nextButton:"p-tablist-next-button p-tablist-nav-button"},Ne=P.extend({name:"tablist",classes:Fe}),Ve={name:"BaseTabList",extends:z,props:{},style:Ne,provide:function(){return{$pcTabList:this,$parentInstance:this}}},St={name:"TabList",extends:Ve,inheritAttrs:!1,inject:["$pcTabs"],data:function(){return{isPrevButtonEnabled:!1,isNextButtonEnabled:!0}},resizeObserver:void 0,inkBarObserver:void 0,watch:{showNavigators:function(e){e?this.bindResizeObserver():this.unbindResizeObserver()},activeValue:{flush:"post",handler:function(){this.updateInkBar(),this.bindInkBarObserver()}}},mounted:function(){var e=this;setTimeout(function(){e.updateInkBar(),e.bindInkBarObserver()},150),this.showNavigators&&(this.updateButtonState(),this.bindResizeObserver())},updated:function(){this.showNavigators&&this.updateButtonState()},beforeUnmount:function(){this.unbindResizeObserver(),this.unbindInkBarObserver()},methods:{onScroll:function(e){this.showNavigators&&this.updateButtonState(),e.preventDefault()},onPrevButtonClick:function(){var e=this.$refs.content,n=this.getVisibleButtonWidths(),o=G(e)-n,i=Math.abs(e.scrollLeft),a=o*.8,d=i-a,l=Math.max(d,0);e.scrollLeft=mt(e)?-1*l:l},onNextButtonClick:function(){var e=this.$refs.content,n=this.getVisibleButtonWidths(),o=G(e)-n,i=Math.abs(e.scrollLeft),a=o*.8,d=i+a,l=e.scrollWidth-o,f=Math.min(d,l);e.scrollLeft=mt(e)?-1*f:f},bindResizeObserver:function(){var e=this;this.resizeObserver=new ResizeObserver(function(){return e.updateButtonState()}),this.resizeObserver.observe(this.$refs.list)},unbindResizeObserver:function(){var e;(e=this.resizeObserver)===null||e===void 0||e.unobserve(this.$refs.list),this.resizeObserver=void 0},bindInkBarObserver:function(){var e=this;this.unbindInkBarObserver();var n=this.$refs.content,o=U(n,'[data-pc-name="tab"][data-p-active="true"]');o&&(this.inkBarObserver=new ResizeObserver(function(){return e.updateInkBar()}),this.inkBarObserver.observe(o))},unbindInkBarObserver:function(){var e;(e=this.inkBarObserver)===null||e===void 0||e.disconnect(),this.inkBarObserver=void 0},updateInkBar:function(){var e=this.$refs,n=e.content,o=e.inkbar,i=e.tabs;if(o){var a=U(n,'[data-pc-name="tab"][data-p-active="true"]');this.$pcTabs.isVertical()?(o.style.height=zt(a)+"px",o.style.top=D(a).top-D(i).top+"px"):(o.style.width=Rt(a)+"px",o.style.left=D(a).left-D(i).left+"px")}},updateButtonState:function(){var e=this.$refs,n=e.list,o=e.content,i=o.scrollTop,a=o.scrollWidth,d=o.scrollHeight,l=o.offsetWidth,f=o.offsetHeight,y=Math.abs(o.scrollLeft),w=[G(o),Kt(o)],g=w[0],k=w[1];this.$pcTabs.isVertical()?(this.isPrevButtonEnabled=i!==0,this.isNextButtonEnabled=n.offsetHeight>=f&&parseInt(i)!==d-k):(this.isPrevButtonEnabled=y!==0,this.isNextButtonEnabled=n.offsetWidth>=l&&parseInt(y)!==a-g)},getVisibleButtonWidths:function(){var e=this.$refs,n=e.prevButton,o=e.nextButton,i=0;return this.showNavigators&&(i=((n==null?void 0:n.offsetWidth)||0)+((o==null?void 0:o.offsetWidth)||0)),i}},computed:{templates:function(){return this.$pcTabs.$slots},activeValue:function(){return this.$pcTabs.d_value},showNavigators:function(){return this.$pcTabs.showNavigators},prevButtonAriaLabel:function(){return this.$primevue.config.locale.aria?this.$primevue.config.locale.aria.previous:void 0},nextButtonAriaLabel:function(){return this.$primevue.config.locale.aria?this.$primevue.config.locale.aria.next:void 0},dataP:function(){return Z({scrollable:this.$pcTabs.scrollable})}},components:{ChevronLeftIcon:_t,ChevronRightIcon:It},directives:{ripple:Y}},Ke=["data-p"],ze=["aria-label","tabindex"],Re=["data-p"],De=["aria-orientation"],Ee=["aria-label","tabindex"];function qe(t,e,n,o,i,a){var d=at("ripple");return u(),c("div",h({ref:"list",class:t.cx("root"),"data-p":a.dataP},t.ptmi("root")),[a.showNavigators&&i.isPrevButtonEnabled?N((u(),c("button",h({key:0,ref:"prevButton",type:"button",class:t.cx("prevButton"),"aria-label":a.prevButtonAriaLabel,tabindex:a.$pcTabs.tabindex,onClick:e[0]||(e[0]=function(){return a.onPrevButtonClick&&a.onPrevButtonClick.apply(a,arguments)})},t.ptm("prevButton"),{"data-pc-group-section":"navigator"}),[(u(),S(M(a.templates.previcon||"ChevronLeftIcon"),h({"aria-hidden":"true"},t.ptm("prevIcon")),null,16))],16,ze)),[[d]]):A("",!0),r("div",h({ref:"content",class:t.cx("content"),onScroll:e[1]||(e[1]=function(){return a.onScroll&&a.onScroll.apply(a,arguments)}),"data-p":a.dataP},t.ptm("content")),[r("div",h({ref:"tabs",class:t.cx("tabList"),role:"tablist","aria-orientation":a.$pcTabs.orientation||"horizontal"},t.ptm("tabList")),[C(t.$slots,"default"),r("span",h({ref:"inkbar",class:t.cx("activeBar"),role:"presentation","aria-hidden":"true"},t.ptm("activeBar")),null,16)],16,De)],16,Re),a.showNavigators&&i.isNextButtonEnabled?N((u(),c("button",h({key:1,ref:"nextButton",type:"button",class:t.cx("nextButton"),"aria-label":a.nextButtonAriaLabel,tabindex:a.$pcTabs.tabindex,onClick:e[2]||(e[2]=function(){return a.onNextButtonClick&&a.onNextButtonClick.apply(a,arguments)})},t.ptm("nextButton"),{"data-pc-group-section":"navigator"}),[(u(),S(M(a.templates.nexticon||"ChevronRightIcon"),h({"aria-hidden":"true"},t.ptm("nextIcon")),null,16))],16,Ee)),[[d]]):A("",!0)],16,Ke)}St.render=qe;var He={root:function(e){var n=e.instance,o=e.props;return["p-tab",{"p-tab-active":n.active,"p-disabled":o.disabled}]}},We=P.extend({name:"tab",classes:He}),je={name:"BaseTab",extends:z,props:{value:{type:[String,Number],default:void 0},disabled:{type:Boolean,default:!1},as:{type:[String,Object],default:"BUTTON"},asChild:{type:Boolean,default:!1}},style:We,provide:function(){return{$pcTab:this,$parentInstance:this}}},H={name:"Tab",extends:je,inheritAttrs:!1,inject:["$pcTabs","$pcTabList"],methods:{onFocus:function(){this.$pcTabs.selectOnFocus&&this.changeActiveValue()},onClick:function(){this.changeActiveValue()},onKeydown:function(e){switch(e.code){case"ArrowRight":this.onArrowRightKey(e);break;case"ArrowLeft":this.onArrowLeftKey(e);break;case"Home":this.onHomeKey(e);break;case"End":this.onEndKey(e);break;case"PageDown":this.onPageDownKey(e);break;case"PageUp":this.onPageUpKey(e);break;case"Enter":case"NumpadEnter":case"Space":this.onEnterKey(e);break}},onArrowRightKey:function(e){var n=this.findNextTab(e.currentTarget);n?this.changeFocusedTab(e,n):this.onHomeKey(e),e.preventDefault()},onArrowLeftKey:function(e){var n=this.findPrevTab(e.currentTarget);n?this.changeFocusedTab(e,n):this.onEndKey(e),e.preventDefault()},onHomeKey:function(e){var n=this.findFirstTab();this.changeFocusedTab(e,n),e.preventDefault()},onEndKey:function(e){var n=this.findLastTab();this.changeFocusedTab(e,n),e.preventDefault()},onPageDownKey:function(e){this.scrollInView(this.findLastTab()),e.preventDefault()},onPageUpKey:function(e){this.scrollInView(this.findFirstTab()),e.preventDefault()},onEnterKey:function(e){this.changeActiveValue()},findNextTab:function(e){var n=arguments.length>1&&arguments[1]!==void 0?arguments[1]:!1,o=n?e:e.nextElementSibling;return o?E(o,"data-p-disabled")||E(o,"data-pc-section")==="activebar"?this.findNextTab(o):U(o,'[data-pc-name="tab"]'):null},findPrevTab:function(e){var n=arguments.length>1&&arguments[1]!==void 0?arguments[1]:!1,o=n?e:e.previousElementSibling;return o?E(o,"data-p-disabled")||E(o,"data-pc-section")==="activebar"?this.findPrevTab(o):U(o,'[data-pc-name="tab"]'):null},findFirstTab:function(){return this.findNextTab(this.$pcTabList.$refs.tabs.firstElementChild,!0)},findLastTab:function(){return this.findPrevTab(this.$pcTabList.$refs.tabs.lastElementChild,!0)},changeActiveValue:function(){this.$pcTabs.updateValue(this.value)},changeFocusedTab:function(e,n){Dt(n),this.scrollInView(n)},scrollInView:function(e){var n;e==null||(n=e.scrollIntoView)===null||n===void 0||n.call(e,{block:"nearest"})}},computed:{active:function(){var e;return F((e=this.$pcTabs)===null||e===void 0?void 0:e.d_value,this.value)},id:function(){var e;return"".concat((e=this.$pcTabs)===null||e===void 0?void 0:e.$id,"_tab_").concat(this.value)},ariaControls:function(){var e;return"".concat((e=this.$pcTabs)===null||e===void 0?void 0:e.$id,"_tabpanel_").concat(this.value)},attrs:function(){return h(this.asAttrs,this.a11yAttrs,this.ptmi("root",this.ptParams))},asAttrs:function(){return this.as==="BUTTON"?{type:"button",disabled:this.disabled}:void 0},a11yAttrs:function(){return{id:this.id,tabindex:this.active?this.$pcTabs.tabindex:-1,role:"tab","aria-selected":this.active,"aria-controls":this.ariaControls,"data-pc-name":"tab","data-p-disabled":this.disabled,"data-p-active":this.active,onFocus:this.onFocus,onKeydown:this.onKeydown}},ptParams:function(){return{context:{active:this.active}}},dataP:function(){return Z({active:this.active})}},directives:{ripple:Y}};function Ue(t,e,n,o,i,a){var d=at("ripple");return t.asChild?C(t.$slots,"default",{key:1,dataP:a.dataP,class:V(t.cx("root")),active:a.active,a11yAttrs:a.a11yAttrs,onClick:a.onClick}):N((u(),S(M(t.as),h({key:0,class:t.cx("root"),"data-p":a.dataP,onClick:a.onClick},a.attrs),{default:T(function(){return[C(t.$slots,"default")]}),_:3},16,["class","data-p","onClick"])),[[d]])}H.render=Ue;var Me={root:"p-tabpanels"},Qe=P.extend({name:"tabpanels",classes:Me}),Ye={name:"BaseTabPanels",extends:z,props:{},style:Qe,provide:function(){return{$pcTabPanels:this,$parentInstance:this}}},Lt={name:"TabPanels",extends:Ye,inheritAttrs:!1};function Ze(t,e,n,o,i,a){return u(),c("div",h({class:t.cx("root"),role:"presentation"},t.ptmi("root")),[C(t.$slots,"default")],16)}Lt.render=Ze;var Ge={root:function(e){var n=e.instance;return["p-tabpanel",{"p-tabpanel-active":n.active}]}},Je=P.extend({name:"tabpanel",classes:Ge}),Xe={name:"BaseTabPanel",extends:z,props:{value:{type:[String,Number],default:void 0},as:{type:[String,Object],default:"DIV"},asChild:{type:Boolean,default:!1},header:null,headerStyle:null,headerClass:null,headerProps:null,headerActionProps:null,contentStyle:null,contentClass:null,contentProps:null,disabled:Boolean},style:Je,provide:function(){return{$pcTabPanel:this,$parentInstance:this}}},W={name:"TabPanel",extends:Xe,inheritAttrs:!1,inject:["$pcTabs"],computed:{active:function(){var e;return F((e=this.$pcTabs)===null||e===void 0?void 0:e.d_value,this.value)},id:function(){var e;return"".concat((e=this.$pcTabs)===null||e===void 0?void 0:e.$id,"_tabpanel_").concat(this.value)},ariaLabelledby:function(){var e;return"".concat((e=this.$pcTabs)===null||e===void 0?void 0:e.$id,"_tab_").concat(this.value)},attrs:function(){return h(this.a11yAttrs,this.ptmi("root",this.ptParams))},a11yAttrs:function(){var e;return{id:this.id,tabindex:(e=this.$pcTabs)===null||e===void 0?void 0:e.tabindex,role:"tabpanel","aria-labelledby":this.ariaLabelledby,"data-pc-name":"tabpanel","data-p-active":this.active}},ptParams:function(){return{context:{active:this.active}}}}};function tn(t,e,n,o,i,a){var d,l;return a.$pcTabs?(u(),c(I,{key:1},[t.asChild?C(t.$slots,"default",{key:1,class:V(t.cx("root")),active:a.active,a11yAttrs:a.a11yAttrs}):(u(),c(I,{key:0},[!((d=a.$pcTabs)!==null&&d!==void 0&&d.lazy)||a.active?N((u(),S(M(t.as),h({key:0,class:t.cx("root")},a.attrs),{default:T(function(){return[C(t.$slots,"default")]}),_:3},16,["class"])),[[Et,(l=a.$pcTabs)!==null&&l!==void 0&&l.lazy?!0:a.active]]):A("",!0)],64))],64)):C(t.$slots,"default",{key:0})}W.render=tn;const en={class:"h-72"},nn=Q({__name:"ComparisonChart",props:{labels:{},budget:{},disbursed:{},title:{default:"เปรียบเทียบงบจัดสรรกับเบิกจ่าย"}},setup(t){rt.register(st,it,kt,lt,ut);const e=t,n=new Intl.NumberFormat("th-TH",{minimumFractionDigits:2,maximumFractionDigits:2}),o=new Intl.NumberFormat("th-TH",{notation:"compact",maximumFractionDigits:1}),i=dt(),a=p(()=>({labels:e.labels,datasets:[{label:"งบ/จัดสรร",data:e.budget,backgroundColor:"#0ea5e9",hoverBackgroundColor:"#38bdf8",borderRadius:6,maxBarThickness:38},{label:"เบิกจ่าย",data:e.disbursed,backgroundColor:"#10b981",hoverBackgroundColor:"#34d399",borderRadius:6,maxBarThickness:38}]})),d=p(()=>({responsive:!0,maintainAspectRatio:!1,animation:i.value?!1:void 0,plugins:{legend:{display:!0,labels:{color:"#cbd5e1",usePointStyle:!0,boxWidth:8}},tooltip:{backgroundColor:"#1e293b",borderColor:"#334155",borderWidth:1,titleColor:"#f1f5f9",bodyColor:"#94a3b8",padding:10,callbacks:{label:l=>` ${l.dataset.label}: ${n.format(Number(l.parsed.y))} บาท`}}},scales:{x:{grid:{color:"#334155"},border:{color:"#334155"},ticks:{color:"#94a3b8"}},y:{beginAtZero:!0,grid:{color:"#334155"},border:{color:"#334155"},ticks:{color:"#94a3b8",callback:l=>o.format(Number(l))}}}}));return(l,f)=>(u(),c("div",en,[m(b($t),{data:a.value,options:d.value,"aria-label":t.title},null,8,["data","options","aria-label"])]))}}),an={class:"space-y-3"},on={class:"flex justify-end"},rn={class:"inline-flex rounded-lg border border-dark-border bg-dark-bg p-0.5 text-xs"},sn={class:"h-72"},ln=Q({__name:"ForecastChart",props:{labels:{},forecastMonthly:{},actualMonthly:{},forecastCumulative:{},actualCumulative:{},title:{default:"พยากรณ์เทียบเบิกจ่ายจริง"}},setup(t){rt.register(st,it,Ht,Wt,jt,Ut,lt,ut);const e=t,n=q("monthly"),o=new Intl.NumberFormat("th-TH",{minimumFractionDigits:2,maximumFractionDigits:2}),i=new Intl.NumberFormat("th-TH",{notation:"compact",maximumFractionDigits:1}),a=dt(),d=p(()=>n.value==="monthly"?e.forecastMonthly:e.forecastCumulative),l=p(()=>n.value==="monthly"?e.actualMonthly:e.actualCumulative),f=p(()=>({labels:e.labels,datasets:[{label:"พยากรณ์ (Forecast)",data:d.value,borderColor:"#f59e0b",backgroundColor:"rgba(245, 158, 11, 0.08)",borderDash:[6,4],pointBackgroundColor:"#f59e0b",pointRadius:3,tension:.3,fill:!1},{label:"เบิกจ่ายจริง (Actual)",data:l.value,borderColor:"#10b981",backgroundColor:"rgba(16, 185, 129, 0.12)",pointBackgroundColor:"#10b981",pointRadius:3,tension:.3,fill:!0}]})),y=p(()=>({responsive:!0,maintainAspectRatio:!1,animation:a.value?!1:void 0,interaction:{mode:"index",intersect:!1},plugins:{legend:{display:!0,labels:{color:"#cbd5e1",usePointStyle:!0,boxWidth:8}},tooltip:{backgroundColor:"#1e293b",borderColor:"#334155",borderWidth:1,titleColor:"#f1f5f9",bodyColor:"#94a3b8",padding:10,callbacks:{label:w=>` ${w.dataset.label}: ${o.format(Number(w.parsed.y))} บาท`}}},scales:{x:{grid:{color:"#334155"},border:{color:"#334155"},ticks:{color:"#94a3b8"}},y:{beginAtZero:!0,grid:{color:"#334155"},border:{color:"#334155"},ticks:{color:"#94a3b8",callback:w=>i.format(Number(w))}}}}));return(w,g)=>(u(),c("div",an,[r("div",on,[r("div",rn,[r("button",{type:"button",class:V(["rounded-md px-3 py-1.5 font-medium transition",n.value==="monthly"?"bg-primary-600 text-white":"text-dark-muted hover:text-white"]),onClick:g[0]||(g[0]=k=>n.value="monthly")}," รายเดือน ",2),r("button",{type:"button",class:V(["rounded-md px-3 py-1.5 font-medium transition",n.value==="cumulative"?"bg-primary-600 text-white":"text-dark-muted hover:text-white"]),onClick:g[1]||(g[1]=k=>n.value="cumulative")}," สะสม ",2)])]),r("div",sn,[m(b(Mt),{data:f.value,options:y.value,"aria-label":t.title},null,8,["data","options","aria-label"])])]))}}),un={class:"space-y-6"},dn={class:"grid grid-cols-1 gap-4 sm:grid-cols-3"},cn={class:"rounded-xl border border-dark-border bg-dark-card p-5 shadow-sm"},bn={class:"mb-4 text-base font-semibold text-white"},pn={key:0,class:"flex h-72 flex-col items-center justify-center gap-2 text-dark-muted"},fn={key:1,class:"h-80"},vn=Q({__name:"RequestApprovalChart",props:{report:{},title:{default:"คำขอ vs อนุมัติ ตามหน่วยงาน"}},setup(t){rt.register(st,it,kt,lt,ut);const e=t,n=new Intl.NumberFormat("th-TH",{style:"currency",currency:"THB"}),o=new Intl.NumberFormat("th-TH",{minimumFractionDigits:2,maximumFractionDigits:2}),i=new Intl.NumberFormat("th-TH",{notation:"compact",maximumFractionDigits:1}),a=new Intl.NumberFormat("th-TH",{style:"percent",maximumFractionDigits:1}),d=dt(),l=p(()=>[{label:"ยอดขอ",value:n.format(e.report.requested),accent:"sky",icon:Zt},{label:"ยอดอนุมัติ",value:n.format(e.report.approved),accent:"emerald",icon:Xt},{label:"อัตราอนุมัติ",value:a.format(e.report.approval_rate),accent:"violet",icon:Yt}]),f=p(()=>e.report.by_org.length>0),y=p(()=>({labels:e.report.by_org.map(g=>g.org_name),datasets:[{label:"ยอดขอ",data:e.report.by_org.map(g=>g.requested),backgroundColor:"#0ea5e9",hoverBackgroundColor:"#38bdf8",borderRadius:6,maxBarThickness:22},{label:"ยอดอนุมัติ",data:e.report.by_org.map(g=>g.approved),backgroundColor:"#10b981",hoverBackgroundColor:"#34d399",borderRadius:6,maxBarThickness:22}]})),w=p(()=>({indexAxis:"y",responsive:!0,maintainAspectRatio:!1,animation:d.value?!1:void 0,plugins:{legend:{display:!0,labels:{color:"#cbd5e1",usePointStyle:!0,boxWidth:8}},tooltip:{backgroundColor:"#1e293b",borderColor:"#334155",borderWidth:1,titleColor:"#f1f5f9",bodyColor:"#94a3b8",padding:10,callbacks:{label:g=>` ${g.dataset.label}: ${o.format(Number(g.parsed.x))} บาท`}}},scales:{x:{beginAtZero:!0,grid:{color:"#334155"},border:{color:"#334155"},ticks:{color:"#94a3b8",callback:g=>i.format(Number(g))}},y:{grid:{display:!1},border:{color:"#334155"},ticks:{color:"#cbd5e1"}}}}));return(g,k)=>(u(),c("div",un,[r("div",dn,[(u(!0),c(I,null,j(l.value,L=>(u(),S(Qt,h({key:L.label},{ref_for:!0},L),null,16))),128))]),r("section",cn,[r("h2",bn,B(t.title),1),f.value?(u(),c("div",fn,[m(b($t),{data:y.value,options:w.value,"aria-label":t.title},null,8,["data","options","aria-label"])])):(u(),c("div",pn,[m(b(tt),{"aria-hidden":"true",class:"h-10 w-10"}),k[0]||(k[0]=r("p",{class:"text-sm"},"ยังไม่มีคำขอแยกตามหน่วยงานในปีงบนี้",-1))]))])]))}});async function hn(t,e){const n=new URLSearchParams({fiscal_year:String(t),dimension:e});return ot(`/analytics/comparison?${n.toString()}`)}async function gn(t){const e=new URLSearchParams({fiscal_year:String(t)});return ot(`/analytics/forecast?${e.toString()}`)}async function mn(t){const e=new URLSearchParams({fiscal_year:String(t)});return ot(`/analytics/request-vs-approved?${e.toString()}`)}const bt=["analytics"];function yn(t,e,n){return ct({queryKey:p(()=>[...bt,"comparison",t.value,e.value]),queryFn:async()=>{const o=await hn(t.value,e.value);if(!o.success||!o.data)throw new Error(o.error??"โหลดข้อมูลเปรียบเทียบไม่สำเร็จ");return o.data},enabled:p(()=>n.value&&!!t.value)})}function kn(t,e){return ct({queryKey:p(()=>[...bt,"forecast",t.value]),queryFn:async()=>{const n=await gn(t.value);if(!n.success||!n.data)throw new Error(n.error??"โหลดข้อมูล Forecast ไม่สำเร็จ");return n.data},enabled:p(()=>e.value&&!!t.value)})}function $n(t,e){return ct({queryKey:p(()=>[...bt,"request-vs-approved",t.value]),queryFn:async()=>{const n=await mn(t.value);if(!n.success||!n.data)throw new Error(n.error??"โหลดข้อมูลคำขอ vs อนุมัติไม่สำเร็จ");return n.data},enabled:p(()=>e.value&&!!t.value)})}const wn={class:"space-y-6"},xn={class:"flex flex-wrap items-end justify-between gap-4"},Tn={class:"flex flex-col items-end gap-1"},Bn={key:0,class:"text-xs text-dark-muted"},Cn={key:0,class:"flex items-center gap-2 rounded-lg border border-sky-800 bg-sky-950/40 px-4 py-2.5 text-sm text-sky-300"},_n={class:"space-y-4"},Sn={key:1,class:"h-72 animate-pulse rounded-xl border border-dark-border bg-dark-card"},Ln={key:2,class:"flex flex-col items-center justify-center gap-2 rounded-xl border border-dark-border bg-dark-card py-16 text-dark-muted"},An={key:3,class:"rounded-xl border border-dark-border bg-dark-card p-5 shadow-sm"},Pn={class:"sr-only"},On={scope:"row"},In={key:1,class:"h-72 animate-pulse rounded-xl border border-dark-border bg-dark-card"},Fn={key:2,class:"flex flex-col items-center justify-center gap-2 rounded-xl border border-dark-border bg-dark-card py-16 text-dark-muted"},Nn={key:3,class:"rounded-xl border border-dark-border bg-dark-card p-5 shadow-sm"},Vn={class:"sr-only"},Kn={scope:"row"},zn={key:1,class:"h-72 animate-pulse rounded-xl border border-dark-border bg-dark-card"},Xn=Q({__name:"AnalyticsPage",setup(t){const e=(()=>{const v=new Date,s=v.getFullYear()+543;return v.getMonth()+1>=10?s+1:s})(),n=q(e),o=q("quarter"),i=q("comparison"),a=Gt(),d=p(()=>{const v=new Set([e]);for(const s of a.data.value??[])v.add(s.fiscal_year);return[...v].sort((s,$)=>$-s).map(s=>({label:`ปีงบ ${s}`,value:s}))}),l=[{label:"รายปี",value:"year"},{label:"รายไตรมาส",value:"quarter"},{label:"รายเดือน",value:"month"}],f=p(()=>i.value==="comparison"&&o.value==="year"),y=p(()=>i.value==="comparison"),w=p(()=>i.value==="forecast"),g=p(()=>i.value==="request"),k=yn(n,o,y),L=kn(n,w),O=$n(n,g),pt=p(()=>{var v;return(((v=k.data.value)==null?void 0:v.rows)??[]).map(s=>s.fiscal_year!=null?`ปีงบ ${s.fiscal_year}`:s.label??"")}),ft=p(()=>{var v;return(((v=k.data.value)==null?void 0:v.rows)??[]).map(s=>s.budget)}),vt=p(()=>{var v;return(((v=k.data.value)==null?void 0:v.rows)??[]).map(s=>s.disbursed)}),At=p(()=>{var v;return(((v=k.data.value)==null?void 0:v.rows)??[]).length>0}),x=p(()=>L.data.value),Pt=p(()=>{var v;return(((v=x.value)==null?void 0:v.labels)??[]).length>0}),ht=p(()=>O.data.value),Ot=p(()=>{var s,$,_;return[(s=k.data.value)==null?void 0:s.scope,($=L.data.value)==null?void 0:$.scope,(_=O.data.value)==null?void 0:_.scope].includes("subtree")});return(v,s)=>(u(),c("div",wn,[r("header",xn,[s[3]||(s[3]=r("div",null,[r("h1",{class:"text-2xl font-bold text-white"},"รายงานวิเคราะห์"),r("p",{class:"mt-1 text-sm text-dark-muted"}," เปรียบเทียบงบจัดสรรกับเบิกจ่าย พยากรณ์เทียบจริง และคำขอเทียบอนุมัติ ")],-1)),r("div",Tn,[m(b(Ft),{modelValue:n.value,"onUpdate:modelValue":s[0]||(s[0]=$=>n.value=$),options:d.value,"option-label":"label","option-value":"value","aria-label":"เลือกปีงบประมาณ",class:"w-40",disabled:f.value},null,8,["modelValue","options","disabled"]),f.value?(u(),c("p",Bn," ตัวเลือกปีไม่มีผลในมุมมองรายปี ")):A("",!0)])]),Ot.value?(u(),c("div",Cn,[m(b(ee),{"aria-hidden":"true",class:"h-4 w-4 shrink-0"}),s[4]||(s[4]=r("span",null,"แสดงเฉพาะข้อมูลตามสิทธิ์หน่วยงานของคุณ",-1))])):A("",!0),m(b(Ct),{value:i.value,"onUpdate:value":s[2]||(s[2]=$=>i.value=$)},{default:T(()=>[m(b(St),null,{default:T(()=>[m(b(H),{value:"comparison"},{default:T(()=>[...s[5]||(s[5]=[J("เปรียบเทียบ",-1)])]),_:1}),m(b(H),{value:"forecast"},{default:T(()=>[...s[6]||(s[6]=[J("Forecast vs จริง",-1)])]),_:1}),m(b(H),{value:"request"},{default:T(()=>[...s[7]||(s[7]=[J("คำขอ vs อนุมัติ",-1)])]),_:1})]),_:1}),m(b(Lt),null,{default:T(()=>[m(b(W),{value:"comparison"},{default:T(()=>[r("div",_n,[m(b(Bt),{modelValue:o.value,"onUpdate:modelValue":s[1]||(s[1]=$=>o.value=$),options:l,"option-label":"label","option-value":"value","allow-empty":!1,"aria-label":"เลือกมุมมองการเปรียบเทียบ"},null,8,["modelValue"]),b(k).isError.value?(u(),S(X,{key:0,error:b(k).error.value,retry:()=>b(k).refetch()},null,8,["error","retry"])):b(k).isLoading.value?(u(),c("div",Sn)):At.value?(u(),c("section",An,[m(nn,{labels:pt.value,budget:ft.value,disbursed:vt.value},null,8,["labels","budget","disbursed"]),r("table",Pn,[s[9]||(s[9]=r("caption",null,"เปรียบเทียบงบจัดสรรกับเบิกจ่าย (บาท)",-1)),s[10]||(s[10]=r("thead",null,[r("tr",null,[r("th",{scope:"col"},"รายการ"),r("th",{scope:"col"},"จัดสรร (บาท)"),r("th",{scope:"col"},"เบิกจ่าย (บาท)")])],-1)),r("tbody",null,[(u(!0),c(I,null,j(pt.value,($,_)=>(u(),c("tr",{key:$},[r("th",On,B($),1),r("td",null,B(ft.value[_]),1),r("td",null,B(vt.value[_]),1)]))),128))])])])):(u(),c("div",Ln,[m(b(tt),{"aria-hidden":"true",class:"h-10 w-10"}),s[8]||(s[8]=r("p",{class:"text-sm"},"ยังไม่มีข้อมูลสำหรับการเปรียบเทียบ",-1))]))])]),_:1}),m(b(W),{value:"forecast"},{default:T(()=>[b(L).isError.value?(u(),S(X,{key:0,error:b(L).error.value,retry:()=>b(L).refetch()},null,8,["error","retry"])):b(L).isLoading.value?(u(),c("div",In)):Pt.value?x.value?(u(),c("section",Nn,[m(ln,{labels:x.value.labels,"forecast-monthly":x.value.forecast_monthly,"actual-monthly":x.value.actual_monthly,"forecast-cumulative":x.value.forecast_cumulative,"actual-cumulative":x.value.actual_cumulative},null,8,["labels","forecast-monthly","actual-monthly","forecast-cumulative","actual-cumulative"]),r("table",Vn,[s[12]||(s[12]=r("caption",null,"พยากรณ์เทียบเบิกจ่ายจริง (บาท)",-1)),s[13]||(s[13]=r("thead",null,[r("tr",null,[r("th",{scope:"col"},"เดือน"),r("th",{scope:"col"},"จริงรายเดือน (บาท)"),r("th",{scope:"col"},"พยากรณ์รายเดือน (บาท)"),r("th",{scope:"col"},"จริงสะสม (บาท)"),r("th",{scope:"col"},"พยากรณ์สะสม (บาท)")])],-1)),r("tbody",null,[(u(!0),c(I,null,j(x.value.labels,($,_)=>(u(),c("tr",{key:$},[r("th",Kn,B($),1),r("td",null,B(x.value.actual_monthly[_]),1),r("td",null,B(x.value.forecast_monthly[_]),1),r("td",null,B(x.value.actual_cumulative[_]),1),r("td",null,B(x.value.forecast_cumulative[_]),1)]))),128))])])])):A("",!0):(u(),c("div",Fn,[m(b(tt),{"aria-hidden":"true",class:"h-10 w-10"}),s[11]||(s[11]=r("p",{class:"text-sm"},"ยังไม่มีข้อมูลพยากรณ์ในปีงบนี้",-1))]))]),_:1}),m(b(W),{value:"request"},{default:T(()=>[b(O).isError.value?(u(),S(X,{key:0,error:b(O).error.value,retry:()=>b(O).refetch()},null,8,["error","retry"])):b(O).isLoading.value?(u(),c("div",zn)):ht.value?(u(),S(vn,{key:2,report:ht.value},null,8,["report"])):A("",!0)]),_:1})]),_:1})]),_:1},8,["value"])]))}});export{Xn as default};
