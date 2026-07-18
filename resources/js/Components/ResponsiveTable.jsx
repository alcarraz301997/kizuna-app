import { Link } from '@inertiajs/react';
import { useRef, useEffect, useState } from 'react';

/**
 * ResponsiveTable - renders as cards on mobile (< 768px) and as a standard
 * HTML table on desktop (>= 768px).
 *
 * @param {Array<{key: string, label: string, render?: (row: any) => React.ReactNode}>} columns
 * @param {Array<Record<string, any>>} rows
 * @param {(row: any) => string|number} rowKey
 * @param {(row: any) => React.ReactNode} [actions]
 * @param {string} [emptyMessage]
 * @param {string} [emptyLinkHref]
 * @param {string} [emptyLinkText]
 * @param {string} [className]
 */
export default function ResponsiveTable({
    columns = [],
    rows = [],
    rowKey,
    actions,
    emptyMessage = 'No data yet.',
    emptyLinkHref,
    emptyLinkText,
    className = '',
}) {
    return (
        <div className={`clay-card p-4 sm:p-6 ${className}`}>
            {rows.length === 0 ? (
                <p className="text-center text-gray-500 py-4">
                    {emptyMessage}{' '}
                    {emptyLinkHref && (
                        <Link
                            href={emptyLinkHref}
                            className="font-semibold text-primary hover:underline"
                        >
                            {emptyLinkText || 'Create one'}
                        </Link>
                    )}
                    .
                </p>
            ) : (
                <>
                    {/* Desktop table — hidden below md */}
                    <div className="hidden md:block overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-100">
                            <thead>
                                <tr>
                                    {columns.map((col) => (
                                        <th
                                            key={col.key}
                                            className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500"
                                        >
                                            {col.label}
                                        </th>
                                    ))}
                                    {actions && (
                                        <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            Actions
                                        </th>
                                    )}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-50">
                                {rows.map((row) => (
                                    <tr
                                        key={rowKey(row)}
                                        className="transition-colors hover:bg-white/40"
                                    >
                                        {columns.map((col) => (
                                            <td
                                                key={col.key}
                                                className="whitespace-nowrap px-6 py-4 text-sm"
                                            >
                                                {col.render
                                                    ? col.render(row)
                                                    : row[col.key] ?? '—'}
                                            </td>
                                        ))}
                                        {actions && (
                                            <td className="whitespace-nowrap px-6 py-4 text-right">
                                                <div className="flex items-center justify-end gap-2">
                                                    {actions(row)}
                                                </div>
                                            </td>
                                        )}
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Mobile cards — visible below md */}
                    <div className="md:hidden space-y-4">
                        {rows.map((row) => (
                            <div
                                key={rowKey(row)}
                                className="clay-card p-4 space-y-2.5"
                            >
                                {columns.map((col) => (
                                    <div
                                        key={col.key}
                                        className="flex items-start justify-between gap-2"
                                    >
                                        <span className="text-xs font-semibold uppercase tracking-wider text-gray-500 shrink-0">
                                            {col.label}
                                        </span>
                                        <span className="text-sm text-gray-900 text-right">
                                            {col.render
                                                ? col.render(row)
                                                : row[col.key] ?? '—'}
                                        </span>
                                    </div>
                                ))}
                                {actions && (
                                    <div className="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                                        {actions(row)}
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                </>
            )}
        </div>
    );
}
