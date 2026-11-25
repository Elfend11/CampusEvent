export function formatDateTime(value) {
  if (!value) {
    return '-'
  }

  return new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value))
}

function safeDate(value) {
  if (!value) {
    return null
  }

  const date = new Date(value)

  return Number.isNaN(date.getTime()) ? null : date
}

export function formatEventDay(value) {
  const date = safeDate(value)

  return date ? new Intl.DateTimeFormat('id-ID', { day: '2-digit' }).format(date) : '--'
}

export function formatEventMonth(value) {
  const date = safeDate(value)

  return date ? new Intl.DateTimeFormat('id-ID', { month: 'short' }).format(date).replace('.', '') : '---'
}

export function formatEventDate(value) {
  const date = safeDate(value)

  if (!date) {
    return 'Jadwal menyusul'
  }

  return new Intl.DateTimeFormat('id-ID', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }).format(date)
}

export function formatEventTime(value) {
  const date = safeDate(value)

  return date
    ? new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit' }).format(date)
    : '--.--'
}

export function formatEventMoment(value) {
  const date = safeDate(value)

  if (!date) {
    return 'Jadwal menyusul'
  }

  return `${formatEventDate(value)} · ${formatEventTime(value)}`
}

export function isFutureDate(value) {
  const date = safeDate(value)

  return Boolean(date && date.getTime() > Date.now())
}

export function isDateWithin(value, start, end) {
  const date = safeDate(value)

  if (!date) {
    return false
  }

  const now = Date.now()
  const from = safeDate(start)
  const to = safeDate(end)

  return (!from || from.getTime() <= now) && (!to || now <= to.getTime())
}

export function formatPrice(value) {
  if (value === null || value === undefined) {
    return '-'
  }

  if (Number(value) === 0) {
    return 'Gratis'
  }

  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(Number(value))
}

export function toDateTimeLocal(value) {
  if (!value) {
    return ''
  }

  const date = new Date(value)
  const offset = date.getTimezoneOffset()
  const local = new Date(date.getTime() - offset * 60 * 1000)

  return local.toISOString().slice(0, 16)
}

export const registrationStatusLabels = {
  PENDING: 'Menunggu Pembayaran',
  CONFIRMED: 'Terkonfirmasi',
  EXPIRED: 'Kedaluwarsa',
  CANCELLED: 'Dibatalkan',
}

export const paymentStatusLabels = {
  PENDING: 'Belum Dibayar',
  PAID: 'Lunas',
  FAILED: 'Gagal',
}

export function registrationStatusLabel(status) {
  return registrationStatusLabels[status] ?? status
}

export function paymentStatusLabel(status) {
  return paymentStatusLabels[status] ?? status
}

