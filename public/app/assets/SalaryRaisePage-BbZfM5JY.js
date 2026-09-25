import{B as N,n as v,c as S,b as p,s as m,J as M,d as U,N as D,e as s,g as t,j as J,i as r,r as B,O as C,k as f,t as b}from"./index-DyIBQ9Fj.js";import{s as T,a as c}from"./index-BWt3Y3Po.js";import{s as q}from"./index-ZsJ5N_89.js";import{s as _}from"./index-2OIS55jl.js";import{s as G}from"./index-5bkMGtZb.js";import{a as H}from"./index-CXb64q6l.js";import{f as K}from"./index-DMeWpJEN.js";import{_ as Q}from"./PageHeader.vue_vue_type_script_setup_true_lang-DHYluVRX.js";import{_ as W}from"./QueryErrorState.vue_vue_type_script_setup_true_lang-BU3Jixd6.js";import{_ as X}from"./ListEmptyState.vue_vue_type_script_setup_true_lang-CT5GlTOi.js";import{f as z}from"./date-Bb5GTYnq.js";import{c as Y,d as Z,e as ee,f as te,g as ne}from"./useSalary-tPNnGZKC.js";import"./index-DmF3qbCV.js";import"./index-PhUvF1eA.js";import"./index-FXvGaelt.js";import"./index-CgeWIMKL.js";import"./index-6bnfxrvF.js";import"./index-B8luFFDE.js";import"./useQuery-DGRD5ryj.js";import"./useMutation-QsRt95sG.js";var ae=`
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
`,oe={root:{position:"relative"}},ie={root:function(n){var l=n.instance,g=n.props;return["p-toggleswitch p-component",{"p-toggleswitch-checked":l.checked,"p-disabled":g.disabled,"p-invalid":l.$invalid}]},input:"p-toggleswitch-input",slider:"p-toggleswitch-slider",handle:"p-toggleswitch-handle"},se=N.extend({name:"toggleswitch",style:ae,classes:ie,inlineStyles:oe}),le={name:"BaseToggleSwitch",extends:H,props:{trueValue:{type:null,default:!0},falseValue:{type:null,default:!1},readonly:{type:Boolean,default:!1},tabindex:{type:Number,default:null},inputId:{type:String,default:null},inputClass:{type:[String,Object],default:null},inputStyle:{type:Object,default:null},ariaLabelledby:{type:String,default:null},ariaLabel:{type:String,default:null}},style:se,provide:function(){return{$pcToggleSwitch:this,$parentInstance:this}}},V={name:"ToggleSwitch",extends:le,inheritAttrs:!1,emits:["change","focus","blur"],methods:{getPTOptions:function(n){var l=n==="root"?this.ptmi:this.ptm;return l(n,{context:{checked:this.checked,disabled:this.disabled}})},onChange:function(n){if(!this.disabled&&!this.readonly){var l=this.checked?this.falseValue:this.trueValue;this.writeValue(l,n),this.$emit("change",n)}},onFocus:function(n){this.$emit("focus",n)},onBlur:function(n){var l,g;this.$emit("blur",n),(l=(g=this.formField).onBlur)===null||l===void 0||l.call(g,n)}},computed:{checked:function(){return this.d_value===this.trueValue},dataP:function(){return K({checked:this.checked,disabled:this.disabled,invalid:this.$invalid})}}},de=["data-p-checked","data-p-disabled","data-p"],re=["id","checked","tabindex","disabled","readonly","aria-checked","aria-labelledby","aria-label","aria-invalid"],ce=["data-p"],ge=["data-p"];function he(a,n,l,g,x,i){return v(),S("div",m({class:a.cx("root"),style:a.sx("root")},i.getPTOptions("root"),{"data-p-checked":i.checked,"data-p-disabled":a.disabled,"data-p":i.dataP}),[p("input",m({id:a.inputId,type:"checkbox",role:"switch",class:[a.cx("input"),a.inputClass],style:a.inputStyle,checked:i.checked,tabindex:a.tabindex,disabled:a.disabled,readonly:a.readonly,"aria-checked":i.checked,"aria-labelledby":a.ariaLabelledby,"aria-label":a.ariaLabel,"aria-invalid":a.invalid||void 0,onFocus:n[0]||(n[0]=function(){return i.onFocus&&i.onFocus.apply(i,arguments)}),onBlur:n[1]||(n[1]=function(){return i.onBlur&&i.onBlur.apply(i,arguments)}),onChange:n[2]||(n[2]=function(){return i.onChange&&i.onChange.apply(i,arguments)})},i.getPTOptions("input")),null,16,re),p("div",m({class:a.cx("slider")},i.getPTOptions("slider"),{"data-p":i.dataP}),[p("div",m({class:a.cx("handle")},i.getPTOptions("handle"),{"data-p":i.dataP}),[M(a.$slots,"handle",{checked:i.checked})],16,ge)],16,ce)],16,de)}V.render=he;const ue={key:1,class:"table-scroll"},pe={class:"mb-3 flex items-center justify-between"},be={class:"text-sm text-dark-muted"},we={class:"table-scroll"},je=U({__name:"SalaryRaisePage",setup(a){const n=D(),{data:l,isLoading:g,isError:x,error:i}=Y(),I=Z(),O=ee(),P=te();function w(o){return`${o.round_month==="apr"?"เม.ย.":"ต.ค."} ${o.round_year_be}`}async function F(o,d){try{await I.mutateAsync({roundId:o.id,include:d}),n.add({severity:"success",summary:d?`นับรอบ ${w(o)} ในงบแล้ว`:`ตัดรอบ ${w(o)} ออกจากงบแล้ว`,life:3e3})}catch(e){const u=e instanceof Error?e.message:"เกิดข้อผิดพลาด";n.add({severity:"error",summary:"อัปเดตไม่สำเร็จ",detail:u,life:5e3})}}const y=B(!1),h=B(null),$=C(()=>{var o;return((o=l.value)==null?void 0:o.find(d=>d.id===h.value))??null}),{data:k,isLoading:L}=ne(h),R=C(()=>(k.value??[]).filter(o=>o.status==="completed").length);function j(o){h.value=o.id,y.value=!0}async function A(o){if(!h.value)return;const d=o.status==="completed"?"pending":"completed";try{await O.mutateAsync({roundId:h.value,organizationId:o.organization_id,status:d})}catch(e){const u=e instanceof Error?e.message:"เกิดข้อผิดพลาด";n.add({severity:"error",summary:"บันทึกไม่สำเร็จ",detail:u,life:5e3})}}async function E(){if(h.value)try{const o=await P.mutateAsync(h.value);n.add({severity:"success",summary:`สร้างแถวติดตาม ${(o==null?void 0:o.created)??0} หน่วยงาน`,life:3e3})}catch(o){const d=o instanceof Error?o.message:"เกิดข้อผิดพลาด";n.add({severity:"error",summary:"สร้างแถวไม่สำเร็จ",detail:d,life:5e3})}}return(o,d)=>(v(),S("div",null,[s(Q,{title:"รอบเลื่อนเงินเดือน",subtitle:'สวิตช์ "นับในงบ" ตัดสินว่ารอบไหนเข้าคำนวณ · สถานะรายหน่วยตัดสินว่าเงินเดือนหน่วยนั้น "ยืนยัน" หรือ "ประมาณ"'}),t(x)?(v(),J(W,{key:0,error:t(i)},null,8,["error"])):(v(),S("div",ue,[s(t(T),{value:t(l)??[],loading:t(g),"data-key":"id",class:"overflow-hidden rounded-lg border border-dark-border shadow"},{empty:r(()=>[s(X,{message:"ยังไม่มีรอบเลื่อน"})]),default:r(()=>[s(t(c),{header:"รอบ"},{body:r(({data:e})=>[f(b(w(e)),1)]),_:1}),s(t(c),{header:"วันมีผล"},{body:r(({data:e})=>[f(b(t(z)(e.effective_date)),1)]),_:1}),s(t(c),{header:"ปีงบที่กระทบ"},{body:r(({data:e})=>[f(b(e.fiscal_year??"—"),1)]),_:1}),s(t(c),{header:"นับในงบ"},{body:r(({data:e})=>[s(t(V),{"aria-label":`นับรอบ ${t(z)(e.effective_date)} ในงบ`,"model-value":!!e.include_in_budget,"onUpdate:modelValue":u=>F(e,u)},null,8,["aria-label","model-value","onUpdate:modelValue"])]),_:1}),s(t(c),{header:"จัดการ",class:"text-right"},{body:r(({data:e})=>[s(t(_),{label:"สถานะรายหน่วย",size:"small",text:"",severity:"info",onClick:u=>j(e)},null,8,["onClick"])]),_:1})]),_:1},8,["value","loading"])])),s(t(q),{visible:y.value,"onUpdate:visible":d[0]||(d[0]=e=>y.value=e),header:`สถานะการเลื่อน – รอบ ${$.value?w($.value):""}`,modal:"",class:"w-full max-w-2xl"},{default:r(()=>[p("div",pe,[p("span",be," เลื่อนเสร็จแล้ว "+b(R.value)+" / "+b((t(k)??[]).length)+" หน่วยงาน ",1),s(t(_),{label:"สร้างแถวทุกหน่วยงาน",size:"small",severity:"secondary",loading:t(P).isPending.value,onClick:E},null,8,["loading"])]),p("div",we,[s(t(T),{value:t(k)??[],loading:t(L),"data-key":"id",paginator:"",rows:15},{empty:r(()=>[...d[1]||(d[1]=[p("p",{class:"py-3 text-center text-dark-muted"},' ยังไม่มีแถวติดตาม – กด "สร้างแถวทุกหน่วยงาน" เพื่อเริ่ม ',-1)])]),default:r(()=>[s(t(c),{field:"organization_name",header:"หน่วยงาน"}),s(t(c),{header:"สถานะ"},{body:r(({data:e})=>[s(t(G),{value:e.status==="completed"?"เลื่อนเสร็จ (ยืนยัน)":"ยังไม่เสร็จ (ประมาณ)",severity:e.status==="completed"?"success":"warn"},null,8,["value","severity"])]),_:1}),s(t(c),{header:"เวลาที่เสร็จ"},{body:r(({data:e})=>[f(b(e.completed_at??"—"),1)]),_:1}),s(t(c),{header:"",class:"text-right"},{body:r(({data:e})=>[s(t(_),{label:e.status==="completed"?"ย้อนเป็นรอ":"ทำเครื่องหมายเสร็จ",size:"small",text:"",severity:e.status==="completed"?"warn":"success",onClick:u=>A(e)},null,8,["label","severity","onClick"])]),_:1})]),_:1},8,["value","loading"])])]),_:1},8,["visible","header"])]))}});export{je as default};
