import { jsxs, jsx } from "react/jsx-runtime";
import "react";
import { Head, Link } from "@inertiajs/react";
function SeriesPage({ series }) {
  console.log("Series Data:", series);
  return /* @__PURE__ */ jsxs("div", { className: "min-h-screen bg-gradient-to-br from-indigo-50 to-white pb-16", children: [
    /* @__PURE__ */ jsx(Head, { title: "Series" }),
    /* @__PURE__ */ jsx("header", { className: "bg-white shadow sticky top-0 z-10", children: /* @__PURE__ */ jsxs("div", { className: "container mx-auto px-4 py-4 flex items-center justify-between", children: [
      /* @__PURE__ */ jsx("h1", { className: "text-2xl md:text-3xl font-extrabold text-indigo-700", children: "Series" }),
      /* @__PURE__ */ jsxs("nav", { className: "space-x-4", children: [
        /* @__PURE__ */ jsx(
          Link,
          {
            href: "/",
            className: "text-indigo-600 hover:underline",
            children: "Home"
          }
        ),
        /* @__PURE__ */ jsx(
          Link,
          {
            href: "/movies",
            className: "text-gray-600 hover:underline",
            children: "Movies"
          }
        )
      ] })
    ] }) }),
    /* @__PURE__ */ jsxs("main", { className: "container mx-auto px-4 pt-8", children: [
      /* @__PURE__ */ jsx("div", { className: "grid lg:grid-cols-5 md:grid-cols-3 sm:grid-cols-2 grid-cols-2 gap-6 mb-8", children: series.data.length === 0 ? /* @__PURE__ */ jsx("div", { className: "col-span-full text-center text-gray-500", children: "No series found." }) : series.data.map((item) => /* @__PURE__ */ jsxs(
        Link,
        {
          href: `/series/${item.id}`,
          className: "bg-white rounded-xl shadow hover:shadow-xl transition-shadow overflow-hidden block",
          children: [
            /* @__PURE__ */ jsxs("div", { className: "relative", children: [
              /* @__PURE__ */ jsx(
                "img",
                {
                  src: item.poster_url || "/default.jpg",
                  alt: item.title,
                  className: "w-full h-56 object-cover"
                }
              ),
              /* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-40" })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "p-3", children: [
              /* @__PURE__ */ jsx("div", { className: "flex items-center justify-between", children: /* @__PURE__ */ jsx("h3", { className: "font-semibold text-gray-800 truncate", children: item.title }) }),
              /* @__PURE__ */ jsxs("div", { className: "flex flex-wrap items-center inset-0 mt-2 gap-1 text-sm text-muted-foreground", children: [
                /* @__PURE__ */ jsxs("div", { className: "text-yellow-400 font-bold", children: [
                  "★ ",
                  item.rating || "N/A"
                ] }),
                /* @__PURE__ */ jsx("span", { children: "•" }),
                item.release_date ? new Date(
                  item.release_date
                ).getFullYear() : "N/A",
                /* @__PURE__ */ jsx("span", { children: "•" }),
                item.status || "N/A"
              ] }),
              /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-600 mt-2 line-clamp-3", children: item.synopsis })
            ] })
          ]
        },
        item.id
      )) }),
      /* @__PURE__ */ jsx("div", { className: "flex justify-center gap-2", children: series.links.map((link, idx) => /* @__PURE__ */ jsx(
        Link,
        {
          href: link.url || "#",
          className: `px-3 py-1 rounded ${link.active ? "bg-indigo-600 text-white" : "bg-white text-indigo-600 hover:bg-indigo-100"} ${!link.url ? "pointer-events-none opacity-50" : ""}`,
          dangerouslySetInnerHTML: { __html: link.label }
        },
        idx
      )) })
    ] }),
    /* @__PURE__ */ jsxs("footer", { className: "bg-white shadow mt-16 py-4 text-center text-gray-500 text-sm", children: [
      "© ",
      (/* @__PURE__ */ new Date()).getFullYear(),
      " Movie & Series Hub. All rights reserved."
    ] })
  ] });
}
export {
  SeriesPage as default
};
