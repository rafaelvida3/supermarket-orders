/* ===== Day.js setup ===== */
import dayjs from 'dayjs'
import 'dayjs/locale/pt-br'
import timezone from 'dayjs/plugin/timezone'
import utc from 'dayjs/plugin/utc'

/* Extend Day.js with plugins and set default locale */
dayjs.extend(utc)
dayjs.extend(timezone)
dayjs.locale('pt-br')

/**
 * Formats a numeric value as BRL currency (e.g., R$ 1.234,56).
 *
 * @param {number|string} value - The number to format.
 * @returns {string} The formatted currency string.
 */
export const formatPrice = (value) => {
  if (value === null || value === undefined || value === '') return 'R$ 0,00'
  const number = Number(value)
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 2,
  }).format(number)
}

/**
 * Formats a date to Brazilian format (DD/MM/YYYY by default).
 *
 * @param {string|Date} value - The date to format (ISO string or Date object).
 * @param {string} [pattern='DD/MM/YYYY'] - Custom output format pattern.
 * @returns {string} The formatted date or '-' if invalid.
 */
export const formatDate = (value, pattern = 'DD/MM/YYYY') => {
  if (!value) return '-'
  const date = dayjs(value)
  return date.isValid() ? date.format(pattern) : '-'
}