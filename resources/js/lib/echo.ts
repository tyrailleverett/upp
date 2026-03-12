export type StatusPageEventMap = {
  "component.status_changed": {
    component_id: number;
    name: string;
    status: string;
    previous_status: string;
  };
  "incident.created": {
    incident_id: number;
    title: string;
    status: string;
    component_ids: number[];
    initial_message: string;
  };
  "incident.updated": {
    incident_id: number;
    status: string;
    message: string;
    created_at: string;
  };
  "incident.resolved": {
    incident_id: number;
    title: string;
    resolved_at: string;
    postmortem: string | null;
  };
  "maintenance.scheduled": {
    maintenance_window_id: number;
    title: string;
    scheduled_at: string;
    ends_at: string;
    component_ids: number[];
  };
  "maintenance.started": {
    maintenance_window_id: number;
    title: string;
    component_ids: number[];
  };
  "maintenance.completed": {
    maintenance_window_id: number;
    title: string;
    completed_at: string;
  };
};

type EventHandler<T extends keyof StatusPageEventMap> = (
  data: StatusPageEventMap[T]
) => void;

export interface StatusPageChannel {
  listen<T extends keyof StatusPageEventMap>(
    event: T,
    handler: EventHandler<T>
  ): StatusPageChannel;
  stopListening<T extends keyof StatusPageEventMap>(
    event: T
  ): StatusPageChannel;
  unsubscribe(): void;
}

import type Echo from "laravel-echo";
import type Pusher from "pusher-js";

type EchoInstance = Echo;

type EchoChannel = ReturnType<EchoInstance["channel"]>;

const CONNECTION_TIMEOUT_MS = 3000;

let echoPromise: Promise<EchoInstance | null> | null = null;

function readMetaContent(name: string): string | null {
  return (
    document
      .querySelector(`meta[name="${name}"]`)
      ?.getAttribute("content")
      ?.trim() || null
  );
}

export async function joinSiteChannel(
  slug: string
): Promise<StatusPageChannel | null> {
  if (typeof window === "undefined") {
    return null;
  }

  const echo = await getEcho();

  if (echo === null) {
    return null;
  }

  const channelName = `site.${slug}`;
  const rawChannel = echo.channel(channelName);
  const connection = echo.connector?.pusher?.connection;

  if (!connection || connection.state === "connected") {
    return createChannel(rawChannel, echo, channelName);
  }

  return await new Promise<StatusPageChannel | null>((resolve) => {
    let settled = false;

    const cleanup = () => {
      connection.unbind("connected", handleConnected);
      connection.unbind("unavailable", handleFailure);
      connection.unbind("failed", handleFailure);
      connection.unbind("error", handleFailure);
      window.clearTimeout(timeoutId);
    };

    const handleConnected = () => {
      if (settled) {
        return;
      }

      settled = true;
      cleanup();
      resolve(createChannel(rawChannel, echo, channelName));
    };

    const handleFailure = () => {
      if (settled) {
        return;
      }

      settled = true;
      cleanup();
      echo.leaveChannel(channelName);
      resolve(null);
    };

    const timeoutId = window.setTimeout(handleFailure, CONNECTION_TIMEOUT_MS);

    connection.bind("connected", handleConnected);
    connection.bind("unavailable", handleFailure);
    connection.bind("failed", handleFailure);
    connection.bind("error", handleFailure);
  });
}

function getEcho(): Promise<EchoInstance | null> {
  const key = readMetaContent("reverb-app-key");
  const host = readMetaContent("reverb-host");
  const port = Number(readMetaContent("reverb-port") ?? "443");
  const scheme = readMetaContent("reverb-scheme") ?? "https";

  if (!(key && host)) {
    return Promise.resolve(null);
  }

  echoPromise ??= createEchoInstance({
    host,
    key,
    port,
    scheme,
  });

  return echoPromise;
}

async function createEchoInstance({
  host,
  key,
  port,
  scheme,
}: {
  host: string;
  key: string;
  port: number;
  scheme: string;
}): Promise<EchoInstance | null> {
  try {
    const [{ default: EchoClass }, { default: PusherClass }] =
      await Promise.all([import("laravel-echo"), import("pusher-js")]);

    const PusherConstructor = PusherClass as typeof Pusher;

    return new EchoClass({
      broadcaster: "reverb",
      client: new PusherConstructor(key, {
        wsHost: host,
        wsPort: port,
        wssPort: port,
        forceTLS: scheme === "https",
        enabledTransports: ["ws", "wss"],
      }),
    }) as EchoInstance;
  } catch {
    return null;
  }
}

function createChannel(
  rawChannel: EchoChannel,
  echo: EchoInstance,
  channelName: string
): StatusPageChannel {
  return {
    listen(event, handler) {
      rawChannel.listen(`.${event}`, handler as (data: unknown) => void);

      return this;
    },
    stopListening(event) {
      rawChannel.stopListening(event);

      return this;
    },
    unsubscribe() {
      echo.leaveChannel(channelName);
    },
  };
}
