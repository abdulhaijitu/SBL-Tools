import React from "react";
import { ChevronLeft, ChevronRight } from "lucide-react";

export interface PaginationProps {
    currentPage: number;
    totalItems: number;
    pageSize: number;
    onPageChange: (page: number) => void;
    onPageSizeChange?: (size: number) => void;
    pageSizeOptions?: number[];
    lang?: "bn" | "en";
    className?: string;
}

export const Pagination: React.FC<PaginationProps> = ({
    currentPage,
    totalItems,
    pageSize,
    onPageChange,
    onPageSizeChange,
    pageSizeOptions = [10, 25, 50, 100],
    lang = "bn",
    className = "",
}) => {
    const totalPages = Math.max(1, Math.ceil(totalItems / pageSize));
    const safeCurrentPage = Math.min(Math.max(1, currentPage), totalPages);

    const startItem = totalItems === 0 ? 0 : (safeCurrentPage - 1) * pageSize + 1;
    const endItem = Math.min(totalItems, safeCurrentPage * pageSize);

    // Generate page numbers with ellipsis
    const getPageNumbers = () => {
        const pages: (number | string)[] = [];
        if (totalPages <= 7) {
            for (let i = 1; i <= totalPages; i++) pages.push(i);
        } else {
            pages.push(1);
            if (safeCurrentPage > 3) {
                pages.push("...");
            }
            const start = Math.max(2, safeCurrentPage - 1);
            const end = Math.min(totalPages - 1, safeCurrentPage + 1);
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            if (safeCurrentPage < totalPages - 2) {
                pages.push("...");
            }
            pages.push(totalPages);
        }
        return pages;
    };

    if (totalItems <= 0) {
        return null;
    }

    return (
        <div
            className={`flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 pb-2 text-xs text-slate-300 ${className}`}
        >
            {/* Left: Results summary */}
            <div className="flex items-center gap-2">
                <span>
                    {lang === "bn" ? (
                        <>
                            মোট <strong className="text-white font-bold">{totalItems}</strong> টির মধ্যে{" "}
                            <strong className="text-white font-bold">{startItem}–{endItem}</strong> দেখানো হচ্ছে
                        </>
                    ) : (
                        <>
                            Showing <strong className="text-white font-bold">{startItem}–{endItem}</strong> of{" "}
                            <strong className="text-white font-bold">{totalItems}</strong>
                        </>
                    )}
                </span>

                {/* Page Size Selector */}
                {onPageSizeChange && (
                    <div className="flex items-center gap-1.5 ml-2 pl-3 border-l border-slate-700">
                        <span className="text-[11px] text-slate-400">
                            {lang === "bn" ? "প্রতি পাতায়:" : "Per page:"}
                        </span>
                        <select
                            value={pageSize}
                            onChange={(e) => {
                                onPageSizeChange(Number(e.target.value));
                                onPageChange(1);
                            }}
                            className="bg-slate-800 border border-slate-700 rounded-lg px-2 py-1 text-xs text-white focus:outline-hidden focus:border-orange-500 transition-colors"
                        >
                            {pageSizeOptions.map((opt) => (
                                <option key={opt} value={opt}>
                                    {opt}
                                </option>
                            ))}
                        </select>
                    </div>
                )}
            </div>

            {/* Right: Pagination Controls */}
            <div className="flex items-center gap-1">
                {/* Previous Button */}
                <button
                    onClick={() => onPageChange(safeCurrentPage - 1)}
                    disabled={safeCurrentPage <= 1}
                    className="flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-300 disabled:opacity-40 disabled:cursor-not-allowed transition-colors text-xs font-semibold"
                    title={lang === "bn" ? "পূর্ববর্তী পাতা" : "Previous page"}
                >
                    <ChevronLeft className="w-3.5 h-3.5" />
                    <span className="hidden sm:inline">{lang === "bn" ? "পূর্ববর্তী" : "Previous"}</span>
                </button>

                {/* Number Buttons */}
                <div className="flex items-center gap-1">
                    {getPageNumbers().map((p, idx) => {
                        if (p === "...") {
                            return (
                                <span key={`ellipsis-${idx}`} className="px-2 py-1 text-slate-500 font-bold">
                                    ...
                                </span>
                            );
                        }
                        const pageNum = Number(p);
                        const isActive = pageNum === safeCurrentPage;
                        return (
                            <button
                                key={pageNum}
                                onClick={() => onPageChange(pageNum)}
                                className={`w-8 h-8 rounded-lg text-xs font-bold transition-all flex items-center justify-center ${
                                    isActive
                                        ? "bg-orange-600 text-white shadow-xs shadow-orange-600/30 border border-orange-500"
                                        : "bg-slate-800 border border-slate-700 text-slate-300 hover:bg-slate-700 hover:text-white"
                                }`}
                            >
                                {pageNum}
                            </button>
                        );
                    })}
                </div>

                {/* Next Button */}
                <button
                    onClick={() => onPageChange(safeCurrentPage + 1)}
                    disabled={safeCurrentPage >= totalPages}
                    className="flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-300 disabled:opacity-40 disabled:cursor-not-allowed transition-colors text-xs font-semibold"
                    title={lang === "bn" ? "পরবর্তী পাতা" : "Next page"}
                >
                    <span className="hidden sm:inline">{lang === "bn" ? "পরবর্তী" : "Next"}</span>
                    <ChevronRight className="w-3.5 h-3.5" />
                </button>
            </div>
        </div>
    );
};

