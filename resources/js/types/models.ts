export type SiteVisibility = "draft" | "published" | "suspended";

export type ComponentStatus =
  | "operational"
  | "degraded_performance"
  | "partial_outage"
  | "major_outage"
  | "under_maintenance";

export interface Component {
  id: number;
  site_id: number;
  name: string;
  description: string | null;
  group: string | null;
  status: ComponentStatus;
  sort_order: number;
  created_at: string;
  updated_at: string;
}

export interface Site {
  id: number;
  user_id: number;
  name: string;
  slug: string;
  description: string | null;
  visibility: SiteVisibility;
  custom_domain: string | null;
  logo_path: string | null;
  favicon_path: string | null;
  accent_color: string | null;
  custom_css: string | null;
  meta_title: string | null;
  meta_description: string | null;
  published_at: string | null;
  suspended_at: string | null;
  created_at: string;
  updated_at: string;
  components?: Component[];
  components_count?: number;
}

export type IncidentStatus =
  | "investigating"
  | "identified"
  | "monitoring"
  | "resolved";

export interface IncidentUpdate {
  id: number;
  incident_id: number;
  status: IncidentStatus;
  message: string;
  created_at: string;
}

export interface Incident {
  id: number;
  site_id: number;
  title: string;
  status: IncidentStatus;
  postmortem: string | null;
  resolved_at: string | null;
  created_at: string;
  updated_at: string;
  components?: Component[];
  updates?: IncidentUpdate[];
}

export interface MaintenanceWindow {
  id: number;
  site_id: number;
  title: string;
  description: string | null;
  scheduled_at: string;
  ends_at: string;
  completed_at: string | null;
  created_at: string;
  updated_at: string;
  components?: Component[];
}

export interface PublicSiteStatus {
  name: string;
  slug: string;
  description: string | null;
  overall_status: ComponentStatus;
  components: PublicComponent[];
  active_incidents_count: number;
  meta_title: string | null;
  meta_description: string | null;
  accent_color: string | null;
  logo_path: string | null;
  favicon_path: string | null;
}

export interface PublicComponent {
  id: number;
  name: string;
  description: string | null;
  group: string | null;
  status: ComponentStatus;
  sort_order: number;
}

export interface UptimeDay {
  date: string;
  uptime_percentage: number;
}

export interface ComponentUptime {
  component_id: number;
  component_name: string;
  days: UptimeDay[];
}
