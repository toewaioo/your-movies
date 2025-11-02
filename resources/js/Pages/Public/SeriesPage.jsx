import React from "react";
import { Head, Link } from "@inertiajs/react";

export default function SeriesPage({ series }) {
    console.log("Series Data:", series);
    return (
        <div className="min-h-screen bg-gradient-to-br from-indigo-50 to-white pb-16">
            <Head title="Series" />
            <header className="bg-white shadow sticky top-0 z-10">
                <div className="container mx-auto px-4 py-4 flex items-center justify-between">
                    <h1 className="text-2xl md:text-3xl font-extrabold text-indigo-700">
                        Series
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
                    </nav>
                </div>
            </header>
            <main className="container mx-auto px-4 pt-8">
                <div className="grid lg:grid-cols-5 md:grid-cols-3 sm:grid-cols-2 grid-cols-2 gap-6 mb-8">
                    {series.data.length === 0 ? (
                        <div className="col-span-full text-center text-gray-500">
                            No series found.
                        </div>
                    ) : (
                        series.data.map((item) => (
                            <Link
                                key={item.id}
                                href={`/series/${item.id}`}
                                className="bg-white rounded-xl shadow hover:shadow-xl transition-shadow overflow-hidden block"
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
                                    <div className="flex flex-wrap items-center inset-0 mt-2 gap-1 text-sm text-muted-foreground">
                                        <div className="text-yellow-400 font-bold">
                                            ★ {item.rating || "N/A"}
                                        </div>
                                        <span>•</span>
                                        {item.release_date
                                            ? new Date(
                                                  item.release_date
                                              ).getFullYear()
                                            : "N/A"}
                                            <span>•</span>
                                            {item.status || "N/A"}
                                    </div>
                                    <p className="text-sm text-gray-600 mt-2 line-clamp-3">
                                        {item.synopsis}
                                    </p>
                                </div>
                            </Link>
                        ))
                    )}
                </div>
                {/* Pagination */}
                <div className="flex justify-center gap-2">
                    {series.links.map((link, idx) => (
                        <Link
                            key={idx}
                            href={link.url || "#"}
                            className={`px-3 py-1 rounded ${
                                link.active
                                    ? "bg-indigo-600 text-white"
                                    : "bg-white text-indigo-600 hover:bg-indigo-100"
                            } ${
                                !link.url
                                    ? "pointer-events-none opacity-50"
                                    : ""
                            }`}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    ))}
                </div>
            </main>
            <footer className="bg-white shadow mt-16 py-4 text-center text-gray-500 text-sm">
                &copy; {new Date().getFullYear()} Movie & Series Hub. All rights
                reserved.
            </footer>
        </div>
    );
}
