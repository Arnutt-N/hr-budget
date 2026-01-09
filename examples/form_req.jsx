import React, { useState, useMemo } from 'react';
import { 
  Users, 
  Banknote, 
  Briefcase, 
  Coins, 
  Star, 
  UserCheck, 
  UserCog, 
  Settings, 
  Wrench, 
  Save, 
  ChevronDown, 
  Activity,
  Zap,
  Shield,
  CreditCard,
  FileText
} from 'lucide-react';

const BudgetForm = () => {
  const [formData, setFormData] = useState({});
  const [errors, setErrors] = useState({});
  const [touched, setTouched] = useState({});
  const [expandedSections, setExpandedSections] = useState({
    salary: true,
    position: false,
    compensation: false,
    special: false,
    permanent: false,
    govEmployee: false,
    operations: false,
    expenses: false
  });

  // Form field configuration
  const formConfig = {
    personnel: {
      title: "งบบุคลากร",
      icon: Users,
      description: "ค่าใช้จ่ายเกี่ยวกับบุคลากร เงินเดือน และค่าจ้าง",
      sections: {
        salary: {
          title: "เงินเดือน",
          icon: Banknote,
          fields: [
            { id: "salary_old_rate", label: "อัตราเดิม", group: "salary_old" },
            { id: "salary_new_rate", label: "อัตราใหม่", group: "salary_new" },
          ]
        },
        position: {
          title: "เงินประจำตำแหน่ง",
          icon: Briefcase,
          fields: [
            { id: "pos_admin", label: "บริหารและอำนวยการ" },
            { id: "pos_academic", label: "วิชาการ" },
            { id: "pos_computer", label: "วิชาชีพเฉพาะ - นักวิชาการคอมพิวเตอร์" },
            { id: "pos_engineer", label: "วิชาชีพเฉพาะ - วิศวกร/สถาปนิก" },
          ]
        },
        compensation: {
          title: "ค่าตอบแทนรายเดือน",
          icon: Coins,
          fields: [
            { id: "comp_admin", label: "บริหารและอำนวยการ" },
            { id: "comp_academic", label: "วิชาการ" },
            { id: "comp_computer", label: "วิชาชีพเฉพาะ - นักวิชาการคอมพิวเตอร์" },
            { id: "comp_engineer", label: "วิชาชีพเฉพาะ - วิศวกร/สถาปนิก" },
            { id: "comp_level8", label: "ข้าราชการระดับ 8 และ 8ว" },
          ]
        },
        special: {
          title: "เงินเพิ่มพิเศษ",
          icon: Star,
          fields: [
            { id: "living_assist", label: "เงินช่วยเหลือการครองชีพข้าราชการระดับต้น" },
            { id: "ptk", label: "พ.ต.ก. (ผู้ปฏิบัติงานด้านนิติกร)" },
            { id: "ppd", label: "พ.พ.ด. (ผู้ปฏิบัติงานด้านพัสดุ)" },
            { id: "psr", label: "พ.ส.ร. (เงินเพิ่มพิเศษสำหรับการสู้รบ)" },
            { id: "spp", label: "สปพ. (สวัสดิการพื้นที่พิเศษ)" },
          ]
        },
        permanent: {
          title: "ค่าจ้างประจำ",
          icon: UserCheck,
          fields: [
            { id: "perm_old", label: "อัตราเดิม" },
            { id: "perm_new", label: "อัตราใหม่" },
            { id: "perm_monthly", label: "ค่าตอบแทนรายเดือนลูกจ้างประจำ" },
            { id: "perm_living", label: "เงินช่วยเหลือค่าครองชีพ" },
            { id: "perm_psr", label: "พ.ส.ร. (เงินเพิ่มพิเศษสำหรับการสู้รบ)" },
          ]
        },
        govEmployee: {
          title: "ค่าตอบแทนพนักงานราชการ",
          icon: UserCog,
          fields: [
            { id: "gov_old", label: "อัตราเดิม" },
            { id: "gov_new", label: "อัตราใหม่" },
            { id: "gov_temp", label: "เงินช่วยเหลือการครองชีพชั่วคราว" },
          ]
        }
      }
    },
    operations: {
      title: "งบดำเนินงาน",
      icon: Activity,
      description: "ค่าตอบแทน ค่าใช้สอย และวัสดุอุปกรณ์",
      sections: {
        operations: {
          title: "ค่าตอบแทน",
          icon: CreditCard,
          fields: [
            { id: "rent", label: "ค่าเช่าบ้าน" },
            { id: "full_salary", label: "ค่าตอบแทนพิเศษเงินเดือนเต็มขั้น" },
            { id: "full_wage", label: "ค่าตอบแทนพิเศษค่าจ้างเต็มขั้น" },
            { id: "south_special", label: "ค่าตอบแทนพิเศษรายเดือน (จังหวัดชายแดนภาคใต้)" },
          ]
        },
        expenses: {
          title: "ค่าใช้สอย",
          icon: Wrench,
          fields: [
            { id: "social_security", label: "เงินสมทบกองทุนประกันสังคม" },
            { id: "compensation_fund", label: "เงินสมทบกองทุนเงินทดแทน" },
          ]
        }
      }
    }
  };

  const handleInputChange = (fieldId, type, value) => {
    const key = `${fieldId}_${type}`;
    const numValue = value === '' ? '' : parseFloat(value);
    
    setFormData(prev => ({
      ...prev,
      [key]: numValue
    }));

    if (errors[key]) {
      setErrors(prev => {
        const newErrors = { ...prev };
        delete newErrors[key];
        return newErrors;
      });
    }
  };

  const handleBlur = (fieldId, type) => {
    const key = `${fieldId}_${type}`;
    setTouched(prev => ({ ...prev, [key]: true }));
    
    const value = formData[key];
    if (value !== '' && value !== undefined && value < 0) {
      setErrors(prev => ({ ...prev, [key]: 'ค่าต้องไม่ติดลบ' }));
    }
  };

  const toggleSection = (sectionId) => {
    setExpandedSections(prev => ({
      ...prev,
      [sectionId]: !prev[sectionId]
    }));
  };

  const calculateTotal = useMemo(() => {
    return Object.entries(formData)
      .filter(([key]) => key.endsWith('_amount'))
      .reduce((sum, [, value]) => sum + (parseFloat(value) || 0), 0);
  }, [formData]);

  const getRowTotal = (fieldId) => {
    const rate = parseFloat(formData[`${fieldId}_rate`]) || 0;
    const price = parseFloat(formData[`${fieldId}_price`]) || 0;
    return rate * price;
  };

  // --- Components ---

  const FormField = ({ field, sectionId }) => {
    const fieldId = field.id;
    const rateKey = `${fieldId}_rate`;
    const priceKey = `${fieldId}_price`;
    const amountKey = `${fieldId}_amount`;
    
    const calculatedAmount = getRowTotal(fieldId);
    const hasError = errors[rateKey] || errors[priceKey] || errors[amountKey];

    return (
      <div className={`group rounded-xl transition-all duration-300 border ${hasError ? 'bg-red-500/5 border-red-500/30' : 'bg-[#1e293b]/30 border-slate-700/50 hover:border-indigo-500/30 hover:bg-[#1e293b]/50'}`}>
        <div className="p-4">
          <div className="flex items-center gap-2 mb-3">
            <div className={`w-1 h-4 rounded-full ${hasError ? 'bg-red-500' : 'bg-indigo-500'}`}></div>
            <label className="text-sm font-medium text-slate-200">
              {field.label}
              {field.required && <span className="text-red-400 ml-1">*</span>}
            </label>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-12 gap-3">
            {/* Rate Input */}
            <div className="md:col-span-3 space-y-1.5">
              <label className="text-[10px] uppercase tracking-wider text-slate-500 font-semibold">อัตรา (คน)</label>
              <input
                type="number"
                min="0"
                placeholder="0"
                value={formData[rateKey] ?? ''}
                onChange={(e) => handleInputChange(fieldId, 'rate', e.target.value)}
                onBlur={() => handleBlur(fieldId, 'rate')}
                className={`w-full bg-[#0f172a] border rounded-lg px-3 py-2 text-sm text-white placeholder-slate-600 focus:outline-none focus:ring-1 transition-all text-right font-mono
                  ${errors[rateKey] ? 'border-red-500/50 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-700 focus:border-cyan-400 focus:ring-cyan-400/20'}`}
              />
            </div>

            {/* Price Input */}
            <div className="md:col-span-4 space-y-1.5">
              <label className="text-[10px] uppercase tracking-wider text-slate-500 font-semibold">ราคา/หน่วย (บาท)</label>
              <input
                type="number"
                min="0"
                step="0.01"
                placeholder="0.00"
                value={formData[priceKey] ?? ''}
                onChange={(e) => handleInputChange(fieldId, 'price', e.target.value)}
                onBlur={() => handleBlur(fieldId, 'price')}
                className={`w-full bg-[#0f172a] border rounded-lg px-3 py-2 text-sm text-white placeholder-slate-600 focus:outline-none focus:ring-1 transition-all text-right font-mono
                  ${errors[priceKey] ? 'border-red-500/50 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-700 focus:border-emerald-400 focus:ring-emerald-400/20'}`}
              />
            </div>

            {/* Amount Input */}
            <div className="md:col-span-5 space-y-1.5">
              <label className="text-[10px] uppercase tracking-wider text-slate-500 font-semibold">วงเงินรวม (บาท)</label>
              <div className="relative">
                <input
                  type="number"
                  min="0"
                  step="0.01"
                  placeholder={calculatedAmount > 0 ? calculatedAmount.toLocaleString('th-TH') : '0.00'}
                  value={formData[amountKey] ?? ''}
                  onChange={(e) => handleInputChange(fieldId, 'amount', e.target.value)}
                  onBlur={() => handleBlur(fieldId, 'amount')}
                  className={`w-full bg-[#0f172a] border rounded-lg px-3 py-2 text-sm font-bold text-right font-mono transition-all
                    ${errors[amountKey] 
                      ? 'border-red-500/50 text-red-400 focus:border-red-500 focus:ring-red-500/20' 
                      : 'border-slate-700 text-indigo-300 focus:border-indigo-400 focus:ring-indigo-400/20 placeholder-slate-700'}`}
                />
                {calculatedAmount > 0 && !formData[amountKey] && (
                  <button
                    type="button"
                    onClick={() => handleInputChange(fieldId, 'amount', calculatedAmount.toString())}
                    className="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-400 hover:bg-indigo-500/20 border border-indigo-500/20 transition-all opacity-0 group-hover:opacity-100"
                  >
                    Use Auto
                  </button>
                )}
              </div>
              <div className="flex justify-end h-3">
                 {calculatedAmount > 0 && <span className="text-[10px] text-slate-500">Auto: {calculatedAmount.toLocaleString('th-TH')}</span>}
              </div>
            </div>
          </div>
          
          {(errors[rateKey] || errors[priceKey] || errors[amountKey]) && (
             <div className="mt-2 text-xs text-red-400 flex items-center gap-1">
                <Shield size={12} />
                <span>กรุณาตรวจสอบข้อมูลที่กรอก</span>
             </div>
          )}
        </div>
      </div>
    );
  };

  const Section = ({ sectionId, section }) => {
    const isExpanded = expandedSections[sectionId];
    const sectionTotal = section.fields.reduce((sum, field) => {
      const amount = parseFloat(formData[`${field.id}_amount`]) || getRowTotal(field.id);
      return sum + amount;
    }, 0);
    
    const Icon = section.icon;

    return (
      <div className={`border rounded-2xl overflow-hidden transition-all duration-300 ${isExpanded ? 'border-indigo-500/30 bg-[#0f172a]/40 shadow-lg shadow-indigo-900/10' : 'border-slate-800 bg-[#0f172a]/20 hover:border-slate-700'}`}>
        <button
          type="button"
          onClick={() => toggleSection(sectionId)}
          className={`w-full flex items-center justify-between p-4 transition-colors ${isExpanded ? 'bg-indigo-500/5' : 'hover:bg-slate-800/50'}`}
        >
          <div className="flex items-center gap-4">
            <div className={`p-2 rounded-lg transition-colors ${isExpanded ? 'bg-indigo-500/20 text-indigo-300' : 'bg-slate-800 text-slate-400'}`}>
               <Icon size={20} />
            </div>
            <div className="text-left">
              <h3 className={`font-semibold ${isExpanded ? 'text-indigo-200' : 'text-slate-300'}`}>{section.title}</h3>
              <p className="text-xs text-slate-500">{section.fields.length} รายการ</p>
            </div>
          </div>
          
          <div className="flex items-center gap-4">
            {sectionTotal > 0 && (
              <div className="hidden sm:block text-right">
                <span className="block text-[10px] text-slate-500 uppercase">Subtotal</span>
                <span className="text-sm font-mono font-medium text-emerald-400">
                  {sectionTotal.toLocaleString('th-TH')}
                </span>
              </div>
            )}
            <ChevronDown size={18} className={`text-slate-500 transition-transform duration-300 ${isExpanded ? 'rotate-180' : ''}`} />
          </div>
        </button>

        <div className={`transition-all duration-300 ease-in-out overflow-hidden ${isExpanded ? 'max-h-[2000px] opacity-100' : 'max-h-0 opacity-0'}`}>
          <div className="p-4 space-y-3 border-t border-slate-800/50">
            {section.fields.map((field) => (
              <FormField key={field.id} field={field} sectionId={sectionId} />
            ))}
          </div>
        </div>
      </div>
    );
  };

  const Category = ({ categoryId, category }) => {
    const Icon = category.icon;
    return (
      <div className="space-y-4 mb-10">
        <div className="flex items-start gap-4 mb-6 px-2">
          <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-700 flex items-center justify-center shadow-lg shadow-indigo-500/20 ring-1 ring-white/10 shrink-0">
            <Icon size={24} className="text-white" />
          </div>
          <div>
            <h2 className="text-xl font-bold text-white tracking-tight">{category.title}</h2>
            <p className="text-sm text-slate-400 mt-1">{category.description}</p>
          </div>
        </div>
        
        <div className="grid gap-4">
          {Object.entries(category.sections).map(([sectionId, section]) => (
            <Section key={sectionId} sectionId={sectionId} section={section} />
          ))}
        </div>
      </div>
    );
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    console.log('Form submitted:', formData);
  };

  return (
    <div className="min-h-screen bg-[#020617] text-slate-300 font-sans selection:bg-indigo-500/30">
      
      {/* Global Style for Font & Number Inputs */}
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700&display=swap');
        
        :root {
          font-family: 'Noto Sans Thai', sans-serif;
        }

        /* Hide Number Input Spinners */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
          -webkit-appearance: none;
          margin: 0;
        }
        input[type="number"] {
          -moz-appearance: textfield;
        }
      `}</style>

      {/* Ambient Background */}
      <div className="fixed inset-0 pointer-events-none overflow-hidden">
        <div className="absolute top-0 left-1/4 w-[600px] h-[600px] bg-indigo-900/10 rounded-full blur-[120px]"></div>
        <div className="absolute bottom-0 right-1/4 w-[500px] h-[500px] bg-cyan-900/10 rounded-full blur-[100px]"></div>
        <div className="absolute top-0 left-0 w-full h-full bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 brightness-100 contrast-150"></div>
      </div>

      <div className="relative z-10 max-w-5xl mx-auto px-4 py-8 pb-32">
        {/* Header */}
        <header className="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12 border-b border-slate-800/60 pb-8">
          <div>
             <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/50 border border-slate-700/50 mb-4 backdrop-blur-sm">
                <span className="relative flex h-2 w-2">
                  <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span className="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span className="text-xs font-medium text-slate-300 tracking-wide uppercase">Fiscal Year 2025</span>
             </div>
             <h1 className="text-4xl font-bold text-white tracking-tight mb-2">Budget Allocation</h1>
             <p className="text-slate-400 max-w-lg">ระบบจัดทำคำขอตั้งงบประมาณประจำปี (Strategic Budgeting System)</p>
          </div>
          
          <div className="flex flex-col items-end gap-2">
             <div className="text-right">
                <div className="text-xs text-slate-500 uppercase tracking-widest">Progress</div>
                <div className="text-2xl font-mono font-bold text-white">
                  {Object.keys(formData).filter(k => k.endsWith('_amount') && formData[k] > 0).length} 
                  <span className="text-base text-slate-500 font-sans ml-1 font-normal">items filled</span>
                </div>
             </div>
          </div>
        </header>

        {/* Form Content */}
        <form onSubmit={handleSubmit} className="space-y-8">
          {Object.entries(formConfig).map(([categoryId, category]) => (
            <Category key={categoryId} categoryId={categoryId} category={category} />
          ))}

          {/* Sticky Summary Footer */}
          <div className="fixed bottom-0 left-0 right-0 p-4 md:p-6 bg-[#020617]/80 backdrop-blur-xl border-t border-slate-800 z-50">
            <div className="max-w-5xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
              <div className="flex items-center gap-4 w-full md:w-auto">
                 <div className="p-3 rounded-xl bg-indigo-500/10 text-indigo-400 hidden sm:block">
                    <Banknote size={24} />
                 </div>
                 <div>
                    <p className="text-xs text-slate-500 uppercase tracking-wider mb-0.5">Total Estimated Budget</p>
                    <p className="text-2xl md:text-3xl font-bold text-white font-mono tracking-tight">
                       {calculateTotal.toLocaleString('th-TH', { minimumFractionDigits: 2 })}
                       <span className="text-sm text-slate-500 font-sans ml-2 font-normal">THB</span>
                    </p>
                 </div>
              </div>
              
              <div className="flex gap-3 w-full md:w-auto">
                <button
                  type="button"
                  className="flex-1 md:flex-none flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 font-medium hover:bg-slate-700 hover:text-white transition-all active:scale-95"
                >
                  <FileText size={18} />
                  <span>Save Draft</span>
                </button>
                <button
                  type="submit"
                  className="flex-1 md:flex-none flex items-center justify-center gap-2 px-8 py-3 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-500 shadow-lg shadow-indigo-600/25 transition-all active:scale-95"
                >
                  <Save size={18} />
                  <span>Submit Request</span>
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  );
};

export default BudgetForm;