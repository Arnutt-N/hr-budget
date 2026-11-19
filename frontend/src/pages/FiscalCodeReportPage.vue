<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Select from 'primevue/select'
import ListEmptyState from '@/components/ListEmptyState.vue'
import PageHeader from '@/components/PageHeader.vue'
import QueryErrorState from '@/components/QueryErrorState.vue'
import { useFiscalCodeReportYears, useFiscalCodeReports } from '@/queries/fiscalCodeReports'

const yearsQuery = useFiscalCodeReportYears()
const selectedYear = ref<number | undefined>(undefined)
watch(
  yearsQuery.data,
  (years) => {
    if (selectedYear.value !== undefined || !years?.length) return
    selectedYear.value = (years.find((year) => year.is_current) ?? years[0]).year
  },
  { immediate: true },
)
const yearSelectOptions = computed(() =>
  (yearsQuery.data.value ?? [])
    .map((fiscalYear) => ({
      label: fiscalYear.is_current ? `ปีงบ ${fiscalYear.year} (ปีปัจจุบัน)` : `ปีงบ ${fiscalYear.year}`,
      value: fiscalYear.year,
    }))
    .sort((a, b) => b.value - a.value),
)

const { sections } = useFiscalCodeReports(selectedYear)

const countFmt = new Intl.NumberFormat('th-TH')
const bahtFmt = new Intl.NumberFormat('th-TH', { style: 'currency', currency: 'THB' })
</script>

<template>
  <div>
    <PageHeader
      title="รหัสงบประมาณ"
      :subtitle="selectedYear ? `จำนวนรหัสงบประมาณและยอดจัดสรร ประจำปีงบ พ.ศ. ${selectedYear}` : 'จำนวนรหัสงบประมาณและยอดจัดสรร ประจำปีปัจจุบัน'"
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

    <QueryErrorState
      v-if="yearsQuery.error.value"
      :error="yearsQuery.error.value"
      :retry="() => yearsQuery.refetch()"
    />

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
            {{ section.supportsYear ? (selectedYear ? `ปีงบ ${selectedYear}` : 'ปีปัจจุบัน') : 'ไม่กรองตามปี' }}
          </span>
        </div>
        <p class="mb-4 text-sm text-dark-muted">{{ section.scope }}</p>

        <QueryErrorState
          v-if="section.error"
          :error="section.error"
          :retry="section.retry"
        />

        <DataTable :value="section.rows" :loading="section.isLoading" data-key="code" class="text-sm">
          <template #empty>
            <ListEmptyState message="ไม่มีข้อมูลสำหรับรายงานนี้" />
          </template>
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
      </section>
    </div>
  </div>
</template>
