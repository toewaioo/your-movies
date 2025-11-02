import React from "react";
import { Head, Link } from "@inertiajs/react";

import "../../../css/app.css";

export default function MovieDetail({ movie = {} }) {
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
        links = {},
    } = movie;
    console.log("Movie Detail:", movie);

    return (
        <div className="min-h-screen bg-gray-50">
            <Head title={title || "Movie"} />
            <header className="bg-white shadow sticky top-0 z-10">
                <div className="container mx-auto px-4 py-4 flex items-center justify-between">
                    <h1 className="text-2xl md:text-3xl font-extrabold text-indigo-700">
                        {title || "Movie"}
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

            <main className="container mx-auto px-4 py-8">
                {/* Card View Container */}
                <div className="bg-white rounded-xl shadow-lg overflow-hidden mb-8">
                    {/* Main Content Card */}
                    <div className="flex flex-col lg:flex-row">
                        {/* Left Side - Poster */}
                        <div className="lg:w-1/3 p-6 flex justify-center lg:justify-start">
                            <div className="w-64 h-96 lg:w-80 lg:h-[28rem] rounded-lg overflow-hidden shadow-md">
                                <img
                                    src={poster_url || "/default.jpg"}
                                    alt={`${title} poster`}
                                    className="w-full h-full object-cover"
                                />
                            </div>
                        </div>

                        {/* Right Side - Details */}
                        <div className="lg:w-2/3 p-6 lg:p-8">
                            <div className="mb-6">
                                <h2 className="text-3xl font-bold text-gray-800 mb-3">
                                    {title}
                                </h2>
                                <div className="flex flex-wrap items-center gap-3 mb-4">
                                    <div className="text-lg text-gray-700 font-medium">
                                        📅 {release_date || "N/A"}
                                    </div>
                                    <span className="text-gray-400">•</span>
                                    <div className="flex items-center text-yellow-500 font-bold text-lg">
                                        ⭐ {rating || "N/A"}
                                    </div>
                                </div>
                            </div>

                            <div className="mb-6">
                                <h3 className="text-xl font-semibold text-gray-800 mb-3">
                                    Synopsis
                                </h3>
                                <p className="text-gray-700 leading-relaxed">
                                    {description || "No description available."}
                                </p>
                            </div>

                            <div className="mb-6">
                                <h3 className="text-xl font-semibold text-gray-800 mb-3">
                                    Genres
                                </h3>
                                <div className="flex flex-wrap gap-2">
                                    {genres.length === 0 ? (
                                        <span className="text-sm text-gray-500">
                                            No genres
                                        </span>
                                    ) : (
                                        genres.map((g) => (
                                            <span
                                                key={g.id}
                                                className="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium"
                                            >
                                                {g.name}
                                            </span>
                                        ))
                                    )}
                                </div>
                            </div>

                            <div className="mb-6">
                                <h3 className="text-xl font-semibold text-gray-800 mb-3">
                                    Cast
                                </h3>
                                <div className="flex flex-wrap gap-3">
                                    {actors.length === 0 ? (
                                        <span className="text-sm text-gray-500">
                                            No actors listed
                                        </span>
                                    ) : (
                                        actors.map((a) => (
                                            <Link
                                                key={a.id}
                                                href={`/actors/${a.id}`}
                                                className="flex items-center gap-3 bg-gray-50 hover:bg-gray-100 px-4 py-2 rounded-lg transition-colors duration-200 border border-gray-200"
                                            >
                                                <img
                                                    src={
                                                        a.profile_url ||
                                                        "/default.jpg"
                                                    }
                                                    alt={a.name}
                                                    className="w-10 h-12 object-cover rounded"
                                                />
                                                <span className="text-sm font-medium text-gray-700">
                                                    {a.name}
                                                </span>
                                            </Link>
                                        ))
                                    )}
                                </div>
                            </div>

                            {/* Download Links Section */}
                            <div className="mb-4">
                                <h3 className="text-xl font-semibold text-gray-800 mb-3">
                                    Download Links
                                </h3>
                                <div className="space-y-3 ">
                                    {links.download && links.download.length > 0 ? (
                                        links.download.map((dl, idx) => (
                                            <a
                                                key={idx}
                                                href={dl.link}
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                className="flex items-center justify-around  bg-indigo-50 hover:bg-indigo-100 px-4 py-3 rounded-lg border border-indigo-200 transition-colors duration-200 group"
                                            >
                                                <div className="flex items-center justify-between w-full ">
                                                    <span className="font-semibold text-indigo-700">
                                                        {dl.label || `Download ${idx + 1}`}
                                                    </span>
                                                    <span className="text-xs bg-indigo-200 text-indigo-800 px-2 py-1 rounded">
                                                        {dl.quality}
                                                    </span>
                                                    {dl.platform && (
                                                        <span className="text-xs text-gray-500">
                                                            {dl.platform}
                                                        </span>
                                                    )}
                                                </div>
                                                
                                            </a>
                                        ))
                                    ) : (
                                        <div className="text-center py-4 text-gray-500 bg-gray-50 rounded-lg border border-gray-200">
                                            No download links available.
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Backdrop Image Section - Bottom of Card */}
                    {backdrop_url && (
                        <div className="border-t border-gray-200">
                            <div className="p-6">
                                <h3 className="text-xl font-semibold text-gray-800 mb-4">
                                    Movie Backdrop
                                </h3>
                                <div className="rounded-lg overflow-hidden shadow-md">
                                    <img
                                        src={backdrop_url}
                                        alt={`${title} backdrop`}
                                        className="w-full h-64 md:h-80 object-cover"
                                    />
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                {/* Back to Movies Link */}
                <div className="text-center">
                    <Link
                        href="/movies"
                        className="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium transition-colors duration-200"
                    >
                        ← Back to movies
                    </Link>
                </div>
            </main>

            <footer className="bg-white shadow mt-16 py-4 text-center text-gray-500 text-sm">
                &copy; {new Date().getFullYear()} Movie & Series Hub. All rights
                reserved.
            </footer>
        </div>
    );
}