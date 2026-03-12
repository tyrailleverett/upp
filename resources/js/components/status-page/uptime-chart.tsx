import type { ComponentUptime } from "@/types/models";

function uptimeColor(percentage: number): string {
  if (percentage >= 100) {
    return "bg-green-500";
  }
  if (percentage > 99) {
    return "bg-yellow-400";
  }
  if (percentage > 95) {
    return "bg-orange-400";
  }
  return "bg-red-500";
}

interface UptimeChartProps {
  uptimeHistory: ComponentUptime;
}

export function UptimeChart({ uptimeHistory }: UptimeChartProps) {
  const { days } = uptimeHistory;

  if (days.length === 0) {
    return (
      <div className="text-gray-400 text-xs dark:text-gray-600">
        No uptime data available
      </div>
    );
  }

  const avgUptime =
    days.reduce((sum, d) => sum + d.uptime_percentage, 0) / days.length;

  return (
    <section aria-label="90-day uptime history">
      <div className="flex items-center gap-1">
        {days.map((day) => (
          <div
            className={`h-8 w-full min-w-0 flex-1 rounded-sm ${uptimeColor(day.uptime_percentage)} cursor-default opacity-80 transition-opacity hover:opacity-100`}
            key={day.date}
            title={`${day.date}: ${day.uptime_percentage.toFixed(2)}%`}
          />
        ))}
      </div>
      <div className="mt-1 flex items-center justify-between">
        <span className="text-gray-400 text-xs dark:text-gray-500">
          90 days ago
        </span>
        <span className="text-gray-500 text-xs dark:text-gray-400">
          {avgUptime.toFixed(2)}% avg
        </span>
        <span className="text-gray-400 text-xs dark:text-gray-500">Today</span>
      </div>
    </section>
  );
}
