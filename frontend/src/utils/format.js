export function formatDateTime(value) {
  if (!value) {
    return '-'
  }

  return new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value))
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

