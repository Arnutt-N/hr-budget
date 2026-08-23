import{B as N,l as v,c as V,a as g,n as m,E as U,d as D,K as J,e as n,g as B,w as r,b as s,r as C,J as T,m as f,t as w}from"./index-CqScWOFR.js";import{s as $,a as c}from"./index-OAcTvORT.js";import{s as K}from"./index-DQIw5N9N.js";import{s as _}from"./index-CV96rPi7.js";import{s as q}from"./index-C-MtztXT.js";import{a as G}from"./index-Caqa4UW-.js";import{f as H}from"./index-C6UhRph8.js";import{_ as Q,a as z}from"./ListEmptyState.vue_vue_type_script_setup_true_lang-BNHUwGXD.js";import{f as W}from"./date-DZRqA0P5.js";import{c as X,d as Y,e as Z,f as ee,g as te}from"./useSalary-CM-00MHg.js";import"./index-DT9V9MfW.js";import"./index-CWvGeoqb.js";import"./index-BqGXsMzU.js";import"./index-ClyXDbcj.js";import"./index-0_qUTC03.js";import"./useQuery-q7DFZd8u.js";import"./useMutation-CtlztslJ.js";import"./useApi-fBmmFS7S.js";var ne=`
    .p-toggleswitch {
        display: inline-block;
        width: dt('toggleswitch.width');
        height: dt('toggleswitch.height');
    }

    .p-toggleswitch-input {
        cursor: pointer;
        appearance: none;
        position: absolute;
        top: 0;
        inset-inline-start: 0;
        width: 100%;
        height: 100%;
        padding: 0;
        margin: 0;
        opacity: 0;
        z-index: 1;
        outline: 0 none;
        border-radius: dt('toggleswitch.border.radius');
    }

    .p-toggleswitch-slider {
        cursor: pointer;
        width: 100%;
        height: 100%;
        border-width: dt('toggleswitch.border.width');
        border-style: solid;
        border-color: dt('toggleswitch.border.color');
        background: dt('toggleswitch.background');
        transition:
            background dt('toggleswitch.transition.duration'),
            color dt('toggleswitch.transition.duration'),
            border-color dt('toggleswitch.transition.duration'),
            outline-color dt('toggleswitch.transition.duration'),
            box-shadow dt('toggleswitch.transition.duration');
        border-radius: dt('toggleswitch.border.radius');
        outline-color: transparent;
        box-shadow: dt('toggleswitch.shadow');
    }

    .p-toggleswitch-handle {
        position: absolute;
        top: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        background: dt('toggleswitch.handle.background');
        color: dt('toggleswitch.handle.color');
        width: dt('toggleswitch.handle.size');
        height: dt('toggleswitch.handle.size');
        inset-inline-start: dt('toggleswitch.gap');
        margin-block-start: calc(-1 * calc(dt('toggleswitch.handle.size') / 2));
        border-radius: dt('toggleswitch.handle.border.radius');
        transition:
            background dt('toggleswitch.transition.duration'),
            color dt('toggleswitch.transition.duration'),
            inset-inline-start dt('toggleswitch.slide.duration'),
            box-shadow dt('toggleswitch.slide.duration');
    }

    .p-toggleswitch.p-toggleswitch-checked .p-toggleswitch-slider {
        background: dt('toggleswitch.checked.background');
        border-color: dt('toggleswitch.checked.border.color');
    }

    .p-toggleswitch.p-toggleswitch-checked .p-toggleswitch-handle {
        background: dt('toggleswitch.handle.checked.background');
        color: dt('toggleswitch.handle.checked.color');
        inset-inline-start: calc(dt('toggleswitch.width') - calc(dt('toggleswitch.handle.size') + dt('toggleswitch.gap')));
    }

    .p-toggleswitch:not(.p-disabled):has(.p-toggleswitch-input:hover) .p-toggleswitch-slider {
        background: dt('toggleswitch.hover.background');
        border-color: dt('toggleswitch.hover.border.color');
    }

    .p-toggleswitch:not(.p-disabled):has(.p-toggleswitch-input:hover) .p-toggleswitch-handle {
        background: dt('toggleswitch.handle.hover.background');
        color: dt('toggleswitch.handle.hover.color');
    }

    .p-toggleswitch:not(.p-disabled):has(.p-toggleswitch-input:hover).p-toggleswitch-checked .p-toggleswitch-slider {
        background: dt('toggleswitch.checked.hover.background');
        border-color: dt('toggleswitch.checked.hover.border.color');
    }

    .p-toggleswitch:not(.p-disabled):has(.p-toggleswitch-input:hover).p-toggleswitch-checked .p-toggleswitch-handle {
        background: dt('toggleswitch.handle.checked.hover.background');
        color: dt('toggleswitch.handle.checked.hover.color');
    }

    .p-toggleswitch:not(.p-disabled):has(.p-toggleswitch-input:focus-visible) .p-toggleswitch-slider {
        box-shadow: dt('toggleswitch.focus.ring.shadow');
        outline: dt('toggleswitch.focus.ring.width') dt('toggleswitch.focus.ring.style') dt('toggleswitch.focus.ring.color');
        outline-offset: dt('toggleswitch.focus.ring.offset');
    }

    .p-toggleswitch.p-invalid > .p-toggleswitch-slider {
        border-color: dt('toggleswitch.invalid.border.color');
    }

    .p-toggleswitch.p-disabled {
        opacity: 1;
    }

    .p-toggleswitch.p-disabled .p-toggleswitch-slider {
        background: dt('toggleswitch.disabled.background');
    }

    .p-toggleswitch.p-disabled .p-toggleswitch-handle {
        background: dt('toggleswitch.handle.disabled.background');
    }
`,ae={root:{position:"relative"}},oe={root:function(t){var l=t.instance,h=t.props;return["p-toggleswitch p-component",{"p-toggleswitch-checked":l.checked,"p-disabled":h.disabled,"p-invalid":l.$invalid}]},input:"p-toggleswitch-input",slider:"p-toggleswitch-slider",handle:"p-toggleswitch-handle"},ie=N.extend({name:"toggleswitch",style:ne,classes:oe,inlineStyles:ae}),se={name:"BaseToggleSwitch",extends:G,props:{trueValue:{type:null,default:!0},falseValue:{type:null,default:!1},readonly:{type:Boolean,default:!1},tabindex:{type:Number,default:null},inputId:{type:String,default:null},inputClass:{type:[String,Object],default:null},inputStyle:{type:Object,default:null},ariaLabelledby:{type:String,default:null},ariaLabel:{type:String,default:null}},style:ie,provide:function(){return{$pcToggleSwitch:this,$parentInstance:this}}},I={name:"ToggleSwitch",extends:se,inheritAttrs:!1,emits:["change","focus","blur"],methods:{getPTOptions:function(t){var l=t==="root"?this.ptmi:this.ptm;return l(t,{context:{checked:this.checked,disabled:this.disabled}})},onChange:function(t){if(!this.disabled&&!this.readonly){var l=this.checked?this.falseValue:this.trueValue;this.writeValue(l,t),this.$emit("change",t)}},onFocus:function(t){this.$emit("focus",t)},onBlur:function(t){var l,h;this.$emit("blur",t),(l=(h=this.formField).onBlur)===null||l===void 0||l.call(h,t)}},computed:{checked:function(){return this.d_value===this.trueValue},dataP:function(){return H({checked:this.checked,disabled:this.disabled,invalid:this.$invalid})}}},le=["data-p-checked","data-p-disabled","data-p"],de=["id","checked","tabindex","disabled","readonly","aria-checked","aria-labelledby","aria-label","aria-invalid"],re=["data-p"],ce=["data-p"];function ge(a,t,l,h,x,i){return v(),V("div",m({class:a.cx("root"),style:a.sx("root")},i.getPTOptions("root"),{"data-p-checked":i.checked,"data-p-disabled":a.disabled,"data-p":i.dataP}),[g("input",m({id:a.inputId,type:"checkbox",role:"switch",class:[a.cx("input"),a.inputClass],style:a.inputStyle,checked:i.checked,tabindex:a.tabindex,disabled:a.disabled,readonly:a.readonly,"aria-checked":i.checked,"aria-labelledby":a.ariaLabelledby,"aria-label":a.ariaLabel,"aria-invalid":a.invalid||void 0,onFocus:t[0]||(t[0]=function(){return i.onFocus&&i.onFocus.apply(i,arguments)}),onBlur:t[1]||(t[1]=function(){return i.onBlur&&i.onBlur.apply(i,arguments)}),onChange:t[2]||(t[2]=function(){return i.onChange&&i.onChange.apply(i,arguments)})},i.getPTOptions("input")),null,16,de),g("div",m({class:a.cx("slider")},i.getPTOptions("slider"),{"data-p":i.dataP}),[g("div",m({class:a.cx("handle")},i.getPTOptions("handle"),{"data-p":i.dataP}),[U(a.$slots,"handle",{checked:i.checked})],16,ce)],16,re)],16,le)}I.render=ge;const he={class:"mb-3 flex items-center justify-between"},ue={class:"text-sm text-dark-muted"},Ie=D({__name:"SalaryRaisePage",setup(a){const t=J(),{data:l,isLoading:h,isError:x,error:i}=X(),F=Y(),L=Z(),S=ee();function b(o){return`${o.round_month==="apr"?"เม.ย.":"ต.ค."} ${o.round_year_be}`}async function O(o,d){try{await F.mutateAsync({roundId:o.id,include:d}),t.add({severity:"success",summary:d?`นับรอบ ${b(o)} ในงบแล้ว`:`ตัดรอบ ${b(o)} ออกจากงบแล้ว`,life:3e3})}catch(e){const p=e instanceof Error?e.message:"เกิดข้อผิดพลาด";t.add({severity:"error",summary:"อัปเดตไม่สำเร็จ",detail:p,life:5e3})}}const y=C(!1),u=C(null),P=T(()=>{var o;return((o=l.value)==null?void 0:o.find(d=>d.id===u.value))??null}),{data:k,isLoading:R}=te(u),E=T(()=>(k.value??[]).filter(o=>o.status==="completed").length);function A(o){u.value=o.id,y.value=!0}async function j(o){if(!u.value)return;const d=o.status==="completed"?"pending":"completed";try{await L.mutateAsync({roundId:u.value,organizationId:o.organization_id,status:d})}catch(e){const p=e instanceof Error?e.message:"เกิดข้อผิดพลาด";t.add({severity:"error",summary:"บันทึกไม่สำเร็จ",detail:p,life:5e3})}}async function M(){if(u.value)try{const o=await S.mutateAsync(u.value);t.add({severity:"success",summary:`สร้างแถวติดตาม ${(o==null?void 0:o.created)??0} หน่วยงาน`,life:3e3})}catch(o){const d=o instanceof Error?o.message:"เกิดข้อผิดพลาด";t.add({severity:"error",summary:"สร้างแถวไม่สำเร็จ",detail:d,life:5e3})}}return(o,d)=>(v(),V("div",null,[d[1]||(d[1]=g("div",{class:"mb-6"},[g("h1",{class:"text-2xl font-bold text-white"},"รอบเลื่อนเงินเดือน"),g("p",{class:"mt-1 text-sm text-dark-muted"},' สวิตช์ "นับในงบ" ตัดสินว่ารอบไหนเข้าคำนวณ · สถานะรายหน่วยตัดสินว่าเงินเดือนหน่วยนั้น "ยืนยัน" หรือ "ประมาณ" ')],-1)),n(x)?(v(),B(Q,{key:0,error:n(i)},null,8,["error"])):(v(),B(n($),{key:1,value:n(l)??[],loading:n(h),"data-key":"id",class:"overflow-hidden rounded-lg border border-dark-border shadow"},{empty:r(()=>[s(z,{message:"ยังไม่มีรอบเลื่อน"})]),default:r(()=>[s(n(c),{header:"รอบ"},{body:r(({data:e})=>[f(w(b(e)),1)]),_:1}),s(n(c),{header:"วันมีผล"},{body:r(({data:e})=>[f(w(n(W)(e.effective_date)),1)]),_:1}),s(n(c),{header:"ปีงบที่กระทบ"},{body:r(({data:e})=>[f(w(e.fiscal_year??"—"),1)]),_:1}),s(n(c),{header:"นับในงบ"},{body:r(({data:e})=>[s(n(I),{"model-value":!!e.include_in_budget,"onUpdate:modelValue":p=>O(e,p)},null,8,["model-value","onUpdate:modelValue"])]),_:1}),s(n(c),{header:"จัดการ",class:"text-right"},{body:r(({data:e})=>[s(n(_),{label:"สถานะรายหน่วย",size:"small",text:"",severity:"info",onClick:p=>A(e)},null,8,["onClick"])]),_:1})]),_:1},8,["value","loading"])),s(n(K),{visible:y.value,"onUpdate:visible":d[0]||(d[0]=e=>y.value=e),header:`สถานะการเลื่อน — รอบ ${P.value?b(P.value):""}`,modal:"",class:"w-full max-w-2xl"},{default:r(()=>[g("div",he,[g("span",ue," เลื่อนเสร็จแล้ว "+w(E.value)+" / "+w((n(k)??[]).length)+" หน่วยงาน ",1),s(n(_),{label:"สร้างแถวทุกหน่วยงาน",size:"small",severity:"secondary",loading:n(S).isPending.value,onClick:M},null,8,["loading"])]),s(n($),{value:n(k)??[],loading:n(R),"data-key":"id",paginator:"",rows:15},{empty:r(()=>[s(z,{message:'ยังไม่มีแถวติดตาม — กด "สร้างแถวทุกหน่วยงาน" เพื่อเริ่ม'})]),default:r(()=>[s(n(c),{field:"organization_name",header:"หน่วยงาน"}),s(n(c),{header:"สถานะ"},{body:r(({data:e})=>[s(n(q),{value:e.status==="completed"?"เลื่อนเสร็จ (ยืนยัน)":"ยังไม่เสร็จ (ประมาณ)",severity:e.status==="completed"?"success":"warn"},null,8,["value","severity"])]),_:1}),s(n(c),{header:"เวลาที่เสร็จ"},{body:r(({data:e})=>[f(w(e.completed_at??"—"),1)]),_:1}),s(n(c),{header:"",class:"text-right"},{body:r(({data:e})=>[s(n(_),{label:e.status==="completed"?"ย้อนเป็นรอ":"ทำเครื่องหมายเสร็จ",size:"small",text:"",severity:e.status==="completed"?"warn":"success",onClick:p=>j(e)},null,8,["label","severity","onClick"])]),_:1})]),_:1},8,["value","loading"])]),_:1},8,["visible","header"])]))}});export{Ie as default};
