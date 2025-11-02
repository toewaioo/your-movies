import React, { useRef, useEffect } from "react";
import { Head, Link } from "@inertiajs/react";

function FeaturedCarousel({ items = [] }) {
    const scrollRef = useRef(null);
    useEffect(() => {
        const interval = setInterval(() => {
            if (scrollRef.current) {
                scrollRef.current.scrollLeft +=
                    scrollRef.current.offsetWidth * 0.6;
                if (
                    scrollRef.current.scrollLeft +
                        scrollRef.current.offsetWidth >=
                    scrollRef.current.scrollWidth - 10
                ) {
                    scrollRef.current.scrollLeft = 0;
                }
            }
        }, 3000);
        return () => clearInterval(interval);
    }, []);

    return (
        <div
            ref={scrollRef}
            className="flex space-x-6 overflow-x-auto pb-4 hide-scrollbar snap-x snap-mandatory"
            style={{ scrollBehavior: "smooth" }}
        >
            {items.map((item) => {
                console.log(item);
                const year = item.release_date
                    ? new Date(item.release_date).getFullYear()
                    : "N/A";
                return (
                    <div
                        key={item.id}
                        className="snap-start min-w-[200px] md:min-w-[220px] bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition-transform"
                    >
                        <div className="relative">
                            <img
                                src={item.poster_url || "/default.jpg"}
                                alt={item.title}
                                className="w-full h-40 md:h-64 object-cover"
                            />
                            <div className="absolute left-3 top-3 bg-indigo-600 text-white text-xs px-2 py-1 rounded">
                                {item.status ? "SERIES" : "MOVIE"}
                            </div>
                        </div>
                        <div className="p-4">
                            <h3 className="font-semibold text-lg text-gray-800 truncate">
                                {item.title}
                            </h3>
                            <div className="flex items-center justify-between mt-2">
                                <div className="text-sm text-gray-500">
                                    {year}
                                </div>
                                <div className="text-yellow-400 font-bold">
                                    ★ {item.rating || "N/A"}
                                </div>
                            </div>
                            <Link
                                href={`/${item.status ? "series" : "movies"}/${
                                    item.id
                                }`}
                                className="inline-block mt-3 text-indigo-600 hover:underline text-sm"
                            >
                                View details →
                            </Link>
                        </div>
                    </div>
                );
            })}
        </div>
    );
}

function CardGrid({ items = [], type }) {
    return (
        <div className="grid lg:grid-cols-5 md:grid-cols-3 sm:grid-cols-2 grid-cols-2 gap-6">
            {items.length === 0 ? (
                <div className="col-span-full text-center text-gray-500">
                    No items found.
                </div>
            ) : (
                items.map((item) => {
                    const year = item.release_date
                        ? new Date(item.release_date).getFullYear()
                        : "N/A";
                    console.log(item.runtime);
                    return (
                        <div
                            key={item.id}
                            className="bg-white rounded-xl shadow hover:shadow-xl transition-shadow overflow-hidden"
                        >
                            <Link
                                href={`/${type}/${item.id}`}
                                className="block"
                            >
                                <div className="relative">
                                    <img
                                        src={item.poster_url || "/default.jpg"}
                                        alt={item.title}
                                        className="w-full h-56 object-cover"
                                    />
                                    <div className="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-40"></div>
                                </div>
                                <div className="p-3">
                                    <div className="flex items-center justify-between">
                                        <h3 className="font-semibold text-gray-800 truncate">
                                            {item.title}
                                        </h3>
                                    </div>
                                    <div className="flex items-center inset-0 mt-2 gap-1 text-sm text-muted-foreground">
                                        <div className="text-sm text-gray-500 ">
                                            {year}
                                        </div>
                                        <span>•</span>
                                        <p className="text-sm text-gray-600  line-clamp-3">
                                            {item.runtime
                                                ? item.runtime
                                                : item.status || "N/A"}
                                        </p>
                                        <span>•</span>
                                        <div className="flex flex-col  text-yellow-400 font-bold">
                                            ★ {item.rating || "N/A"}
                                        </div>
                                    </div>
                                </div>
                            </Link>
                        </div>
                    );
                })
            )}
        </div>
    );
}

export default function HomePage({ movies = [], series = [], features = {} }) {
    const featuredItems = [
        ...(features.movies || []),
        ...(features.series || []),
    ];

    return (
        <div className="min-h-screen bg-gradient-to-br from-indigo-50 to-white pb-16">
            <Head title="Home" />

            <header className="bg-white/60 backdrop-blur sticky top-0 z-20">
                <div className="container mx-auto px-2 py-2 md:px-6 md:py-6 flex items-center justify-between">
                    <h1 className="text-2xl md:text-3xl font-extrabold text-indigo-700">
                        Movie & Series Hub
                    </h1>
                    <nav className="space-x-4">
                        <Link
                            href="/"
                            className="text-indigo-600 hover:underline"
                        >
                            Home
                        </Link>
                        <Link
                            href="/movies"
                            className="text-gray-600 hover:underline"
                        >
                            Movies
                        </Link>
                        <Link
                            href="/series"
                            className="text-gray-600 hover:underline"
                        >
                            Series
                        </Link>
                    </nav>
                </div>
            </header>

            <main className="container mx-auto px-4 pt-4">
                <section className="mb-2">
                    <h2 className="text-2xl font-bold mb-4 text-gray-800">
                        Featured
                    </h2>
                    <FeaturedCarousel items={featuredItems} />
                </section>

                <section className="mb-12">
                    <div className="flex items-center justify-between mb-4">
                        <h2 className="text-2xl font-bold text-indigo-700">
                            Latest Movies
                        </h2>
                        <Link
                            href="/movies"
                            className="text-sm text-indigo-600"
                        >
                            Browse all
                        </Link>
                    </div>
                    <CardGrid items={movies} type="movies" />
                </section>

                <section>
                    <div className="flex items-center justify-between mb-4">
                        <h2 className="text-2xl font-bold text-pink-700">
                            Latest Series
                        </h2>
                        <Link href="/series" className="text-sm text-pink-600">
                            Browse all
                        </Link>
                    </div>
                    <CardGrid items={series} type="series" />
                </section>
            </main>

            <footer className="bg-white shadow mt-16 py-4 text-center text-gray-500 text-sm">
                &copy; {new Date().getFullYear()} Movie & Series Hub. All rights
                reserved.
            </footer>
        </div>
    );
}
