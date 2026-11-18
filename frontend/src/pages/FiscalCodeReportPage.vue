<script setup lang="ts">
import { computed, ref } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Select from 'primevue/select'
import ListEmptyState from '@/components/ListEmptyState.vue'
import PageHeader from '@/components/PageHeader.vue'
import QueryErrorState from '@/components/QueryErrorState.vue'
import { useFiscalYearOptions } from '@/queries/useFiscalYears'
import { useFiscalCodeReports } from '@/queries/fiscalCodeReports'

const currentFiscalYear = (() => {
  const now = new Date()
  const beYear = now.getFullYear() + 543
  return now.getMonth() + 1 >= 10 ? beYear + 1 : beYear
})()

const selectedYear = ref<number>(currentFiscalYear)
const yearOptions = useFiscalYearOptions()
const yearSelectOptions = computed(() => {
  const years = new Set<number>([currentFiscalYear])
  for (const option of yearOptions.value) years.add(option.value)
  return [...years]
    .sort((a, b) => b - a)
    .map((year) => ({ label: `ปีงบ ${year}`, value: year }))
})

const { sections } = useFiscalCodeReports(selectedYear)

const countFmt = new Intl.NumberFormat('th-TH')
const bahtFmt = new Intl.NumberFormat('th-TH', { style: 'currency', currency: 'THB' })
</script>

<template>
  <div>
    <PageHeader
      title="รหัสงบประมาณ"
      :subtitle="`จำนวนรหัสงบประมาณและยอดจัดสรร ประจำปีงบ พ.ศ. ${selectedYear}`"
    >
      <Select
        v-model="selectedYear"
        :options="yearSelectOptions"
        option-label="label"
        option-value="value"
        aria-label="เลือกปีงบประมาณ"
        class="w-40"
      />
    </PageHeader>

    <p class="mb-6 rounded-xl border border-dark-border bg-dark-card px-4 py-3 text-sm text-dark-muted">
      ตารางที่ระบุว่าไม่กรองตามปีจะแสดงรหัสทั้งหมดในระบบ ปีที่เลือกมีผลเฉพาะตารางที่ระบุว่ากรองตามปี
      จำนวนรหัสและยอดจัดสรรเป็นหน่วยต่างกัน จึงแสดงแยกคอลัมน์
    </p>

    <div class="space-y-6">
      <section
        v-for="section in sections"
        :key="section.title"
        class="rounded-xl border border-dark-border bg-dark-card p-5 shadow-sm"
      >
        <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
          <h2 class="text-base font-semibold text-white">{{ section.title }}</h2>
          <span
            class="badge"
            :class="section.supportsYear ? 'badge-blue' : 'badge-gray'"
          >
            {{ section.supportsYear ? `ปีงบ ${selectedYear}` : 'ไม่กรองตามปี' }}
          </span>
        </div>
        <p class="mb-4 text-sm text-dark-muted">{{ section.scope }}</p>

        <QueryErrorState
          v-if="section.error"
          :error="section.error"
          :retry="section.retry"
        />

        <DataTable :value="section.rows" :loading="section.isLoading" data-key="code" class="text-sm">
          <Column field="code" header="รหัส" />
          <Column header="ชื่อ">
            <template #body="{ data }">
              <span :class="data.name_th ? 'text-dark-text' : 'text-dark-muted'">
                {{ data.name_th ?? 'ไม่ระบุชื่อ' }}
              </span>
            </template>
          </Column>
          <Column header="จำนวนรหัส" class="text-right">
            <template #body="{ data }">{{ countFmt.format(data.count) }}</template>
          </Column>
          <Column v-if="section.hasAllocation" header="ยอดจัดสรร (บาท)" class="text-right">
            <template #body="{ data }">{{ bahtFmt.format(data.allocated_pba) }}</template>
          </Column>
        </DataTable>

        <ListEmptyState
          v-if="!section.isLoading && !section.error && section.rows.length === 0"
          message="ไม่มีข้อมูลสำหรับรายงานนี้"
        />
      </section>
    </div>
  </div>
</template>
