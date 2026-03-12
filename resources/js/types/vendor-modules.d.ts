declare module "laravel-echo" {
  type EchoChannel = {
    listen(event: string, handler: (data: unknown) => void): EchoChannel;
    stopListening(event: string): EchoChannel;
  };

  class Echo {
    constructor(options: Record<string, unknown>);
    channel(name: string): EchoChannel;
    leaveChannel(name: string): void;
    connector?: {
      pusher?: {
        connection?: {
          state?: string;
          bind(event: string, callback: () => void): void;
          unbind(event: string, callback: () => void): void;
        };
      };
    };
  }

  export default Echo;
}

declare module "pusher-js" {
  class Pusher {
    constructor(key: string, options: Record<string, unknown>);
  }

  export default Pusher;
}
