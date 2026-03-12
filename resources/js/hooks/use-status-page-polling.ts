import { useCallback, useEffect, useRef, useState } from "react";
import { joinSiteChannel } from "@/lib/echo";
import type {
  ComponentStatus,
  Incident,
  MaintenanceWindow,
  PublicComponent,
} from "@/types/models";

const POLLING_INTERVAL_MS = 30_000;

type StatusApiResponse = {
  data: {
    overall_status: ComponentStatus;
    components: PublicComponent[];
  };
  effective_statuses: Record<number, ComponentStatus>;
  open_incidents: Incident[];
  incident_history: Incident[];
  upcoming_maintenance: MaintenanceWindow[];
  active_maintenance: MaintenanceWindow[];
};

type UseStatusPagePollingOptions = {
  slug: string;
  initialOverallStatus: ComponentStatus;
  initialComponents: PublicComponent[];
  initialEffectiveStatuses: Record<number, ComponentStatus>;
  initialOpenIncidents: Incident[];
  initialIncidentHistory: Incident[];
  initialUpcomingMaintenance: MaintenanceWindow[];
  initialActiveMaintenance: MaintenanceWindow[];
};

type UseStatusPagePollingResult = {
  overallStatus: ComponentStatus;
  components: PublicComponent[];
  effectiveStatuses: Record<number, ComponentStatus>;
  openIncidents: Incident[];
  incidentHistory: Incident[];
  upcomingMaintenance: MaintenanceWindow[];
  activeMaintenance: MaintenanceWindow[];
};

export function useStatusPagePolling({
  slug,
  initialOverallStatus,
  initialComponents,
  initialEffectiveStatuses,
  initialOpenIncidents,
  initialIncidentHistory,
  initialUpcomingMaintenance,
  initialActiveMaintenance,
}: UseStatusPagePollingOptions): UseStatusPagePollingResult {
  const [overallStatus, setOverallStatus] =
    useState<ComponentStatus>(initialOverallStatus);
  const [components, setComponents] =
    useState<PublicComponent[]>(initialComponents);
  const [effectiveStatuses, setEffectiveStatuses] = useState<
    Record<number, ComponentStatus>
  >(initialEffectiveStatuses);
  const [openIncidents, setOpenIncidents] =
    useState<Incident[]>(initialOpenIncidents);
  const [incidentHistory, setIncidentHistory] = useState<Incident[]>(
    initialIncidentHistory
  );
  const [upcomingMaintenance, setUpcomingMaintenance] = useState<
    MaintenanceWindow[]
  >(initialUpcomingMaintenance);
  const [activeMaintenance, setActiveMaintenance] = useState<
    MaintenanceWindow[]
  >(initialActiveMaintenance);

  const intervalRef = useRef<ReturnType<typeof setInterval> | null>(null);

  const fetchStatus = useCallback(async () => {
    try {
      const response = await fetch(`/api/sites/${slug}/status`, {
        headers: { Accept: "application/json" },
      });

      if (!response.ok) {
        return;
      }

      const data = (await response.json()) as StatusApiResponse;

      setOverallStatus(data.data.overall_status);
      setComponents(data.data.components);
      setEffectiveStatuses(data.effective_statuses);
      setOpenIncidents(data.open_incidents);
      setIncidentHistory(data.incident_history);
      setUpcomingMaintenance(data.upcoming_maintenance);
      setActiveMaintenance(data.active_maintenance);
    } catch {
      // Silently ignore network errors — next poll will retry
    }
  }, [slug]);

  const stopPolling = useCallback(() => {
    if (intervalRef.current !== null) {
      clearInterval(intervalRef.current);
      intervalRef.current = null;
    }
  }, []);

  const startPolling = useCallback(() => {
    if (intervalRef.current !== null) {
      return;
    }

    intervalRef.current = setInterval(() => {
      fetchStatus().catch(() => {
        // Silently ignore — next poll will retry
      });
    }, POLLING_INTERVAL_MS);
  }, [fetchStatus]);

  useEffect(() => {
    let isActive = true;
    let cleanupChannel: null | (() => void) = null;

    const connect = async () => {
      const channel = await joinSiteChannel(slug);

      if (!isActive) {
        channel?.unsubscribe();

        return;
      }

      if (channel === null) {
        startPolling();

        return;
      }

      const refreshStatus = () => {
        fetchStatus().catch(() => {
          // Silently ignore — next refresh or poll will retry
        });
      };

      const events = [
        "component.status_changed",
        "incident.created",
        "incident.updated",
        "incident.resolved",
        "maintenance.scheduled",
        "maintenance.started",
        "maintenance.completed",
      ] as const;

      for (const event of events) {
        channel.listen(event, refreshStatus);
      }

      cleanupChannel = () => {
        for (const event of events) {
          channel.stopListening(event);
        }

        channel.unsubscribe();
      };
    };

    connect().catch(() => {
      startPolling();
    });

    return () => {
      isActive = false;
      stopPolling();
      cleanupChannel?.();
    };
  }, [fetchStatus, slug, startPolling, stopPolling]);

  return {
    overallStatus,
    components,
    effectiveStatuses,
    openIncidents,
    incidentHistory,
    upcomingMaintenance,
    activeMaintenance,
  };
}
