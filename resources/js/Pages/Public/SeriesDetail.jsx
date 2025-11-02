import React from "react";
import { Head, Link } from "@inertiajs/react";

import "../../../css/app.css";

export default function SeriesDetail({ series = {}, seasons = {} }) {
    const [openSeason, setOpenSeason] = React.useState(null);
    const {
        id,
        title,
        poster_url,
        backdrop_url,
        release_date,
        rating,
        status,
        synopsis: description,
        genres = [],
        actors = [],
    } = series;
    console.log("Series Detail:", series);
    console.log("Seasons:Liks", seasons);

    return (
        <div className="min-h-screen bg-gray-50">
            <Head title={title || "Series"} />
            <header className="bg-white shadow sticky top-0 z-10">
                <div className="container mx-auto px-4 py-4 flex items-center justify-between">
                    <h1 className="text-2xl md:text-3xl font-extrabold text-indigo-700">
                        {title || "Series"}
                    </h1>
                    <nav className="space-x-4">
                        <Link
                            href="/"
                            className="text-indigo-600 hover:underline"
                        >
                            Home
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
                                    <span className="text-gray-400">•</span>
                                    <div className="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium">
                                        {status || "N/A"}
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

                            <div>
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
                        </div>
                    </div>

                    {/* Backdrop Image Section - Bottom of Card */}
                    {backdrop_url && (
                        <div className="border-t border-gray-200">
                            <div className="p-6">
                                <h3 className="text-xl font-semibold text-gray-800 mb-4">
                                    Series Backdrop
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

                {/* Seasons Section */}
                <div className="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div className="p-6 lg:p-8">
                        <h3 className="text-2xl font-bold text-gray-800 mb-6">
                            Seasons & Episodes
                        </h3>
                        <div className="space-y-4">
                            {seasons.length === 0 ? (
                                <div className="text-center py-8 text-gray-500">
                                    No seasons available
                                </div>
                            ) : (
                                seasons.map((s) => (
                                    <div
                                        key={s.id || s.number}
                                        className="bg-gray-50 rounded-lg border border-gray-200"
                                    >
                                        <button
                                            type="button"
                                            className="w-full flex items-center justify-between p-4 hover:bg-gray-100 transition-colors duration-200 rounded-lg"
                                            onClick={() =>
                                                setOpenSeason(
                                                    openSeason ===
                                                        (s.id || s.number)
                                                        ? null
                                                        : s.id || s.number
                                                )
                                            }
                                        >
                                            <div className="flex items-center gap-4">
                                                <span className="font-semibold text-lg text-gray-800">
                                                    Season {s.number}
                                                </span>
                                                <span className="text-sm text-gray-500 bg-white px-2 py-1 rounded">
                                                    Episodes:{" "}
                                                    {s.episodes?.length ??
                                                        s.episode_count ??
                                                        "N/A"}
                                                </span>
                                            </div>
                                            <span
                                                className={`text-gray-400 transition-transform duration-200 ${
                                                    openSeason ===
                                                    (s.id || s.number)
                                                        ? "rotate-90"
                                                        : ""
                                                }`}
                                            >
                                                ▶
                                            </span>
                                        </button>
                                        {openSeason === (s.id || s.number) && (
                                            <div className="px-4 pb-4">
                                                {s.episodes &&
                                                s.episodes.length > 0 ? (
                                                    <ul className="space-y-3 mt-3">
                                                        {s.episodes.map(
                                                            (ep) => (
                                                                <li
                                                                    key={ep.id}
                                                                    className="bg-white rounded-lg shadow-sm border border-gray-100 p-4"
                                                                >
                                                                    <div className="flex flex-col gap-3">
                                                                        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                                                            <div className="flex items-center gap-2 flex-wrap">
                                                                                <span className="font-bold text-indigo-700">
                                                                                    Episode{" "}
                                                                                    {
                                                                                        ep.episode_number
                                                                                    }
                                                                                </span>
                                                                                <span className="font-semibold text-gray-800">
                                                                                    {
                                                                                        ep.title
                                                                                    }
                                                                                </span>
                                                                                {ep.runtime && (
                                                                                    <span className="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded">
                                                                                        {
                                                                                            ep.runtime
                                                                                        }{" "}
                                                                                        min
                                                                                    </span>
                                                                                )}
                                                                                <span className="text-xs text-gray-500">
                                                                                    {ep.release_date
                                                                                        ? new Date(
                                                                                              ep.release_date
                                                                                          ).getFullYear()
                                                                                        : "N/A"}
                                                                                </span>
                                                                            </div>
                                                                            <div className="flex gap-2 flex-wrap">
                                                                                {/* Episode Links */}
                                                                                {ep.links &&
                                                                                    ep.links
                                                                                        .stream &&
                                                                                    ep.links.stream
                                                                                        .length >
                                                                                        0 &&
                                                                                    ep.links.stream.map(
                                                                                        (
                                                                                            link,
                                                                                            idx
                                                                                        ) => (
                                                                                            <a
                                                                                                key={
                                                                                                    "stream-" +
                                                                                                    idx
                                                                                                }
                                                                                                href={
                                                                                                    link.link
                                                                                                }
                                                                                                target="_blank"
                                                                                                rel="noopener noreferrer"
                                                                                                className="px-3 py-1 rounded bg-green-100 text-green-700 text-xs font-semibold hover:bg-green-200 border border-green-200 transition-colors duration-200"
                                                                                            >
                                                                                                <span>
                                                                                                    Stream
                                                                                                </span>
                                                                                                <span className="ml-1">
                                                                                                    {
                                                                                                        link.quality
                                                                                                    }
                                                                                                </span>
                                                                                            </a>
                                                                                        )
                                                                                    )}
                                                                                {ep.links &&
                                                                                    ep.links
                                                                                        .download &&
                                                                                    ep.links
                                                                                        .download
                                                                                        .length >
                                                                                        0 &&
                                                                                    ep.links.download.map(
                                                                                        (
                                                                                            link,
                                                                                            idx
                                                                                        ) => (
                                                                                            <a
                                                                                                key={
                                                                                                    "download-" +
                                                                                                    idx
                                                                                                }
                                                                                                href={
                                                                                                    link.link
                                                                                                }
                                                                                                target="_blank"
                                                                                                rel="noopener noreferrer"
                                                                                                className="px-3 py-1 rounded bg-blue-100 text-blue-700 text-xs font-semibold hover:bg-blue-200 border border-blue-200 transition-colors duration-200"
                                                                                            >
                                                                                                <span>
                                                                                                    Download
                                                                                                </span>
                                                                                                <span className="ml-1">
                                                                                                    {
                                                                                                        link.quality
                                                                                                    }
                                                                                                </span>
                                                                                            </a>
                                                                                        )
                                                                                    )}
                                                                            </div>
                                                                        </div>
                                                                        {ep.synopsis && (
                                                                            <div className="text-sm text-gray-600 mt-1">
                                                                                {
                                                                                    ep.synopsis
                                                                                }
                                                                            </div>
                                                                        )}
                                                                    </div>
                                                                </li>
                                                            )
                                                        )}
                                                    </ul>
                                                ) : (
                                                    <div className="text-center py-4 text-gray-400 text-sm">
                                                        No episodes in this
                                                        season.
                                                    </div>
                                                )}
                                            </div>
                                        )}
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </div>

                {/* Back to Series Link */}
                <div className="mt-6 text-center">
                    <Link
                        href="/series"
                        className="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium transition-colors duration-200"
                    >
                        ← Back to series
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