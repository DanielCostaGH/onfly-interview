const dateFormatter = new Intl.DateTimeFormat('pt-BR', {
  day: '2-digit',
  month: '2-digit',
  year: 'numeric',
});

const timeFormatter = new Intl.DateTimeFormat('pt-BR', {
  hour: '2-digit',
  minute: '2-digit',
  hour12: false,
});

function parseDate(value?: string | null): Date | null {
  if (!value) return null;
  if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    const parts = value.split('-');
    if (parts.length === 3) {
      const year = Number(parts[0]);
      const month = Number(parts[1]);
      const day = Number(parts[2]);
      if (!Number.isNaN(year) && !Number.isNaN(month) && !Number.isNaN(day)) {
        return new Date(year, month - 1, day);
      }
    }
  }
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) {
    return null;
  }
  return date;
}

function formatParts(value: Date): { day: string; month: string; year: string } {
  const parts = dateFormatter.formatToParts(value);
  const day = parts.find((part) => part.type === 'day')?.value ?? '';
  const month = parts.find((part) => part.type === 'month')?.value ?? '';
  const year = parts.find((part) => part.type === 'year')?.value ?? '';
  return { day, month, year };
}

export function formatDate(value?: string | null): string {
  const date = parseDate(value);
  if (!date) return '';
  const { day, month, year } = formatParts(date);
  return `${day}-${month}-${year}`;
}

export function formatDateTime(value?: string | null): string {
  const date = parseDate(value);
  if (!date) return '';
  const { day, month, year } = formatParts(date);
  const time = timeFormatter.format(date);
  return `${day}-${month}-${year} · ${time}`;
}
