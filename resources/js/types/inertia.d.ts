import "@inertiajs/core";

declare module "@inertiajs/core" {
  interface InertiaConfig {
    flashDataType: {
      success?: string;
      error?: string;
      warning?: string;
      info?: string;
    };
  }
}
