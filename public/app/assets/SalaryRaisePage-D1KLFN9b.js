import{B as M,l as v,c as z,a as g,n as m,E as N,d as U,K as D,e as n,g as B,w as r,b as s,r as C,J as T,m as f,t as w}from"./index-DNFVT4Q-.js";import{s as $,a as c}from"./index-BeUGIRy6.js";import{s as J}from"./index-BG8vQuQK.js";import{s as x}from"./index-BYxNJ8DN.js";import{s as K}from"./index-Isw16dby.js";import{a as q}from"./index-T-ckYvU_.js";import{f as G}from"./index-pfXIoZRk.js";import{_ as H,a as Q}from"./ListEmptyState.vue_vue_type_script_setup_true_lang-BRNOY1tA.js";import{f as W}from"./date-DZRqA0P5.js";import{c as X,d as Y,e as Z,f as ee,g as te}from"./useSalary-D6kDkmC3.js";import"./index-DZZ7cXtL.js";import"./index-BMHQ66s7.js";import"./index-kKQm_Sdm.js";import"./index-CrugWtxy.js";import"./index-DWV1_c4M.js";import"./useQuery-DOz-0Xuj.js";import"./useMutation-Z2pN5Dio.js";import"./useApi-BI00xo4D.js";var ne=`
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
`,ae={root:{position:"relative"}},oe={root:function(t){var d=t.instance,u=t.props;return["p-toggleswitch p-component",{"p-toggleswitch-checked":d.checked,"p-disabled":u.disabled,"p-invalid":d.$invalid}]},input:"p-toggleswitch-input",slider:"p-toggleswitch-slider",handle:"p-toggleswitch-handle"},ie=M.extend({name:"toggleswitch",style:ne,classes:oe,inlineStyles:ae}),se={name:"BaseToggleSwitch",extends:q,props:{trueValue:{type:null,default:!0},falseValue:{type:null,default:!1},readonly:{type:Boolean,default:!1},tabindex:{type:Number,default:null},inputId:{type:String,default:null},inputClass:{type:[String,Object],default:null},inputStyle:{type:Object,default:null},ariaLabelledby:{type:String,default:null},ariaLabel:{type:String,default:null}},style:ie,provide:function(){return{$pcToggleSwitch:this,$parentInstance:this}}},V={name:"ToggleSwitch",extends:se,inheritAttrs:!1,emits:["change","focus","blur"],methods:{getPTOptions:function(t){var d=t==="root"?this.ptmi:this.ptm;return d(t,{context:{checked:this.checked,disabled:this.disabled}})},onChange:function(t){if(!this.disabled&&!this.readonly){var d=this.checked?this.falseValue:this.trueValue;this.writeValue(d,t),this.$emit("change",t)}},onFocus:function(t){this.$emit("focus",t)},onBlur:function(t){var d,u;this.$emit("blur",t),(d=(u=this.formField).onBlur)===null||d===void 0||d.call(u,t)}},computed:{checked:function(){return this.d_value===this.trueValue},dataP:function(){return G({checked:this.checked,disabled:this.disabled,invalid:this.$invalid})}}},le=["data-p-checked","data-p-disabled","data-p"],de=["id","checked","tabindex","disabled","readonly","aria-checked","aria-labelledby","aria-label","aria-invalid"],re=["data-p"],ce=["data-p"];function ge(a,t,d,u,_,i){return v(),z("div",m({class:a.cx("root"),style:a.sx("root")},i.getPTOptions("root"),{"data-p-checked":i.checked,"data-p-disabled":a.disabled,"data-p":i.dataP}),[g("input",m({id:a.inputId,type:"checkbox",role:"switch",class:[a.cx("input"),a.inputClass],style:a.inputStyle,checked:i.checked,tabindex:a.tabindex,disabled:a.disabled,readonly:a.readonly,"aria-checked":i.checked,"aria-labelledby":a.ariaLabelledby,"aria-label":a.ariaLabel,"aria-invalid":a.invalid||void 0,onFocus:t[0]||(t[0]=function(){return i.onFocus&&i.onFocus.apply(i,arguments)}),onBlur:t[1]||(t[1]=function(){return i.onBlur&&i.onBlur.apply(i,arguments)}),onChange:t[2]||(t[2]=function(){return i.onChange&&i.onChange.apply(i,arguments)})},i.getPTOptions("input")),null,16,de),g("div",m({class:a.cx("slider")},i.getPTOptions("slider"),{"data-p":i.dataP}),[g("div",m({class:a.cx("handle")},i.getPTOptions("handle"),{"data-p":i.dataP}),[N(a.$slots,"handle",{checked:i.checked})],16,ce)],16,re)],16,le)}V.render=ge;const ue={class:"mb-3 flex items-center justify-between"},he={class:"text-sm text-dark-muted"},Ie=U({__name:"SalaryRaisePage",setup(a){const t=D(),{data:d,isLoading:u,isError:_,error:i}=X(),I=Y(),F=Z(),S=ee();function b(o){return`${o.round_month==="apr"?"เม.ย.":"ต.ค."} ${o.round_year_be}`}async function L(o,l){try{await I.mutateAsync({roundId:o.id,include:l}),t.add({severity:"success",summary:l?`นับรอบ ${b(o)} ในงบแล้ว`:`ตัดรอบ ${b(o)} ออกจากงบแล้ว`,life:3e3})}catch(e){const p=e instanceof Error?e.message:"เกิดข้อผิดพลาด";t.add({severity:"error",summary:"อัปเดตไม่สำเร็จ",detail:p,life:5e3})}}const y=C(!1),h=C(null),P=T(()=>{var o;return((o=d.value)==null?void 0:o.find(l=>l.id===h.value))??null}),{data:k,isLoading:O}=te(h),R=T(()=>(k.value??[]).filter(o=>o.status==="completed").length);function E(o){h.value=o.id,y.value=!0}async function A(o){if(!h.value)return;const l=o.status==="completed"?"pending":"completed";try{await F.mutateAsync({roundId:h.value,organizationId:o.organization_id,status:l})}catch(e){const p=e instanceof Error?e.message:"เกิดข้อผิดพลาด";t.add({severity:"error",summary:"บันทึกไม่สำเร็จ",detail:p,life:5e3})}}async function j(){if(h.value)try{const o=await S.mutateAsync(h.value);t.add({severity:"success",summary:`สร้างแถวติดตาม ${(o==null?void 0:o.created)??0} หน่วยงาน`,life:3e3})}catch(o){const l=o instanceof Error?o.message:"เกิดข้อผิดพลาด";t.add({severity:"error",summary:"สร้างแถวไม่สำเร็จ",detail:l,life:5e3})}}return(o,l)=>(v(),z("div",null,[l[2]||(l[2]=g("div",{class:"mb-6"},[g("h1",{class:"text-2xl font-bold text-white"},"รอบเลื่อนเงินเดือน"),g("p",{class:"mt-1 text-sm text-dark-muted"},' สวิตช์ "นับในงบ" ตัดสินว่ารอบไหนเข้าคำนวณ · สถานะรายหน่วยตัดสินว่าเงินเดือนหน่วยนั้น "ยืนยัน" หรือ "ประมาณ" ')],-1)),n(_)?(v(),B(H,{key:0,error:n(i)},null,8,["error"])):(v(),B(n($),{key:1,value:n(d)??[],loading:n(u),"data-key":"id",class:"overflow-hidden rounded-lg border border-dark-border shadow"},{empty:r(()=>[s(Q,{message:"ยังไม่มีรอบเลื่อน"})]),default:r(()=>[s(n(c),{header:"รอบ"},{body:r(({data:e})=>[f(w(b(e)),1)]),_:1}),s(n(c),{header:"วันมีผล"},{body:r(({data:e})=>[f(w(n(W)(e.effective_date)),1)]),_:1}),s(n(c),{header:"ปีงบที่กระทบ"},{body:r(({data:e})=>[f(w(e.fiscal_year??"—"),1)]),_:1}),s(n(c),{header:"นับในงบ"},{body:r(({data:e})=>[s(n(V),{"model-value":!!e.include_in_budget,"onUpdate:modelValue":p=>L(e,p)},null,8,["model-value","onUpdate:modelValue"])]),_:1}),s(n(c),{header:"จัดการ",class:"text-right"},{body:r(({data:e})=>[s(n(x),{label:"สถานะรายหน่วย",size:"small",text:"",severity:"info",onClick:p=>E(e)},null,8,["onClick"])]),_:1})]),_:1},8,["value","loading"])),s(n(J),{visible:y.value,"onUpdate:visible":l[0]||(l[0]=e=>y.value=e),header:`สถานะการเลื่อน — รอบ ${P.value?b(P.value):""}`,modal:"",class:"w-full max-w-2xl"},{default:r(()=>[g("div",ue,[g("span",he," เลื่อนเสร็จแล้ว "+w(R.value)+" / "+w((n(k)??[]).length)+" หน่วยงาน ",1),s(n(x),{label:"สร้างแถวทุกหน่วยงาน",size:"small",severity:"secondary",loading:n(S).isPending.value,onClick:j},null,8,["loading"])]),s(n($),{value:n(k)??[],loading:n(O),"data-key":"id",paginator:"",rows:15},{empty:r(()=>[...l[1]||(l[1]=[g("p",{class:"py-3 text-center text-dark-muted"},' ยังไม่มีแถวติดตาม — กด "สร้างแถวทุกหน่วยงาน" เพื่อเริ่ม ',-1)])]),default:r(()=>[s(n(c),{field:"organization_name",header:"หน่วยงาน"}),s(n(c),{header:"สถานะ"},{body:r(({data:e})=>[s(n(K),{value:e.status==="completed"?"เลื่อนเสร็จ (ยืนยัน)":"ยังไม่เสร็จ (ประมาณ)",severity:e.status==="completed"?"success":"warn"},null,8,["value","severity"])]),_:1}),s(n(c),{header:"เวลาที่เสร็จ"},{body:r(({data:e})=>[f(w(e.completed_at??"—"),1)]),_:1}),s(n(c),{header:"",class:"text-right"},{body:r(({data:e})=>[s(n(x),{label:e.status==="completed"?"ย้อนเป็นรอ":"ทำเครื่องหมายเสร็จ",size:"small",text:"",severity:e.status==="completed"?"warn":"success",onClick:p=>A(e)},null,8,["label","severity","onClick"])]),_:1})]),_:1},8,["value","loading"])]),_:1},8,["visible","header"])]))}});export{Ie as default};
