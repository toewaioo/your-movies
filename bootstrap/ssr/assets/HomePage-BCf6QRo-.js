import { jsxs, jsx } from "react/jsx-runtime";
import { useRef, useEffect } from "react";
import { Head, Link } from "@inertiajs/react";
function FeaturedCarousel({ items = [] }) {
  const scrollRef = useRef(null);
  useEffect(() => {
    const interval = setInterval(() => {
      if (scrollRef.current) {
        scrollRef.current.scrollLeft += scrollRef.current.offsetWidth * 0.6;
        if (scrollRef.current.scrollLeft + scrollRef.current.offsetWidth >= scrollRef.current.scrollWidth - 10) {
          scrollRef.current.scrollLeft = 0;
        }
      }
    }, 3e3);
    return () => clearInterval(interval);
  }, []);
  return /* @__PURE__ */ jsx(
    "div",
    {
      ref: scrollRef,
      className: "flex space-x-6 overflow-x-auto pb-4 hide-scrollbar snap-x snap-mandatory",
      style: { scrollBehavior: "smooth" },
      children: items.map((item) => {
        console.log(item);
        const year = item.release_date ? new Date(item.release_date).getFullYear() : "N/A";
        return /* @__PURE__ */ jsxs(
          "div",
          {
            className: "snap-start min-w-[200px] md:min-w-[220px] bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition-transform",
            children: [
              /* @__PURE__ */ jsxs("div", { className: "relative", children: [
                /* @__PURE__ */ jsx(
                  "img",
                  {
                    src: item.poster_url || "/default.jpg",
                    alt: item.title,
                    className: "w-full h-40 md:h-64 object-cover"
                  }
                ),
                /* @__PURE__ */ jsx("div", { className: "absolute left-3 top-3 bg-indigo-600 text-white text-xs px-2 py-1 rounded", children: item.status ? "SERIES" : "MOVIE" })
              ] }),
              /* @__PURE__ */ jsxs("div", { className: "p-4", children: [
                /* @__PURE__ */ jsx("h3", { className: "font-semibold text-lg text-gray-800 truncate", children: item.title }),
                /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between mt-2", children: [
                  /* @__PURE__ */ jsx("div", { className: "text-sm text-gray-500", children: year }),
                  /* @__PURE__ */ jsxs("div", { className: "text-yellow-400 font-bold", children: [
                    "★ ",
                    item.rating || "N/A"
                  ] })
                ] }),
                /* @__PURE__ */ jsx(
                  Link,
                  {
                    href: `/${item.status ? "series" : "movies"}/${item.id}`,
                    className: "inline-block mt-3 text-indigo-600 hover:underline text-sm",
                    children: "View details →"
                  }
                )
              ] })
            ]
          },
          item.id
        );
      })
    }
  );
}
function CardGrid({ items = [], type }) {
  return /* @__PURE__ */ jsx("div", { className: "grid lg:grid-cols-5 md:grid-cols-3 sm:grid-cols-2 grid-cols-2 gap-6", children: items.length === 0 ? /* @__PURE__ */ jsx("div", { className: "col-span-full text-center text-gray-500", children: "No items found." }) : items.map((item) => {
    const year = item.release_date ? new Date(item.release_date).getFullYear() : "N/A";
    console.log(item.runtime);
    return /* @__PURE__ */ jsx(
      "div",
      {
        className: "bg-white rounded-xl shadow hover:shadow-xl transition-shadow overflow-hidden",
        children: /* @__PURE__ */ jsxs(
          Link,
          {
            href: `/${type}/${item.id}`,
            className: "block",
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
                /* @__PURE__ */ jsxs("div", { className: "flex items-center inset-0 mt-2 gap-1 text-sm text-muted-foreground", children: [
                  /* @__PURE__ */ jsx("div", { className: "text-sm text-gray-500 ", children: year }),
                  /* @__PURE__ */ jsx("span", { children: "•" }),
                  /* @__PURE__ */ jsx("p", { className: "text-sm text-gray-600  line-clamp-3", children: item.runtime ? item.runtime : item.status || "N/A" }),
                  /* @__PURE__ */ jsx("span", { children: "•" }),
                  /* @__PURE__ */ jsxs("div", { className: "flex flex-col  text-yellow-400 font-bold", children: [
                    "★ ",
                    item.rating || "N/A"
                  ] })
                ] })
              ] })
            ]
          }
        )
      },
      item.id
    );
  }) });
}
function HomePage({ movies = [], series = [], features = {} }) {
  const featuredItems = [
    ...features.movies || [],
    ...features.series || []
  ];
  return /* @__PURE__ */ jsxs("div", { className: "min-h-screen bg-gradient-to-br from-indigo-50 to-white pb-16", children: [
    /* @__PURE__ */ jsx(Head, { title: "Home" }),
    /* @__PURE__ */ jsx("header", { className: "bg-white/60 backdrop-blur sticky top-0 z-20", children: /* @__PURE__ */ jsxs("div", { className: "container mx-auto px-2 py-2 md:px-6 md:py-6 flex items-center justify-between", children: [
      /* @__PURE__ */ jsx("h1", { className: "text-2xl md:text-3xl font-extrabold text-indigo-700", children: "Movie & Series Hub" }),
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
        ),
        /* @__PURE__ */ jsx(
          Link,
          {
            href: "/series",
            className: "text-gray-600 hover:underline",
            children: "Series"
          }
        )
      ] })
    ] }) }),
    /* @__PURE__ */ jsxs("main", { className: "container mx-auto px-4 pt-4", children: [
      /* @__PURE__ */ jsxs("section", { className: "mb-2", children: [
        /* @__PURE__ */ jsx("h2", { className: "text-2xl font-bold mb-4 text-gray-800", children: "Featured" }),
        /* @__PURE__ */ jsx(FeaturedCarousel, { items: featuredItems })
      ] }),
      /* @__PURE__ */ jsxs("section", { className: "mb-12", children: [
        /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between mb-4", children: [
          /* @__PURE__ */ jsx("h2", { className: "text-2xl font-bold text-indigo-700", children: "Latest Movies" }),
          /* @__PURE__ */ jsx(
            Link,
            {
              href: "/movies",
              className: "text-sm text-indigo-600",
              children: "Browse all"
            }
          )
        ] }),
        /* @__PURE__ */ jsx(CardGrid, { items: movies, type: "movies" })
      ] }),
      /* @__PURE__ */ jsxs("section", { children: [
        /* @__PURE__ */ jsxs("div", { className: "flex items-center justify-between mb-4", children: [
          /* @__PURE__ */ jsx("h2", { className: "text-2xl font-bold text-pink-700", children: "Latest Series" }),
          /* @__PURE__ */ jsx(Link, { href: "/series", className: "text-sm text-pink-600", children: "Browse all" })
        ] }),
        /* @__PURE__ */ jsx(CardGrid, { items: series, type: "series" })
      ] })
    ] }),
    /* @__PURE__ */ jsxs("footer", { className: "bg-white shadow mt-16 py-4 text-center text-gray-500 text-sm", children: [
      "© ",
      (/* @__PURE__ */ new Date()).getFullYear(),
      " Movie & Series Hub. All rights reserved."
    ] })
  ] });
}
export {
  HomePage as default
};
