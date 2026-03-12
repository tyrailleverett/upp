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
