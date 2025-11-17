import { jsxs, jsx } from "react/jsx-runtime";
import "react";
import { G as GuestLayout } from "./GuestLayout-B-R2jca9.js";
import { Head, Link } from "@inertiajs/react";
import "./ApplicationLogo-xMpxFOcX.js";
function NotFound() {
  return /* @__PURE__ */ jsxs(GuestLayout, { children: [
    /* @__PURE__ */ jsx(Head, { title: "404 Not Found" }),
    /* @__PURE__ */ jsxs("div", { className: "text-center py-16", children: [
      /* @__PURE__ */ jsx("h1", { className: "text-4xl font-bold mb-4", children: "404" }),
      /* @__PURE__ */ jsx("p", { className: "mb-6 text-lg", children: "Sorry you are hacked, the page you are looking for could not be found." }),
      /* @__PURE__ */ jsx(Link, { href: "/", className: "text-indigo-600 hover:underline", children: "Go Home" })
    ] })
  ] });
}
export {
  NotFound as default
};
