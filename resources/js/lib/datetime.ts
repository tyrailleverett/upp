export function localDatetimeToUtcIso(value: string): string {
  return new Date(value).toISOString();
}

export function isoDatetimeToLocalInputValue(value: string): string {
  const date = new Date(value);
  const pad = (number: number) => number.toString().padStart(2, "0");

  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}
