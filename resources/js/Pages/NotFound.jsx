import React from "react";
import GuestLayout from "@/Layouts/GuestLayout";
import { Head, Link } from "@inertiajs/react";

export default function NotFound() {
    return (
        <GuestLayout>
            <Head title="404 Not Found" />
            <div className="text-center py-16">
                <h1 className="text-4xl font-bold mb-4">404</h1>
                <p className="mb-6 text-lg">
                    Sorry you are hacked, the page you are looking for could not be found.
                </p>
                <Link href="/" className="text-indigo-600 hover:underline">
                    Go Home
                </Link>
            </div>
        </GuestLayout>
    );
}
