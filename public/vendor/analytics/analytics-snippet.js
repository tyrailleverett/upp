!(() => {
  function t(t) {
    const e = document.cookie.match(new RegExp("(^| )" + t + "=([^;]+)"));
    return e ? decodeURIComponent(e[2]) : null;
  }
  function e(t, e, n) {
    const o =
      n > 0
        ? "; expires=" + new Date(Date.now() + 864e5 * n).toUTCString()
        : "; expires=Thu, 01 Jan 1970 00:00:00 GMT";
    document.cookie = t + "=" + encodeURIComponent(e) + o + "; path=/";
  }
  function n() {
    returntypeof;
    crypto != "undefined" && typeof crypto.randomUUID == "function"
      ? crypto.randomUUID()
      : "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, (t) => {
          const e = (16 * Math.random()) | 0;
          return (t === "x" ? e : (3 & e) | 8).toString(16);
        });
  }
  const o = {
    _config: null,
    _sessionKey: null,
    _optedOut: !1,
    init(e) {
      const o =
        e.csrfToken ??
        (() => {
          const e = document.querySelector('meta[name="csrf-token"]');
          return e
            ? (e.getAttribute("content") ?? "")
            : (t("XSRF-TOKEN") ?? "");
        })();
      if (
        ((this._config = { ...e, csrfToken: o }),
        (this._sessionKey =
          sessionStorage.getItem("analytics_session_key") ?? n()),
        sessionStorage.setItem("analytics_session_key", this._sessionKey),
        (this._optedOut = t("analytics_opt_out") === "1"),
        !1 !== e.autoPageView)
      ) {
        this.track("page_viewed", {
          url: window.location.href,
          referrer: document.referrer || null,
        });
        const t = history.pushState.bind(history);
        history.pushState = (...e) => {
          t(...e), this.track("page_viewed", { url: window.location.href });
        };
      }
    },
    track(o, i = {}) {
      if (this._optedOut || !this._config) {
        return;
      }
      const s = this._config.userId
          ? null
          : ((o) => {
              let i = t(o);
              return i || ((i = n()), e(o, i, 365)), i;
            })(this._config.anonymousIdKey ?? "analytics_anon_id"),
        r = {
          event: o,
          properties: i,
          session_key: this._sessionKey,
          anonymous_id: s,
          url: window.location.href,
          referrer: document.referrer || void 0,
        },
        c = this._config.endpoint,
        a = this._config.csrfToken ?? "",
        u = JSON.stringify(r);
      if (navigator.sendBeacon && !a) {
        const t = new Blob([u], { type: "application/json" });
        navigator.sendBeacon(c, t);
      } else {
        fetch(c, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": a,
            "X-Requested-With": "XMLHttpRequest",
          },
          body: u,
          keepalive: !0,
        }).catch(() => {});
      }
    },
    optOut() {
      (this._optedOut = !0), e("analytics_opt_out", "1", 365);
    },
    optIn() {
      (this._optedOut = !1), e("analytics_opt_out", "0", 0);
    },
  };
  window.Analytics = o;
})();
