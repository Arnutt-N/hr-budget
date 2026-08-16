import{B as M,l as v,c as z,a as c,n as f,E as N,d as U,I as D,e as t,g as B,w as r,b as l,r as C,H as T,m as w,t as b}from"./index-5tNKhMzB.js";import{s as $,a as g}from"./index-CdeW3nOC.js";import{s as H}from"./index-Cj7wqJjE.js";import{s as x}from"./index-BT9nWnWx.js";import{s as q}from"./index-DestKcOq.js";import{a as G}from"./index-DwBr5uML.js";import{f as J}from"./index-C5K37nv0.js";import{s as K}from"./index-DtYamkF5.js";import{f as Q}from"./date-DZRqA0P5.js";import{c as W,d as X,e as Y,f as Z,g as ee}from"./useSalary-SDadMPEB.js";import"./index-Dmf9LRlF.js";import"./index-HaPAXpmP.js";import"./index-A85q8mMN.js";import"./index-DtuTir_m.js";import"./useQuery-DGbn9GTk.js";import"./useMutation-C_kgS13s.js";import"./useApi-6qa_BdnJ.js";var te=`
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
`,ne={root:{position:"relative"}},ae={root:function(n){var d=n.instance,u=n.props;return["p-toggleswitch p-component",{"p-toggleswitch-checked":d.checked,"p-disabled":u.disabled,"p-invalid":d.$invalid}]},input:"p-toggleswitch-input",slider:"p-toggleswitch-slider",handle:"p-toggleswitch-handle"},oe=M.extend({name:"toggleswitch",style:te,classes:ae,inlineStyles:ne}),se={name:"BaseToggleSwitch",extends:G,props:{trueValue:{type:null,default:!0},falseValue:{type:null,default:!1},readonly:{type:Boolean,default:!1},tabindex:{type:Number,default:null},inputId:{type:String,default:null},inputClass:{type:[String,Object],default:null},inputStyle:{type:Object,default:null},ariaLabelledby:{type:String,default:null},ariaLabel:{type:String,default:null}},style:oe,provide:function(){return{$pcToggleSwitch:this,$parentInstance:this}}},I={name:"ToggleSwitch",extends:se,inheritAttrs:!1,emits:["change","focus","blur"],methods:{getPTOptions:function(n){var d=n==="root"?this.ptmi:this.ptm;return d(n,{context:{checked:this.checked,disabled:this.disabled}})},onChange:function(n){if(!this.disabled&&!this.readonly){var d=this.checked?this.falseValue:this.trueValue;this.writeValue(d,n),this.$emit("change",n)}},onFocus:function(n){this.$emit("focus",n)},onBlur:function(n){var d,u;this.$emit("blur",n),(d=(u=this.formField).onBlur)===null||d===void 0||d.call(u,n)}},computed:{checked:function(){return this.d_value===this.trueValue},dataP:function(){return J({checked:this.checked,disabled:this.disabled,invalid:this.$invalid})}}},ie=["data-p-checked","data-p-disabled","data-p"],le=["id","checked","tabindex","disabled","readonly","aria-checked","aria-labelledby","aria-label","aria-invalid"],de=["data-p"],re=["data-p"];function ce(a,n,d,u,S,s){return v(),z("div",f({class:a.cx("root"),style:a.sx("root")},s.getPTOptions("root"),{"data-p-checked":s.checked,"data-p-disabled":a.disabled,"data-p":s.dataP}),[c("input",f({id:a.inputId,type:"checkbox",role:"switch",class:[a.cx("input"),a.inputClass],style:a.inputStyle,checked:s.checked,tabindex:a.tabindex,disabled:a.disabled,readonly:a.readonly,"aria-checked":s.checked,"aria-labelledby":a.ariaLabelledby,"aria-label":a.ariaLabel,"aria-invalid":a.invalid||void 0,onFocus:n[0]||(n[0]=function(){return s.onFocus&&s.onFocus.apply(s,arguments)}),onBlur:n[1]||(n[1]=function(){return s.onBlur&&s.onBlur.apply(s,arguments)}),onChange:n[2]||(n[2]=function(){return s.onChange&&s.onChange.apply(s,arguments)})},s.getPTOptions("input")),null,16,le),c("div",f({class:a.cx("slider")},s.getPTOptions("slider"),{"data-p":s.dataP}),[c("div",f({class:a.cx("handle")},s.getPTOptions("handle"),{"data-p":s.dataP}),[N(a.$slots,"handle",{checked:s.checked})],16,re)],16,de)],16,ie)}I.render=ce;const ge={class:"mb-3 flex items-center justify-between"},ue={class:"text-sm text-dark-muted"},ze=U({__name:"SalaryRaisePage",setup(a){const n=D(),{data:d,isLoading:u,isError:S,error:s}=W(),V=X(),F=Y(),P=Z();function m(o){return`${o.round_month==="apr"?"เม.ย.":"ต.ค."} ${o.round_year_be}`}async function L(o,i){try{await V.mutateAsync({roundId:o.id,include:i}),n.add({severity:"success",summary:i?`นับรอบ ${m(o)} ในงบแล้ว`:`ตัดรอบ ${m(o)} ออกจากงบแล้ว`,life:3e3})}catch(e){const p=e instanceof Error?e.message:"เกิดข้อผิดพลาด";n.add({severity:"error",summary:"อัปเดตไม่สำเร็จ",detail:p,life:5e3})}}const y=C(!1),h=C(null),_=T(()=>{var o;return((o=d.value)==null?void 0:o.find(i=>i.id===h.value))??null}),{data:k,isLoading:O}=ee(h),R=T(()=>(k.value??[]).filter(o=>o.status==="completed").length);function E(o){h.value=o.id,y.value=!0}async function A(o){if(!h.value)return;const i=o.status==="completed"?"pending":"completed";try{await F.mutateAsync({roundId:h.value,organizationId:o.organization_id,status:i})}catch(e){const p=e instanceof Error?e.message:"เกิดข้อผิดพลาด";n.add({severity:"error",summary:"บันทึกไม่สำเร็จ",detail:p,life:5e3})}}async function j(){if(h.value)try{const o=await P.mutateAsync(h.value);n.add({severity:"success",summary:`สร้างแถวติดตาม ${(o==null?void 0:o.created)??0} หน่วยงาน`,life:3e3})}catch(o){const i=o instanceof Error?o.message:"เกิดข้อผิดพลาด";n.add({severity:"error",summary:"สร้างแถวไม่สำเร็จ",detail:i,life:5e3})}}return(o,i)=>(v(),z("div",null,[i[3]||(i[3]=c("div",{class:"mb-6"},[c("h1",{class:"text-2xl font-bold text-white"},"รอบเลื่อนเงินเดือน"),c("p",{class:"mt-1 text-sm text-dark-muted"},' สวิตช์ "นับในงบ" ตัดสินว่ารอบไหนเข้าคำนวณ · สถานะรายหน่วยตัดสินว่าเงินเดือนหน่วยนั้น "ยืนยัน" หรือ "ประมาณ" ')],-1)),t(S)?(v(),B(t(K),{key:0,severity:"error",closable:!1},{default:r(()=>{var e;return[w(b(((e=t(s))==null?void 0:e.message)??"ไม่สามารถโหลดข้อมูลได้"),1)]}),_:1})):(v(),B(t($),{key:1,value:t(d)??[],loading:t(u),"data-key":"id",class:"overflow-hidden rounded-lg border border-dark-border shadow"},{empty:r(()=>[...i[1]||(i[1]=[c("p",{class:"py-4 text-center text-dark-muted"},"ยังไม่มีรอบเลื่อน",-1)])]),default:r(()=>[l(t(g),{header:"รอบ"},{body:r(({data:e})=>[w(b(m(e)),1)]),_:1}),l(t(g),{header:"วันมีผล"},{body:r(({data:e})=>[w(b(t(Q)(e.effective_date)),1)]),_:1}),l(t(g),{header:"ปีงบที่กระทบ"},{body:r(({data:e})=>[w(b(e.fiscal_year??"—"),1)]),_:1}),l(t(g),{header:"นับในงบ"},{body:r(({data:e})=>[l(t(I),{"model-value":!!e.include_in_budget,"onUpdate:modelValue":p=>L(e,p)},null,8,["model-value","onUpdate:modelValue"])]),_:1}),l(t(g),{header:"จัดการ",class:"text-right"},{body:r(({data:e})=>[l(t(x),{label:"สถานะรายหน่วย",size:"small",text:"",severity:"info",onClick:p=>E(e)},null,8,["onClick"])]),_:1})]),_:1},8,["value","loading"])),l(t(H),{visible:y.value,"onUpdate:visible":i[0]||(i[0]=e=>y.value=e),header:`สถานะการเลื่อน — รอบ ${_.value?m(_.value):""}`,modal:"",class:"w-full max-w-2xl"},{default:r(()=>[c("div",ge,[c("span",ue," เลื่อนเสร็จแล้ว "+b(R.value)+" / "+b((t(k)??[]).length)+" หน่วยงาน ",1),l(t(x),{label:"สร้างแถวทุกหน่วยงาน",size:"small",severity:"secondary",loading:t(P).isPending.value,onClick:j},null,8,["loading"])]),l(t($),{value:t(k)??[],loading:t(O),"data-key":"id",paginator:"",rows:15},{empty:r(()=>[...i[2]||(i[2]=[c("p",{class:"py-3 text-center text-dark-muted"},' ยังไม่มีแถวติดตาม — กด "สร้างแถวทุกหน่วยงาน" เพื่อเริ่ม ',-1)])]),default:r(()=>[l(t(g),{field:"organization_name",header:"หน่วยงาน"}),l(t(g),{header:"สถานะ"},{body:r(({data:e})=>[l(t(q),{value:e.status==="completed"?"เลื่อนเสร็จ (ยืนยัน)":"ยังไม่เสร็จ (ประมาณ)",severity:e.status==="completed"?"success":"warn"},null,8,["value","severity"])]),_:1}),l(t(g),{header:"เวลาที่เสร็จ"},{body:r(({data:e})=>[w(b(e.completed_at??"—"),1)]),_:1}),l(t(g),{header:"",class:"text-right"},{body:r(({data:e})=>[l(t(x),{label:e.status==="completed"?"ย้อนเป็นรอ":"ทำเครื่องหมายเสร็จ",size:"small",text:"",severity:e.status==="completed"?"warn":"success",onClick:p=>A(e)},null,8,["label","severity","onClick"])]),_:1})]),_:1},8,["value","loading"])]),_:1},8,["visible","header"])]))}});export{ze as default};
