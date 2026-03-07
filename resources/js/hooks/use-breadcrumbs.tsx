import type { ReactNode } from "react";
import { createContext, useContext, useEffect, useMemo, useState } from "react";

export type BreadcrumbItem = {
  label: string;
  href: string;
};

type BreadcrumbContextValue = {
  items: BreadcrumbItem[];
  setItems: React.Dispatch<React.SetStateAction<BreadcrumbItem[]>>;
};

const BreadcrumbContext = createContext<BreadcrumbContextValue | null>(null);

export function BreadcrumbProvider({ children }: { children: ReactNode }) {
  const [items, setItems] = useState<BreadcrumbItem[]>([]);

  const value = useMemo(() => ({ items, setItems }), [items]);

  return (
    <BreadcrumbContext.Provider value={value}>
      {children}
    </BreadcrumbContext.Provider>
  );
}

export function useSetBreadcrumbs(items: BreadcrumbItem[]) {
  const context = useContext(BreadcrumbContext);

  if (!context) {
    throw new Error(
      "useSetBreadcrumbs must be used within a BreadcrumbProvider."
    );
  }

  const serialized = JSON.stringify(items);
  const { setItems } = context;

  useEffect(() => {
    setItems(JSON.parse(serialized) as BreadcrumbItem[]);
  }, [serialized, setItems]);
}

export function useBreadcrumbs() {
  const context = useContext(BreadcrumbContext);

  if (!context) {
    throw new Error("useBreadcrumbs must be used within a BreadcrumbProvider.");
  }

  return context.items;
}
