import { router } from "@inertiajs/react";
import { useEffect } from "react";
import { toast } from "sonner";

const FLASH_TYPES = ["success", "error", "warning", "info"] as const;

export function useFlashToasts() {
  useEffect(() => {
    return router.on("flash", (event) => {
      const flash = event.detail.flash;

      for (const type of FLASH_TYPES) {
        if (flash[type]) {
          toast[type](flash[type]);
        }
      }
    });
  }, []);
}
