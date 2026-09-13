import{B as N,m as v,c as z,b as w,s as m,I as j,d as U,M as D,e as s,g as n,h as B,w as r,r as C,N as T,p as f,t as p}from"./index-D0UwyIn9.js";import{s as $,a as c}from"./index-Bz7AafUY.js";import{s as q}from"./index-lj3LHGcP.js";import{s as _}from"./index-BoL2qreB.js";import{s as G}from"./index-CSt3zxC8.js";import{a as H}from"./index-BvMx4K31.js";import{f as J}from"./index-bHz43R8j.js";import{_ as K}from"./PageHeader.vue_vue_type_script_setup_true_lang-Ca8etIRm.js";import{_ as Q}from"./QueryErrorState.vue_vue_type_script_setup_true_lang-D1PvYlg0.js";import{_ as W}from"./ListEmptyState.vue_vue_type_script_setup_true_lang-DJAuHffb.js";import{f as X}from"./date-DZRqA0P5.js";import{c as Y,d as Z,e as ee,f as te,g as ne}from"./useSalary-Fo0dQ0i_.js";import"./index-VyAgE7oF.js";import"./index-Cf3GbFP-.js";import"./index-DRWVUBTd.js";import"./index-uA769GZP.js";import"./index-DnBc_x-a.js";import"./index-hUjBsd8e.js";import"./useQuery-BPUCpDXR.js";import"./useMutation-BwKvH5CL.js";var oe=`
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
`,ae={root:{position:"relative"}},ie={root:function(t){var l=t.instance,g=t.props;return["p-toggleswitch p-component",{"p-toggleswitch-checked":l.checked,"p-disabled":g.disabled,"p-invalid":l.$invalid}]},input:"p-toggleswitch-input",slider:"p-toggleswitch-slider",handle:"p-toggleswitch-handle"},se=N.extend({name:"toggleswitch",style:oe,classes:ie,inlineStyles:ae}),le={name:"BaseToggleSwitch",extends:H,props:{trueValue:{type:null,default:!0},falseValue:{type:null,default:!1},readonly:{type:Boolean,default:!1},tabindex:{type:Number,default:null},inputId:{type:String,default:null},inputClass:{type:[String,Object],default:null},inputStyle:{type:Object,default:null},ariaLabelledby:{type:String,default:null},ariaLabel:{type:String,default:null}},style:se,provide:function(){return{$pcToggleSwitch:this,$parentInstance:this}}},I={name:"ToggleSwitch",extends:le,inheritAttrs:!1,emits:["change","focus","blur"],methods:{getPTOptions:function(t){var l=t==="root"?this.ptmi:this.ptm;return l(t,{context:{checked:this.checked,disabled:this.disabled}})},onChange:function(t){if(!this.disabled&&!this.readonly){var l=this.checked?this.falseValue:this.trueValue;this.writeValue(l,t),this.$emit("change",t)}},onFocus:function(t){this.$emit("focus",t)},onBlur:function(t){var l,g;this.$emit("blur",t),(l=(g=this.formField).onBlur)===null||l===void 0||l.call(g,t)}},computed:{checked:function(){return this.d_value===this.trueValue},dataP:function(){return J({checked:this.checked,disabled:this.disabled,invalid:this.$invalid})}}},de=["data-p-checked","data-p-disabled","data-p"],re=["id","checked","tabindex","disabled","readonly","aria-checked","aria-labelledby","aria-label","aria-invalid"],ce=["data-p"],ge=["data-p"];function ue(o,t,l,g,S,i){return v(),z("div",m({class:o.cx("root"),style:o.sx("root")},i.getPTOptions("root"),{"data-p-checked":i.checked,"data-p-disabled":o.disabled,"data-p":i.dataP}),[w("input",m({id:o.inputId,type:"checkbox",role:"switch",class:[o.cx("input"),o.inputClass],style:o.inputStyle,checked:i.checked,tabindex:o.tabindex,disabled:o.disabled,readonly:o.readonly,"aria-checked":i.checked,"aria-labelledby":o.ariaLabelledby,"aria-label":o.ariaLabel,"aria-invalid":o.invalid||void 0,onFocus:t[0]||(t[0]=function(){return i.onFocus&&i.onFocus.apply(i,arguments)}),onBlur:t[1]||(t[1]=function(){return i.onBlur&&i.onBlur.apply(i,arguments)}),onChange:t[2]||(t[2]=function(){return i.onChange&&i.onChange.apply(i,arguments)})},i.getPTOptions("input")),null,16,re),w("div",m({class:o.cx("slider")},i.getPTOptions("slider"),{"data-p":i.dataP}),[w("div",m({class:o.cx("handle")},i.getPTOptions("handle"),{"data-p":i.dataP}),[j(o.$slots,"handle",{checked:i.checked})],16,ge)],16,ce)],16,de)}I.render=ue;const he={class:"mb-3 flex items-center justify-between"},pe={class:"text-sm text-dark-muted"},Oe=U({__name:"SalaryRaisePage",setup(o){const t=D(),{data:l,isLoading:g,isError:S,error:i}=Y(),V=Z(),F=ee(),x=te();function b(a){return`${a.round_month==="apr"?"เม.ย.":"ต.ค."} ${a.round_year_be}`}async function L(a,d){try{await V.mutateAsync({roundId:a.id,include:d}),t.add({severity:"success",summary:d?`นับรอบ ${b(a)} ในงบแล้ว`:`ตัดรอบ ${b(a)} ออกจากงบแล้ว`,life:3e3})}catch(e){const h=e instanceof Error?e.message:"เกิดข้อผิดพลาด";t.add({severity:"error",summary:"อัปเดตไม่สำเร็จ",detail:h,life:5e3})}}const y=C(!1),u=C(null),P=T(()=>{var a;return((a=l.value)==null?void 0:a.find(d=>d.id===u.value))??null}),{data:k,isLoading:O}=ne(u),R=T(()=>(k.value??[]).filter(a=>a.status==="completed").length);function A(a){u.value=a.id,y.value=!0}async function E(a){if(!u.value)return;const d=a.status==="completed"?"pending":"completed";try{await F.mutateAsync({roundId:u.value,organizationId:a.organization_id,status:d})}catch(e){const h=e instanceof Error?e.message:"เกิดข้อผิดพลาด";t.add({severity:"error",summary:"บันทึกไม่สำเร็จ",detail:h,life:5e3})}}async function M(){if(u.value)try{const a=await x.mutateAsync(u.value);t.add({severity:"success",summary:`สร้างแถวติดตาม ${(a==null?void 0:a.created)??0} หน่วยงาน`,life:3e3})}catch(a){const d=a instanceof Error?a.message:"เกิดข้อผิดพลาด";t.add({severity:"error",summary:"สร้างแถวไม่สำเร็จ",detail:d,life:5e3})}}return(a,d)=>(v(),z("div",null,[s(K,{title:"รอบเลื่อนเงินเดือน",subtitle:'สวิตช์ "นับในงบ" ตัดสินว่ารอบไหนเข้าคำนวณ · สถานะรายหน่วยตัดสินว่าเงินเดือนหน่วยนั้น "ยืนยัน" หรือ "ประมาณ"'}),n(S)?(v(),B(Q,{key:0,error:n(i)},null,8,["error"])):(v(),B(n($),{key:1,value:n(l)??[],loading:n(g),"data-key":"id",class:"overflow-hidden rounded-lg border border-dark-border shadow"},{empty:r(()=>[s(W,{message:"ยังไม่มีรอบเลื่อน"})]),default:r(()=>[s(n(c),{header:"รอบ"},{body:r(({data:e})=>[f(p(b(e)),1)]),_:1}),s(n(c),{header:"วันมีผล"},{body:r(({data:e})=>[f(p(n(X)(e.effective_date)),1)]),_:1}),s(n(c),{header:"ปีงบที่กระทบ"},{body:r(({data:e})=>[f(p(e.fiscal_year??"—"),1)]),_:1}),s(n(c),{header:"นับในงบ"},{body:r(({data:e})=>[s(n(I),{"model-value":!!e.include_in_budget,"onUpdate:modelValue":h=>L(e,h)},null,8,["model-value","onUpdate:modelValue"])]),_:1}),s(n(c),{header:"จัดการ",class:"text-right"},{body:r(({data:e})=>[s(n(_),{label:"สถานะรายหน่วย",size:"small",text:"",severity:"info",onClick:h=>A(e)},null,8,["onClick"])]),_:1})]),_:1},8,["value","loading"])),s(n(q),{visible:y.value,"onUpdate:visible":d[0]||(d[0]=e=>y.value=e),header:`สถานะการเลื่อน — รอบ ${P.value?b(P.value):""}`,modal:"",class:"w-full max-w-2xl"},{default:r(()=>[w("div",he,[w("span",pe," เลื่อนเสร็จแล้ว "+p(R.value)+" / "+p((n(k)??[]).length)+" หน่วยงาน ",1),s(n(_),{label:"สร้างแถวทุกหน่วยงาน",size:"small",severity:"secondary",loading:n(x).isPending.value,onClick:M},null,8,["loading"])]),s(n($),{value:n(k)??[],loading:n(O),"data-key":"id",paginator:"",rows:15},{empty:r(()=>[...d[1]||(d[1]=[w("p",{class:"py-3 text-center text-dark-muted"},' ยังไม่มีแถวติดตาม — กด "สร้างแถวทุกหน่วยงาน" เพื่อเริ่ม ',-1)])]),default:r(()=>[s(n(c),{field:"organization_name",header:"หน่วยงาน"}),s(n(c),{header:"สถานะ"},{body:r(({data:e})=>[s(n(G),{value:e.status==="completed"?"เลื่อนเสร็จ (ยืนยัน)":"ยังไม่เสร็จ (ประมาณ)",severity:e.status==="completed"?"success":"warn"},null,8,["value","severity"])]),_:1}),s(n(c),{header:"เวลาที่เสร็จ"},{body:r(({data:e})=>[f(p(e.completed_at??"—"),1)]),_:1}),s(n(c),{header:"",class:"text-right"},{body:r(({data:e})=>[s(n(_),{label:e.status==="completed"?"ย้อนเป็นรอ":"ทำเครื่องหมายเสร็จ",size:"small",text:"",severity:e.status==="completed"?"warn":"success",onClick:h=>E(e)},null,8,["label","severity","onClick"])]),_:1})]),_:1},8,["value","loading"])]),_:1},8,["visible","header"])]))}});export{Oe as default};
