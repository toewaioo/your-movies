import { jsxs, jsx } from "react/jsx-runtime";
import "react";
import { Head, Link } from "@inertiajs/react";
/* empty css      */
function MovieDetail({ movie = {} }) {
  const {
    id,
    title,
    poster_url,
    backdrop_url,
    release_date,
    rating,
    synopsis: description,
    genres = [],
    actors = [],
    links = {}
  } = movie;
  console.log("Movie Detail:", movie);
  return /* @__PURE__ */ jsxs("div", { className: "min-h-screen bg-gray-50", children: [
    /* @__PURE__ */ jsx(Head, { title: title || "Movie" }),
    /* @__PURE__ */ jsx("header", { className: "bg-white shadow sticky top-0 z-10", children: /* @__PURE__ */ jsxs("div", { className: "container mx-auto px-4 py-4 flex items-center justify-between", children: [
      /* @__PURE__ */ jsx("h1", { className: "text-2xl md:text-3xl font-extrabold text-indigo-700", children: title || "Movie" }),
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
    /* @__PURE__ */ jsxs("main", { className: "container mx-auto px-4 py-8", children: [
      /* @__PURE__ */ jsxs("div", { className: "bg-white rounded-xl shadow-lg overflow-hidden mb-8", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex flex-col lg:flex-row", children: [
          /* @__PURE__ */ jsx("div", { className: "lg:w-1/3 p-6 flex justify-center lg:justify-start", children: /* @__PURE__ */ jsx("div", { className: "w-64 h-96 lg:w-80 lg:h-[28rem] rounded-lg overflow-hidden shadow-md", children: /* @__PURE__ */ jsx(
            "img",
            {
              src: poster_url || "/default.jpg",
              alt: `${title} poster`,
              className: "w-full h-full object-cover"
            }
          ) }) }),
          /* @__PURE__ */ jsxs("div", { className: "lg:w-2/3 p-6 lg:p-8", children: [
            /* @__PURE__ */ jsxs("div", { className: "mb-6", children: [
              /* @__PURE__ */ jsx("h2", { className: "text-3xl font-bold text-gray-800 mb-3", children: title }),
              /* @__PURE__ */ jsxs("div", { className: "flex flex-wrap items-center gap-3 mb-4", children: [
                /* @__PURE__ */ jsxs("div", { className: "text-lg text-gray-700 font-medium", children: [
                  "📅 ",
                  release_date || "N/A"
                ] }),
                /* @__PURE__ */ jsx("span", { className: "text-gray-400", children: "•" }),
                /* @__PURE__ */ jsxs("div", { className: "flex items-center text-yellow-500 font-bold text-lg", children: [
                  "⭐ ",
                  rating || "N/A"
                ] })
              ] })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "mb-6", children: [
              /* @__PURE__ */ jsx("h3", { className: "text-xl font-semibold text-gray-800 mb-3", children: "Synopsis" }),
              /* @__PURE__ */ jsx("p", { className: "text-gray-700 leading-relaxed", children: description || "No description available." })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "mb-6", children: [
              /* @__PURE__ */ jsx("h3", { className: "text-xl font-semibold text-gray-800 mb-3", children: "Genres" }),
              /* @__PURE__ */ jsx("div", { className: "flex flex-wrap gap-2", children: genres.length === 0 ? /* @__PURE__ */ jsx("span", { className: "text-sm text-gray-500", children: "No genres" }) : genres.map((g) => /* @__PURE__ */ jsx(
                "span",
                {
                  className: "px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium",
                  children: g.name
                },
                g.id
              )) })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "mb-6", children: [
              /* @__PURE__ */ jsx("h3", { className: "text-xl font-semibold text-gray-800 mb-3", children: "Cast" }),
              /* @__PURE__ */ jsx("div", { className: "flex flex-wrap gap-3", children: actors.length === 0 ? /* @__PURE__ */ jsx("span", { className: "text-sm text-gray-500", children: "No actors listed" }) : actors.map((a) => /* @__PURE__ */ jsxs(
                Link,
                {
                  href: `/actors/${a.id}`,
                  className: "flex items-center gap-3 bg-gray-50 hover:bg-gray-100 px-4 py-2 rounded-lg transition-colors duration-200 border border-gray-200",
                  children: [
                    /* @__PURE__ */ jsx(
                      "img",
                      {
                        src: a.profile_url || "/default.jpg",
                        alt: a.name,
                        className: "w-10 h-12 object-cover rounded"
                      }
                    ),
                    /* @__PURE__ */ jsx("span", { className: "text-sm font-medium text-gray-700", children: a.name })
                  ]
                },
                a.id
              )) })
            ] }),
            /* @__PURE__ */ jsxs("div", { className: "mb-4", children: [
              /* @__PURE__ */ jsx("h3", { className: "text-xl font-semibold text-gray-800 mb-3", children: "Download Links" }),
              /* @__PURE__ */ jsx("div", { className: "space-y-3 ", children: links.download && links.download.length > 0 ? links.download.map((dl, idx) => /* @__PURE__ */ jsx(
                "a",
                {
                  href: dl.link,
                  target: "_blank",
                  rel: "noopener noreferrer",
                  className: "flex items-center justify-around  bg-indigo-50 hover:bg-indigo-100 px-4 py-3 rounded-lg border border-indigo-200 transition-colors duration-200 group",
                  children: /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between w-full ", children: [
                    /* @__PURE__ */ jsx("span", { className: "font-semibold text-indigo-700", children: dl.label || `Download ${idx + 1}` }),
                    /* @__PURE__ */ jsx("span", { className: "text-xs bg-indigo-200 text-indigo-800 px-2 py-1 rounded", children: dl.quality }),
                    dl.platform && /* @__PURE__ */ jsx("span", { className: "text-xs text-gray-500", children: dl.platform })
                  ] })
                },
                idx
              )) : /* @__PURE__ */ jsx("div", { className: "text-center py-4 text-gray-500 bg-gray-50 rounded-lg border border-gray-200", children: "No download links available." }) })
            ] })
          ] })
        ] }),
        backdrop_url && /* @__PURE__ */ jsx("div", { className: "border-t border-gray-200", children: /* @__PURE__ */ jsxs("div", { className: "p-6", children: [
          /* @__PURE__ */ jsx("h3", { className: "text-xl font-semibold text-gray-800 mb-4", children: "Movie Backdrop" }),
          /* @__PURE__ */ jsx("div", { className: "rounded-lg overflow-hidden shadow-md", children: /* @__PURE__ */ jsx(
            "img",
            {
              src: backdrop_url,
              alt: `${title} backdrop`,
              className: "w-full h-64 md:h-80 object-cover"
            }
          ) })
        ] }) })
      ] }),
      /* @__PURE__ */ jsx("div", { className: "text-center", children: /* @__PURE__ */ jsx(
        Link,
        {
          href: "/movies",
          className: "inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium transition-colors duration-200",
          children: "← Back to movies"
        }
      ) })
    ] }),
    /* @__PURE__ */ jsxs("footer", { className: "bg-white shadow mt-16 py-4 text-center text-gray-500 text-sm", children: [
      "© ",
      (/* @__PURE__ */ new Date()).getFullYear(),
      " Movie & Series Hub. All rights reserved."
    ] })
  ] });
}
export {
  MovieDetail as default
};
