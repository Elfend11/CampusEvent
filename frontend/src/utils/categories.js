export const eventCategories = [
  {
    value: 'Seminar',
    label: 'Seminar',
    description: 'Diskusi, wawasan, dan pembicara inspiratif.',
    icon: 'seminar',
    tone: 'teal',
    color: 'var(--cat-seminar)',
    soft: 'var(--cat-seminar-soft)',
  },
  {
    value: 'Workshop',
    label: 'Workshop',
    description: 'Belajar praktik langsung bersama mentor.',
    icon: 'workshop',
    tone: 'amber',
    color: 'var(--cat-workshop)',
    soft: 'var(--cat-workshop-soft)',
  },
  {
    value: 'Kompetisi',
    label: 'Kompetisi',
    description: 'Adu ide, karya, dan bakat antar mahasiswa.',
    icon: 'kompetisi',
    tone: 'violet',
    color: 'var(--cat-kompetisi)',
    soft: 'var(--cat-kompetisi-soft)',
  },
  {
    value: 'Konser',
    label: 'Konser',
    description: 'Musik, panggung, dan energi komunitas kampus.',
    icon: 'konser',
    tone: 'rose',
    color: 'var(--cat-konser)',
    soft: 'var(--cat-konser-soft)',
  },
]

const fallbackCategory = {
  value: '',
  label: 'Event',
  description: 'Kegiatan kampus pilihan.',
  icon: 'spark',
  tone: 'teal',
  color: 'var(--cat-seminar)',
  soft: 'var(--cat-seminar-soft)',
}

const categoryMap = new Map(eventCategories.map((category) => [category.value, category]))

export function categoryMeta(value) {
  return categoryMap.get(value) ?? { ...fallbackCategory, value: value ?? '', label: value || 'Event' }
}

export function categoryOptions() {
  return eventCategories.map(({ value, label }) => ({ value, label }))
}
